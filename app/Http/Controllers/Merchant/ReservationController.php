<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Notifications\NewReservationNotification;
use App\Notifications\ReservationCancelledNotification;
use App\Notifications\ReservationStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $merchantId = Auth::id();

        // Marcar notificaciones de reservas como leídas
        Auth::user()->unreadNotifications
            ->filter(fn($n) => in_array($n->data['type'] ?? '', ['new_reservation', 'reservation_cancelled'], true)
                || in_array($n->type, [NewReservationNotification::class, ReservationCancelledNotification::class], true))
            ->each->markAsRead();

        $currentStatus = $request->query('status', '');

        $baseQuery = Reservation::where('merchant_id', $merchantId)
            ->with(['customer', 'product']);

        $counts = [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        if (!empty($currentStatus) && in_array($currentStatus, ['pending', 'confirmed', 'completed', 'rejected', 'cancelled'], true)) {
            $baseQuery->where('status', $currentStatus);
        }

        $reservations = $baseQuery->latest()->paginate(20)->withQueryString();

        return view('merchant.reservations', compact('reservations', 'counts', 'currentStatus'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->merchant_id === Auth::id(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:confirmed,rejected,completed'],
            'merchant_notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $allowed = match ($reservation->status) {
            'pending' => ['confirmed', 'rejected'],
            'confirmed' => ['completed', 'rejected'],
            default => [],
        };
        abort_unless(in_array($data['status'], $allowed, true), 409, 'No se puede aplicar ese cambio al estado actual.');

        $reservation->update([
            'status' => $data['status'],
            'merchant_notes' => isset($data['merchant_notes']) ? strip_tags($data['merchant_notes']) : $reservation->merchant_notes,
            'responded_at' => now(),
        ]);

        try {
            $reservation->customer?->notify(new ReservationStatusNotification($reservation));
        } catch (\Throwable $e) {
            Log::warning('Error notificando al cliente sobre actualización de reserva: ' . $e->getMessage());
        }

        $messages = [
            'confirmed' => '¡Reserva confirmada con éxito! Se notificó al cliente.',
            'rejected' => 'La solicitud de reserva ha sido marcada como no disponible / rechazada.',
            'completed' => 'La reserva ha sido marcada como completada.',
        ];

        return back()->with('success', $messages[$data['status']] ?? 'Estado de la reserva actualizado.');
    }
}
