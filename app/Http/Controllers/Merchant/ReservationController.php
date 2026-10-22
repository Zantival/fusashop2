<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::where('merchant_id', Auth::id())
            ->with(['customer', 'product'])
            ->latest()
            ->paginate(20);

        return view('merchant.reservations', compact('reservations'));
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

        return back()->with('success', 'Estado de la reserva actualizado.');
    }
}
