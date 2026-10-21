<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FactusService
{
    /** Códigos de tipo de documento según catálogo DIAN (Factus API v2) */
    private const DOC_TYPES = [
        'RC'  => '11', // Registro Civil
        'TI'  => '12', // Tarjeta de Identidad
        'CC'  => '13', // Cédula de Ciudadanía
        'TE'  => '21', // Tarjeta de Extranjería
        'CE'  => '22', // Cédula de Extranjería
        'NIT' => '31', // NIT
        'PP'  => '41', // Pasaporte
        'DIE' => '42', // Documento de Identificación Extranjero
        'PEP' => '47', // Permiso Especial de Permanencia
        'PPT' => '48', // Permiso por Protección Temporal
    ];

    private function token(bool $forceRefresh = false): string
    {
        if ($forceRefresh) {
            Cache::forget('factus.access_token');
        }

        return Cache::remember('factus.access_token', 50 * 60, function () {
            $cfg = config('services.factus');
            $req = Http::baseUrl($cfg['api_url'])->asForm()->acceptJson()->timeout(20);

            if (app()->environment('local', 'testing') || ($cfg['env'] ?? 'sandbox') === 'sandbox') {
                $req->withoutVerifying();
            }

            $res = $req->post('/oauth/token', [
                'grant_type' => 'password',
                'client_id' => $cfg['client_id'],
                'client_secret' => $cfg['client_secret'],
                'username' => $cfg['username'],
                'password' => $cfg['password'],
            ]);

            if ($res->failed() || !$res->json('access_token')) {
                throw new RuntimeException('Factus OAuth falló (' . $res->status() . '): ' . $res->body());
            }

            return $res->json('access_token');
        });
    }

    private function client(): PendingRequest
    {
        $cfg = config('services.factus');
        $req = Http::baseUrl($cfg['api_url'])
            ->withToken($this->token())
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->retry(1, 300, function ($e, $request) {
                // Token vencido: renovar y reintentar una vez
                if (method_exists($e, 'response') && $e->response?->status() === 401) {
                    $request->withToken($this->token(true));
                    return true;
                }
                return false;
            }, throw: false);

        if (app()->environment('local', 'testing') || ($cfg['env'] ?? 'sandbox') === 'sandbox') {
            $req->withoutVerifying();
        }

        return $req;
    }

    /** Payload JSON de factura electrónica compatible con Factus API v2 */
    public function buildPayload(Order $order): array
    {
        $order->loadMissing('items.product', 'user');
        $cfg = config('services.factus');
        $snap = $order->shipping_snapshot ?? [];
        $rate = (float) ($cfg['iva_rate'] ?? 19.0);
        $gross = (float) $order->subtotal + (float) $order->shipping_cost;
        $discountRate = $gross > 0 ? round(((float) $order->discount / $gross) * 100, 2) : 0;

        $docType = strtoupper($order->customer_document_type ?? 'CC');
        $isCompany = $docType === 'NIT';
        $doc = (string) ($order->customer_document ?: '222222222222');

        $lines = [];
        $totalCalculated = 0.0;

        foreach ($order->items as $item) {
            $taxed = $item->product?->is_taxed ?? true;
            $line = $this->line(
                'P' . $item->product_id,
                (string) ($item->product?->name ?? 'Producto'),
                (int) $item->quantity,
                (float) $item->price,
                $taxed,
                $discountRate,
                $rate,
                (bool) ($cfg['prices_include_tax'] ?? true)
            );
            $totalCalculated += $line['_line_total'];
            unset($line['_line_total']);
            $lines[] = $line;
        }

        if ((float) $order->shipping_cost > 0) {
            $line = $this->line(
                'FLETE',
                'Servicio de envío',
                1,
                (float) $order->shipping_cost,
                true,
                $discountRate,
                $rate,
                (bool) ($cfg['prices_include_tax'] ?? true)
            );
            $totalCalculated += $line['_line_total'];
            unset($line['_line_total']);
            $lines[] = $line;
        }

        // El monto declarado en payment_details debe coincidir con el total de la factura
        $paymentAmount = $totalCalculated > 0 ? $totalCalculated : (float) $order->total;

        $paymentMethod = match ($order->payment_method) {
            'cash'     => '10', // Efectivo
            'transfer' => '42', // Consignación / Transferencia
            default    => '48', // Tarjeta Crédito / Wompi
        };

        $reference = $order->reference ?: ('ORD-' . $order->id . '-' . time());

        $customerData = [
            'identification_document_code' => self::DOC_TYPES[$docType] ?? '13',
            'identification'               => $doc,
            'names'                        => $order->user?->name ?: 'Consumidor Final',
            'address'                      => trim(($snap['street'] ?? '') . ' ' . ($snap['number'] ?? ''))
                ?: ($order->shipping_address ?: ($order->isServiceReservation() ? 'No aplica - reserva de servicio' : 'No informado')),
            'email'                        => $order->user?->email ?: 'facturacion@fusashop.com',
            'phone'                        => (string) ($snap['phone'] ?? $order->user?->phone ?? '3000000000'),
            'legal_organization_code'      => $isCompany ? '1' : '2',
            'tribute_code'                 => $isCompany ? '01' : 'ZZ',
            'municipality_code'            => (string) ($cfg['default_municipality_code'] ?? $cfg['default_municipality_id'] ?? '25290'),
        ];

        if ($isCompany && strlen($doc) > 0) {
            $customerData['dv'] = self::computeDv($doc);
            $customerData['company'] = $order->user?->name ?: 'Empresa Cliente';
        }

        return [
            'numbering_range_id' => (int) $cfg['numbering_range_id'],
            'reference_code'     => $reference,
            // El envío del comprobante al cliente lo gestiona la plataforma (Resend).
            // Factus tiene send_email=true por defecto; evitar que lleguen correos duplicados.
            'send_email'         => false,
            'observation'        => 'Pedido ' . ($order->reference ?: $order->id),
            'payment_details'    => [
                [
                    'payment_form'        => '1',
                    'payment_method_code' => $paymentMethod,
                    'reference_code'      => (string) ($order->wompi_transaction_id ?: $reference),
                    'amount'              => number_format($paymentAmount, 2, '.', ''),
                ]
            ],
            'customer' => $customerData,
            'items'    => $lines,
        ];
    }

    private function line(string $code, string $name, int $qty, float $price, bool $taxed, float $discountRate, float $rate, bool $includesTax): array
    {
        // En Factus v2 el 'price' es el valor unitario base (antes de impuestos)
        $unitBase = $taxed && $includesTax
            ? round($price / (1 + ($rate / 100)), 2)
            : $price;

        $gross = round($unitBase * $qty, 2);
        $discount = round($gross * ($discountRate / 100), 2);
        $taxable = $gross - $discount;
        $taxAmount = $taxed ? round($taxable * ($rate / 100), 2) : 0.0;
        $lineTotal = round($taxable + $taxAmount, 2);

        $taxes = [];
        if ($taxed && $rate > 0) {
            $taxes[] = [
                'code' => '01', // IVA
                'rate' => number_format($rate, 2, '.', ''),
            ];
        }

        return [
            'code_reference'    => $code,
            'name'              => mb_substr($name, 0, 250),
            'quantity'          => number_format($qty, 2, '.', ''),
            'discount_rate'     => number_format($discountRate, 2, '.', ''),
            'price'             => number_format($unitBase, 2, '.', ''),
            'unit_measure_code' => (string) (config('services.factus.unit_measure_code') ?? '94'),
            'standard_code'     => '999',
            'is_excluded'       => !$taxed,
            'taxes'             => $taxes,
            '_line_total'       => $lineTotal,
        ];
    }

    /** Emite la factura (POST /v2/bills/validate). */
    public function issue(Order $order): array
    {
        $payload = $this->buildPayload($order);
        $cfg = config('services.factus');

        // En sandbox sin credenciales configuradas: emitir factura simulada para pruebas
        if ((empty($cfg['client_id']) || empty($cfg['username'])) && ($cfg['env'] ?? 'sandbox') === 'sandbox') {
            $num = 'SETP-' . rand(1000, 9999);
            $cufe = hash('sha384', ($order->reference ?? 'REF') . time());
            return [
                'payload' => $payload,
                'number'  => $num,
                'cufe'    => $cufe,
                'raw'     => ['sandbox' => true, 'mock' => true, 'status' => 'Habilitación Sandbox DIAN']
            ];
        }

        $res = $this->client()->post('/v2/bills/validate', $payload);

        if ($res->failed()) {
            throw new RuntimeException('Factus validate falló (' . $res->status() . '): ' . mb_substr($res->body(), 0, 400));
        }

        $bill = $res->json('data.bill') ?? $res->json('data') ?? [];
        if (empty($bill['cufe']) || empty($bill['number'])) {
            throw new RuntimeException('Factus no devolvió CUFE/número de factura. Respuesta: ' . mb_substr($res->body(), 0, 300));
        }

        return [
            'payload' => $payload,
            'number'  => $bill['number'],
            'cufe'    => $bill['cufe'],
            'raw'     => $res->json(),
        ];
    }

    /** Descarga PDF (base64 decodificado). */
    public function downloadPdf(string $number): string
    {
        return $this->download("/v2/bills/{$number}/download-pdf", 'pdf_base_64_encoded');
    }

    /** Descarga XML (base64 decodificado). */
    public function downloadXml(string $number): string
    {
        return $this->download("/v2/bills/{$number}/download-xml", 'xml_base_64_encoded');
    }

    private function download(string $path, string $key): string
    {
        $cfg = config('services.factus');
        if ((empty($cfg['client_id']) || empty($cfg['username'])) && ($cfg['env'] ?? 'sandbox') === 'sandbox') {
            return "%PDF-1.4\n% FusaShop Factura Electronica DIAN Sandbox (Simulacion)\n";
        }

        $res = $this->client()->get($path);
        $b64 = $res->json("data.{$key}");
        if ($res->failed() || !$b64) {
            throw new RuntimeException("Factus descarga falló ({$path}, {$res->status()}).");
        }

        $bin = base64_decode($b64, true);
        if ($bin === false) {
            throw new RuntimeException('Factus devolvió base64 inválido.');
        }

        return $bin;
    }

    public static function computeDv(string $nit): string
    {
        $primes = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $clean = preg_replace('/\D/', '', $nit);
        $len = strlen($clean);
        $total = 0;
        for ($i = 0; $i < $len; $i++) {
            $digit = (int) $clean[$len - 1 - $i];
            $total += $digit * ($primes[$i] ?? 0);
        }
        $mod = $total % 11;
        return (string) ($mod > 1 ? 11 - $mod : $mod);
    }
}
