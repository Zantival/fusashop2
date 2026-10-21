<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class WompiService
{
    /**
     * Firma de integridad: SHA256("{referencia}{monto_en_centavos}{moneda}{secreto_integridad}").
     */
    public function integritySignature(string $reference, int $amountInCents, string $currency = 'COP', ?string $customSecret = null): string
    {
        $secret = $customSecret;
        if ($secret === null) {
            $order = Order::where('reference', $reference)->first();
            $secret = $order?->merchantProfile()?->wompi_integrity_secret ?: config('services.wompi.integrity_secret');
        }

        return hash('sha256', $reference . $amountInCents . $currency . $secret);
    }

    public function isSandboxWithoutKeys(?Order $order = null): bool
    {
        $merchantProfile = $order?->merchantProfile();
        if ($merchantProfile?->hasCustomWompi()) {
            return false;
        }

        return config('services.wompi.env', 'sandbox') === 'sandbox'
            && empty(config('services.wompi.public_key'));
    }

    /** URL del Web Checkout oficial de Wompi (el pago ingresa directamente al comerciante si configuró sus llaves). */
    public function checkoutUrl(Order $order, string $redirectUrl): string
    {
        $cents = $order->totalInCents();
        $merchantProfile = $order->merchantProfile();
        $useMerchantKeys = $merchantProfile?->hasCustomWompi() ?? false;

        $signature = $this->integritySignature(
            $order->reference,
            $cents,
            $order->currency,
            $useMerchantKeys ? $merchantProfile->wompi_integrity_secret : null
        );

        if ($this->isSandboxWithoutKeys($order)) {
            return route('wompi.sandbox.checkout', [
                'reference'       => $order->reference,
                'amount_in_cents' => $cents,
                'currency'        => $order->currency,
                'signature'       => $signature,
                'redirect_url'    => $redirectUrl,
            ]);
        }

        $publicKey = $useMerchantKeys ? $merchantProfile->wompi_public_key : config('services.wompi.public_key');

        if (empty($publicKey)) {
            throw new \RuntimeException('El pedido no tiene una cuenta Wompi válida de un único comerciante.');
        }

        return rtrim(config('services.wompi.checkout_url'), '/') . '/?' . $this->buildCheckoutQuery([
            'public-key'              => $publicKey,
            'currency'                => $order->currency,
            'amount-in-cents'         => $cents,
            'reference'               => $order->reference,
            'signature:integrity'     => $signature,
            'redirect-url'            => $redirectUrl,
            'customer-data:email'     => $order->user->email,
            'customer-data:full-name' => $order->user->name,
        ]);
    }

    /** URL de Checkout Wompi para pago de pauta publicitaria (BannerRequest - va a la plataforma o destino del analista). */
    public function bannerCheckoutUrl(\App\Models\BannerRequest $bannerRequest, string $reference, string $redirectUrl): string
    {
        $cents = (int) round(((float) $bannerRequest->cost) * 100);
        $activeSnapshot = \App\Models\Setting::activeBannerPayoutSnapshot();
        $publicKey = $activeSnapshot['wompi_public_key'] ?: config('services.wompi.public_key');
        $integritySecret = $activeSnapshot['wompi_integrity_secret'] ?: config('services.wompi.integrity_secret');

        $signature = hash('sha256', $reference . $cents . 'COP' . $integritySecret);

        if ($this->isSandboxWithoutKeys() && empty($activeSnapshot['wompi_public_key'])) {
            return route('wompi.sandbox.checkout', [
                'reference'       => $reference,
                'amount_in_cents' => $cents,
                'currency'        => 'COP',
                'signature'       => $signature,
                'redirect_url'    => $redirectUrl,
            ]);
        }

        return rtrim(config('services.wompi.checkout_url'), '/') . '/?' . $this->buildCheckoutQuery([
            'public-key'              => $publicKey,
            'currency'                => 'COP',
            'amount-in-cents'         => $cents,
            'reference'               => $reference,
            'signature:integrity'     => $signature,
            'redirect-url'            => $redirectUrl,
            'customer-data:email'     => $bannerRequest->user?->email ?? '',
            'customer-data:full-name' => $bannerRequest->user?->name ?? '',
        ]);
    }

    /**
     * El checkout de Wompi rechaza (403 CloudFront) un redirect-url que apunta a localhost.
     * En ese caso se omite el parámetro; en producción/túnel https se envía normalmente.
     */
    private function buildCheckoutQuery(array $params): string
    {
        $host = strtolower((string) parse_url((string) ($params['redirect-url'] ?? ''), PHP_URL_HOST));
        if (in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]', ''], true)) {
            unset($params['redirect-url']);
        }

        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Valida la autenticidad de un evento de Wompi.
     * Soporta tanto las credenciales del comerciante como las globales.
     */
    public function verifyEvent(array $payload, ?string $headerChecksum = null): bool
    {
        $reference = Arr::get($payload, 'data.transaction.reference');
        $order = $reference ? Order::where('reference', $reference)->first() : null;
        $merchantProfile = $order?->merchantProfile();
        $bannerRequest = !$order && $reference
            ? \App\Models\BannerRequest::where('payment_proof_path', $reference)->exists()
            : false;

        if ($merchantProfile?->hasCustomWompi()) {
            $secret = $merchantProfile->wompi_events_secret;
        } elseif ($bannerRequest) {
            $bannerCredentials = \App\Models\Setting::activeBannerPayoutSnapshot();
            $secret = !empty($bannerCredentials['wompi_public_key'])
                ? $bannerCredentials['wompi_events_secret']
                : config('services.wompi.events_secret');
        } else {
            $secret = config('services.wompi.events_secret');
        }

        $properties = Arr::get($payload, 'signature.properties');
        $checksum = Arr::get($payload, 'signature.checksum') ?: $headerChecksum;
        $timestamp = Arr::get($payload, 'timestamp');

        if (!$secret || !is_array($properties) || !$checksum || $timestamp === null) {
            return false;
        }

        $concat = '';
        foreach ($properties as $path) {
            $value = Arr::get($payload['data'] ?? [], $path);
            if ($value === null || is_array($value)) {
                return false;
            }
            $concat .= is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        }

        $expected = hash('sha256', $concat . $timestamp . $secret);

        return hash_equals($expected, strtolower((string) $checksum));
    }

    /** Protección contra replay: el timestamp del evento debe estar dentro de la ventana permitida. */
    public function timestampIsFresh(array $payload): bool
    {
        $ts = (int) Arr::get($payload, 'timestamp', 0);
        if ($ts <= 0) {
            return false;
        }

        return abs(time() - $ts) <= config('services.wompi.timestamp_tolerance');
    }

    /** Consulta server-to-server del estado real de una transacción. */
    public function fetchTransaction(string $transactionId): array
    {
        if (str_starts_with($transactionId, 'sbx_') || $this->isSandboxWithoutKeys()) {
            $record = \App\Models\PaymentTransaction::where('transaction_id', $transactionId)->first();
            if ($record) {
                return [
                    'id'              => $record->transaction_id,
                    'status'          => $record->status,
                    'reference'       => $record->reference,
                    'amount_in_cents' => (int) $record->amount_in_cents,
                    'currency'        => $record->currency ?: 'COP',
                ];
            }
        }

        $response = Http::baseUrl(config('services.wompi.api_url'))
            ->timeout(15)->retry(2, 300, throw: false)
            ->acceptJson()
            ->get('/transactions/' . urlencode($transactionId));

        $response->throw();

        return $response->json('data') ?? [];
    }
}
