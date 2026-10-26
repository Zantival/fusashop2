<?php           

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use App\Events\MessageSent;
use App\Notifications\NewMessageNotification;
use App\Support\ChatMessageModeration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        if (Auth::user()?->isMerchant()) {
            return redirect()->route('merchant.messages');
        }

        // Lista de conversaciones (últimos mensajes con otros usuarios)
        $userId = Auth::id();
        $conversations = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(function($msg) use ($userId) {
                return $msg->sender_id == $userId ? $msg->receiver_id : $msg->sender_id;
            });

        $latestMessageId = (int) ($conversations->flatten(1)->max('id') ?? 0);

        return view('chat.index', compact('conversations', 'latestMessageId'));
    }

    public function show($otherUserId)
    {
        $otherUser = User::findOrFail($otherUserId);

        if (Auth::user()?->isMerchant()) {
            if ($otherUser->isAnalyst()) {
                return redirect()->route('merchant.support.contact', ['analyst' => $otherUser->id]);
            }
            return redirect()->route('merchant.messages', $otherUserId);
        }

        $userId = Auth::id();
        
        $messages = Message::where(function($query) use ($userId, $otherUserId) {
            $query->where(function($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $userId)->where('receiver_id', $otherUserId);
            })->orWhere(function($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $otherUserId)->where('receiver_id', $userId);
            });
        })
            ->with(['product', 'sender'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Marcar como leídos
        Message::where('sender_id', $otherUserId)->where('receiver_id', $userId)->update(['is_read' => true]);
        Auth::user()?->unreadNotifications
            ->filter(fn($n) => ($n->data['sender_id'] ?? null) == $otherUserId)
            ->each->markAsRead();

        $badWords = Message::getBadWords();

        return view('chat.show', compact('messages', 'otherUser', 'badWords'));
    }

    /**
     * Polling endpoint — devuelve mensajes nuevos desde un timestamp dado
     */
    public function poll($otherUserId, Request $request)
    {
        $userId = Auth::id();
        $afterId = max(0, (int) $request->query('after', 0));

        $messages = Message::where(function ($query) use ($userId, $otherUserId) {
                $query->where(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $otherUserId);
                })->orWhere(function ($q) use ($userId, $otherUserId) {
                    $q->where('sender_id', $otherUserId)->where('receiver_id', $userId);
                });
            })
            ->where('id', '>', $afterId)
            ->with(['product', 'sender'])
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get()
            ->map(function($m) use ($userId) {
                return [
                    'id'        => $m->id,
                    'content'   => e($m->content),
                    'mine'      => $m->sender_id === $userId,
                    'time'      => $m->created_at->format('H:i'),
                    'sender_name' => $m->sender?->name,
                    'sender_avatar' => $m->sender?->avatarUrl(),
                    'product'   => $m->product ? ['name' => e($m->product->name), 'price' => number_format($m->product->price,0,',','.')] : null,
                ];
            });

        // Marcar como leídos los del otro
        Message::where('sender_id', $otherUserId)->where('receiver_id', $userId)->update(['is_read' => true]);
        Auth::user()?->unreadNotifications
            ->filter(fn($n) => ($n->data['sender_id'] ?? null) == $otherUserId)
            ->each->markAsRead();

        return response()->json(['messages' => $messages, 'last_message_id' => (int) ($messages->max('id') ?? $afterId)])
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function inboxPoll(Request $request)
    {
        $userId = Auth::id();
        $afterId = max(0, (int) $request->query('after', 0));
        $messages = Message::where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->where('id', '>', $afterId)
            ->with(['sender', 'receiver'])
            ->orderBy('id')
            ->limit(50)
            ->get();
        $incomingPartners = $messages->where('receiver_id', $userId)->pluck('sender_id')->unique()->values();
        $unreadCounts = $incomingPartners->isEmpty()
            ? collect()
            : Message::where('receiver_id', $userId)
                ->where('is_read', false)
                ->whereIn('sender_id', $incomingPartners)
                ->selectRaw('sender_id, COUNT(*) as unread_count')
                ->groupBy('sender_id')
                ->pluck('unread_count', 'sender_id');

        $messages = $messages->map(function (Message $message) use ($userId, $unreadCounts) {
                $other = $message->sender_id === $userId ? $message->receiver : $message->sender;
                return [
                    'id' => $message->id,
                    'partner_id' => $other?->id,
                    'partner_name' => $other?->name,
                    'partner_role' => $other?->role,
                    'partner_avatar' => $other?->avatarUrl(),
                    'content' => e($message->content),
                    'mine' => $message->sender_id === $userId,
                    'time' => $message->created_at->diffForHumans(null, true),
                    'unread' => $message->receiver_id === $userId ? (int) ($unreadCounts[$message->sender_id] ?? 0) : 0,
                ];
            });

        return response()->json([
            'messages' => $messages,
            'last_message_id' => (int) ($messages->max('id') ?? $afterId),
        ])->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function store(Request $request, ChatMessageModeration $moderator)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string|max:1000',
            'product_id'  => 'nullable|exists:products,id'
        ]);

        $content = strip_tags($request->content);

        $moderation = $moderator->inspect($content);
        if (!$moderation['allowed']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'content' => 'No se envió el mensaje porque contiene lenguaje ofensivo. Puedes reformularlo o, si estás citando un insulto recibido para reportarlo, explica claramente ese contexto.',
            ]);
        }

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'content'     => $content,
            'product_id'  => $request->product_id
        ]);

        // Notificar al receptor
        $receiver = User::find($request->receiver_id);
        if($receiver) {
            $receiver->notify(new NewMessageNotification($message));
        }

        broadcast(new MessageSent($message))->toOthers();

        if ($request->wantsJson()) {
            return response()->json($message);
        }

        return back()->with('success', 'Mensaje enviado.');
    }

    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        
        if ($message->sender_id === Auth::id()) {
            $message->delete();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 403);
    }
}
