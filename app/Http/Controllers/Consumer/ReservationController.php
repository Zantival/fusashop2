<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:120'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'party_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'customer_notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $product = Product::active()->with('merchant.companyProfile')->findOrFail($data['product_id']);
        abort_unless($product->merchant_id && $product->isReservationOnly(), 422, 'Este artículo no está configurado para reservas.');
        $selectedOptions = $data['options'] ?? [];
        abort_unless($product->validOptionSelection($selectedOptions), 422, 'Selecciona una opción válida para cada especificación.');
        $variant = $product->variantForOptions($selectedOptions);
        abort_if($product->variants && !$variant, 422, 'La opción seleccionada ya no está disponible.');
        if ($variant) {
            abort_if($data['quantity'] > max(0, (int) $variant['stock'] - (int) ($variant['reserved_stock'] ?? 0)), 422, 'No hay disponibilidad suficiente para esta opción.');
        }

        $reservation = Reservation::create([
            'reference' => 'RSV-' . strtoupper(Str::ulid()->toBase32()),
            'user_id' => Auth::id(),
            'merchant_id' => $product->merchant_id,
            'product_id' => $product->id,
            'quantity' => $data['quantity'],
            'selected_options' => $selectedOptions,
            'quoted_price' => $product->priceForOptions($selectedOptions),
            'preferred_date' => $data['preferred_date'] ?? null,
            'preferred_time' => $data['preferred_time'] ?? null,
            'party_size' => $data['party_size'] ?? null,
            'customer_notes' => isset($data['customer_notes']) ? strip_tags($data['customer_notes']) : null,
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitud de reserva enviada. El comercio debe confirmar disponibilidad.',
                'reference' => $reservation->reference,
                'redirect' => route('consumer.reservations'),
            ], 201);
        }

        return redirect()->route('consumer.reservations')
            ->with('success', "Solicitud {$reservation->reference} enviada. El comercio confirmará la disponibilidad.");
    }

    public function index()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->with(['product.merchant.companyProfile'])
            ->latest()
            ->paginate(15);

        return view('consumer.reservations', compact('reservations'));
    }

    public function cancel(Reservation $reservation)
    {
        abort_unless($reservation->user_id === Auth::id(), 403);
        abort_unless(in_array($reservation->status, ['pending', 'confirmed'], true), 409, 'Esta reserva ya no se puede cancelar.');

        $reservation->update(['status' => 'cancelled']);

        return back()->with('success', 'La solicitud de reserva fue cancelada.');
    }
}
