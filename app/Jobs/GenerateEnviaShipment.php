<?php

namespace App\Jobs;

use App\Jobs\Concerns\HandlesIntegrationFailure;
use App\Models\IntegrationLog;
use App\Models\Order;
use App\Services\EnviaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateEnviaShipment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesIntegrationFailure;

    public function __construct(public int $orderId)
    {
    }

    protected function service(): string { return 'envia'; }
    protected function action(): string { return 'ship.generate'; }

    public function handle(EnviaService $envia): void
    {
        $result = DB::transaction(function () use ($envia) {
            $order = Order::whereKey($this->orderId)->lockForUpdate()->firstOrFail();

            if ($order->payment_status !== 'paid' || $order->tracking_number || !$order->hasShippableItems()) {
                return null;
            }

            // El proveedor se consulta mientras el pedido está bloqueado. Así dos workers
            // no pueden emitir guías externas distintas para el mismo pedido.
            $result = $envia->generate($order);
            $order->update([
                'tracking_number'  => $result['tracking_number'],
                'label_url'        => $result['label_url'],
                'tracking_url'     => $result['tracking_url'],
                'shipping_carrier' => $result['carrier'],
                'tracking_status'  => 'label_created',
                'packed_at'        => $order->packed_at ?? now(),
            ]);
            return $result;
        });

        if (!$result) {
            return;
        }

        SendOrderShippedEmail::dispatch($this->orderId);

        IntegrationLog::success('envia', 'ship.generate', $this->orderId, $result['payload'], $result['raw']);
    }
}
