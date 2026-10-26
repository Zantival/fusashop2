<?php

namespace App\Http\Controllers\Consumer;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessWompiEvent;
use App\Models\IntegrationLog;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\CompanyProfile;
use App\Models\ShippingAddress;
use App\Services\CheckoutService;
use App\Services\EnviaService;
use App\Services\WompiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        $cart = $user->cart()->with('items.product.merchant.companyProfile')->first();
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('consumer.cart');
        }

        // Leer ítems seleccionados desde request o session
        $selectedItemIds = $request->input('selected_items');
        if (is_array($selectedItemIds) && !empty($selectedItemIds)) {
            $selectedItemIds = array_map('intval', $selectedItemIds);
            session(['checkout_selected_items' => $selectedItemIds]);
        } else {
            $selectedItemIds = session('checkout_selected_items');
        }

        if (!empty($selectedItemIds)) {
            $filtered = $this->selectedItems($cart, $selectedItemIds);
            if ($filtered->isEmpty()) {
                return redirect()->route('consumer.cart')->with('error', 'Selecciona al menos un producto para proceder al pago.');
            }
            $cart->setRelation('items', $filtered);
        }

        if ($cart->items->contains(fn ($item) => $item->product?->isReservationOnly())) {
            return redirect()->route('consumer.cart')->with('error', 'Los servicios se solicitan como reserva desde su ficha; no se compran ni se pagan en el checkout.');
        }

        if ($message = $this->merchantPaymentError($cart->items)) {
            return redirect()->route('consumer.cart')->with('error', $message);
        }

        $requiresShipping = $cart->items->contains(fn ($item) => !$item->product?->isService());

        $merchantIds = $cart->items->pluck('product.merchant_id')->unique();
        $availablePoints = LoyaltyPoint::where('user_id', $user->id)->whereIn('merchant_id', $merchantIds)->sum('points');
        $eligibleSubtotal = (float) $cart->items->filter(fn ($i) => (bool) ($i->product->allows_points ?? true))
            ->sum(fn ($i) => $i->quantity * $i->product->effectivePrice());

        $usePoints = $request->has('use_points') ? $request->boolean('use_points') : session('checkout_use_points', false);
        session(['checkout_use_points' => $usePoints]);

        $addresses = $user->shippingAddresses()->orderByDesc('is_default')->get();

        if ($requiresShipping && $addresses->isEmpty()) {
            return redirect()->route('consumer.addresses')
                ->with('error', 'Agrega una dirección de envío para poder cotizar tu flete.');
        }

        return view('consumer.checkout', compact('cart', 'availablePoints', 'eligibleSubtotal', 'addresses', 'usePoints', 'selectedItemIds', 'requiresShipping'));
    }

    /** Cotización dinámica de flete por dirección + carrito (peso/dimensiones volumétricas). */
    public function quote(Request $request, EnviaService $envia): JsonResponse
    {
        $data = $request->validate([
            'address_id' => 'required|integer',
            'selected_items' => 'nullable|array',
            'selected_items.*' => 'integer',
        ]);
        $selectedIds = $data['selected_items'] ?? session('checkout_selected_items');
        $cart = Auth::user()->cart()->with('items.product')->first();
        abort_if(!$cart, 422, 'Carrito vacío');
        $items = $this->selectedItems($cart, $selectedIds);
        abort_if($items->isEmpty(), 422, 'Ningún producto seleccionado');
        abort_if(!$items->contains(fn ($item) => $item->product && !$item->product->isService()), 422, 'No hay productos físicos que requieran envío.');

        $address = Auth::user()->shippingAddresses()->findOrFail($data['address_id']);

        try {
            return response()->json(['rates' => $this->rates($envia, $address, $selectedIds)]);
        } catch (Throwable $e) {
            IntegrationLog::failure('envia', 'rate', null, $e, ['postal_code' => $address->postal_code]);
            return response()->json(['message' => 'No pudimos cotizar el envío en este momento. Intenta de nuevo.'], 502);
        }
    }

    private function rates(EnviaService $envia, ShippingAddress $address, ?array $selectedItemIds = null): array
    {
        $cart = Auth::user()->cart()->with('items.product')->first();
        abort_if(!$cart || $cart->items->isEmpty(), 422, 'Carrito vacío');

        $items = $this->selectedItems($cart, $selectedItemIds)
            ->filter(fn ($item) => !$item->product?->isService());
        abort_if($items->isEmpty(), 422, 'No hay productos físicos que requieran envío.');

        $itemsTotal = (float) $items->sum(fn ($i) => $i->quantity * $i->product->effectivePrice());
        $package = $envia->buildPackage($items, $itemsTotal);
        $snapshot = $address->toSnapshot() + ['email' => Auth::user()->email];

        return $envia->quote($snapshot, $package);
    }

    private function selectedItems($cart, ?array $selectedItemIds)
    {
        $items = $cart->items;
        if (!empty($selectedItemIds)) {
            $items = $items->whereIn('id', array_map('intval', $selectedItemIds));
            abort_if($items->count() !== count(array_unique(array_map('intval', $selectedItemIds))), 422, 'La selección del carrito ya no está disponible.');
        }

        return $items;
    }

    public function process(Request $request, EnviaService $envia, CheckoutService $checkout, WompiService $wompi)
    {
        $data = $request->validate([
            'address_id' => 'nullable|integer',
            'shipping_rate' => ['nullable', 'string', 'regex:/^[a-z0-9_\-]+\|[A-Za-z0-9_\-\. ]+$/i'],
            'document_type' => 'required|in:CC,CE,NIT,PP,TI,TE',
            'document' => ['required', 'string', 'regex:/^[A-Za-z0-9\-]{5,20}$/'],
            'use_points' => 'nullable|boolean',
            'selected_items' => 'nullable|array',
            'selected_items.*' => 'integer',
        ]);

        $user = Auth::user();
        $cart = $user->cart()->with('items.product.merchant.companyProfile')->first();
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('consumer.cart');
        }

        $selectedItemIds = $data['selected_items'] ?? session('checkout_selected_items');
        $cartItems = $this->selectedItems($cart, $selectedItemIds);
        if ($cartItems->isEmpty()) {
            return back()->withInput()->with('error', 'Selecciona al menos un producto para proceder al pago.');
        }

        if ($cartItems->contains(fn ($item) => $item->product?->isReservationOnly())) {
            return redirect()->route('consumer.cart')->with('error', 'Los servicios se solicitan como reserva desde su ficha; no se compran ni se pagan en el checkout.');
        }

        if ($message = $this->merchantPaymentError($cartItems)) {
            return redirect()->route('consumer.cart')->with('error', $message);
        }

        $requiresShipping = $cartItems->contains(fn ($item) => !$item->product?->isService());
        $address = null;
        if ($requiresShipping) {
            if (empty($data['address_id']) || empty($data['shipping_rate'])) {
                return back()->withInput()->with('error', 'Selecciona una dirección y una tarifa de envío para los productos físicos.');
            }
            $address = $user->shippingAddresses()->findOrFail($data['address_id']);

            // El precio del flete se re-cotiza en servidor: nunca se confía en el valor enviado por el cliente.
            [$carrier, $service] = explode('|', $data['shipping_rate'], 2);
            try {
                $rate = collect($this->rates($envia, $address, $selectedItemIds))
                    ->first(fn ($r) => strtolower($r['carrier']) === strtolower($carrier) && $r['service'] === $service);
            } catch (Throwable $e) {
                IntegrationLog::failure('envia', 'rate', null, $e);
                return back()->withInput()->with('error', 'No pudimos validar el costo de envío. Intenta de nuevo.');
            }
            if (!$rate) {
                return back()->withInput()->with('error', 'La tarifa de envío seleccionada ya no está disponible. Cotiza nuevamente.');
            }
        } else {
            // Para servicios se reserva la capacidad del vendedor; no existe costo ni guía de envío.
            $rate = ['carrier' => null, 'service' => null, 'price' => 0];
        }

        try {
            $order = $checkout->createOrder(
                $user, $cart, $address, $rate, $data['document_type'], strtoupper($data['document']), $request->boolean('use_points'), $selectedItemIds
            );
            session()->forget(['checkout_selected_items', 'checkout_use_points']);
        } catch (InsufficientStockException $e) {
            return redirect()->route('consumer.cart')->with('error', $e->getMessage());
        }

        try {
            return redirect()->away($wompi->checkoutUrl($order, route('consumer.checkout.result')));
        } catch (Throwable $e) {
            IntegrationLog::failure('wompi', 'checkout_url', $order->id, $e);
            return redirect()->route('consumer.cart')->with('error', 'No fue posible iniciar el pago. Verifica que el comerciante tenga sus llaves Wompi configuradas.');
        }
    }

    /** El checkout solo puede cobrar a un comercio y siempre usa su cuenta Wompi. */
    private function merchantPaymentError($items): ?string
    {
        $merchantIds = $items->map(fn ($item) => $item->product?->merchant_id)->filter()->unique();
        if ($merchantIds->count() !== 1 || $items->contains(fn ($item) => !$item->product?->merchant_id)) {
            return 'Para pagar, selecciona productos de un solo comerciante. Finaliza esa compra y luego puedes pagar los productos de otra tienda.';
        }

        $profile = CompanyProfile::where('merchant_id', $merchantIds->first())->first();
        $wompi = app(\App\Services\WompiService::class);
        $hasGlobalFallback = !empty(config('services.wompi.public_key')) || $wompi->isSandboxWithoutKeys();
        if (!$profile?->hasCustomWompi() && !$hasGlobalFallback) {
            return 'Esta tienda todavía no tiene su cuenta de pagos Wompi configurada. Contacta al comerciante para que complete su configuración antes de comprar.';
        }

        return null;
    }

    /**
     * Retorno desde Wompi (?id=transaction_id). La fuente de verdad sigue siendo el webhook; aquí solo se
     * reconcilia consultando la API de Wompi (server-to-server) por si el webhook aún no llegó.
     */
    public function result(Request $request, WompiService $wompi)
    {
        $transactionId = (string) $request->query('id', '');
        $order = null;

        if ($transactionId !== '') {
            try {
                $tx = $wompi->fetchTransaction($transactionId);
                $order = Order::where('user_id', Auth::id())->where('reference', $tx['reference'] ?? '')->first();
                if ($order && $order->payment_status === 'pending') {
                    ProcessWompiEvent::dispatch($tx);
                }
            } catch (Throwable $e) {
                IntegrationLog::failure('wompi', 'reconcile', null, $e, ['transaction_id' => $transactionId]);
            }
        }

        $order ??= Order::where('user_id', Auth::id())->latest()->first();
        abort_if(!$order, 404);

        [$status, $message] = match (true) {
            $order->payment_status === 'paid' && $order->isServiceReservation() => ['success', '¡Pago aprobado y reserva confirmada! Te enviaremos por correo la factura y los datos del servicio adquirido.'],
            $order->payment_status === 'paid' => ['success', '¡Pago aprobado! Estamos preparando tu pedido y tu factura electrónica.'],
            in_array($order->payment_status, ['failed'], true) => ['error', 'El pago no fue aprobado. Las unidades reservadas fueron liberadas.'],
            default => ['pending', 'Estamos confirmando tu pago con Wompi. Esta página se actualizará sola.'],
        };

        return view('payment.result', ['status' => $status, 'message' => $message, 'reference' => $order->reference]);
    }
}
