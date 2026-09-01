<?php

namespace App\Http\Controllers;

use App\Models\PQRS;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PQRSController extends Controller
{
    // --- Lado Usuario (Consumidor/Comerciante) ---
    public function index()
    {
        $pqrs = PQRS::where('user_id', Auth::id())->latest()->paginate(10);
        return view('pqrs.index', compact('pqrs'));
    }

    public function create()
    {
        return view('pqrs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'    => 'required|in:peticion,queja,reclamo,sugerencia',
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
        ]);

        $pqrs = PQRS::create([
            'user_id' => Auth::id(),
            'type'    => $data['type'],
            'subject' => strip_tags($data['subject']),
            'content' => strip_tags($data['content']),
            'status'  => 'pending',
        ]);

        // Notificar a los analistas
        $analysts = User::where('role', 'analyst')->get();
        foreach ($analysts as $analyst) {
            $analyst->notify(new \App\Notifications\NewPQRSNotification($pqrs));
        }

        return redirect()->route('pqrs.index')->with('success', 'Tu solicitud ha sido enviada correctamente. Un administrador la revisará pronto.');
    }

    public function show(PQRS $pqrs)
    {
        if ($pqrs->user_id !== Auth::id() && !Auth::user()->isAnalyst()) {
            abort(403);
        }
        return view('pqrs.show', compact('pqrs'));
    }

    // --- Lado Administrador (Analyst) ---
    public function adminIndex()
    {
        $pqrs = PQRS::with('user')->latest()->paginate(20);
        return view('analyst.pqrs.index', compact('pqrs'));
    }

    public function adminReply(Request $request, PQRS $pqrs)
    {
        $request->validate([
            'admin_response' => 'required|string|max:2000',
            'status'         => 'required|in:in_review,resolved,closed'
        ]);

        $pqrs->update([
            'admin_response' => strip_tags($request->admin_response),
            'status'         => $request->status,
            'resolved_at'    => $request->status === 'resolved' ? now() : $pqrs->resolved_at,
        ]);

        // Notificar al usuario
        $pqrs->user->notify(new \App\Notifications\PQRSResponseNotification($pqrs));

        return back()->with('success', 'Respuesta enviada y estado actualizado.');
    }
}
