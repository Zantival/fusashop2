<?php

namespace App\Services;

use App\Models\MerchantSuborder;
use App\Models\MerchantWalletEntry;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class MerchantSettlementService
{
    /** Create one immutable seller suborder and wallet credit for each paid general order. */
    public function creditPaidOrder(Order $order): void
    {
        $order->loadMissing('items.product');

        $sellerGross = [];
        foreach ($order->items as $item) {
            $merchantId = (int) ($item->product?->merchant_id ?? 0);
            if ($merchantId < 1) {
                throw new \RuntimeException("El producto del pedido {$order->id} no tiene comerciante.");
            }
            $sellerGross[$merchantId] = ($sellerGross[$merchantId] ?? 0)
                + (int) round((float) $item->price * 100) * (int) $item->quantity;
        }

        if (!$sellerGross) {
            return;
        }

        // El descuento por puntos se distribuye proporcionalmente entre vendedores.
        // El envío queda en la plataforma; el ingreso de vendedor solo incluye mercancía pagada.
        $subtotalCents = array_sum($sellerGross);
        $paidMerchandiseCents = max(0, $subtotalCents - (int) round((float) $order->discount * 100));
        $commissionRate = $subtotalCents > 0
            ? (int) round(((int) round((float) $order->commission_amount * 100)) * 10000 / $subtotalCents)
            : 0;

        $merchantIds = array_keys($sellerGross);
        sort($merchantIds, SORT_NUMERIC); // residuo de redondeo asignado determinísticamente
        $grossRemaining = $paidMerchandiseCents;
        $commissionRemaining = (int) round($paidMerchandiseCents * $commissionRate / 10000);
        foreach ($merchantIds as $index => $merchantId) {
            $isLast = $index === array_key_last($merchantIds);
            $grossCents = $isLast
                ? $grossRemaining
                : (int) floor($paidMerchandiseCents * $sellerGross[$merchantId] / max(1, $subtotalCents));
            $grossRemaining -= $grossCents;

            $sellerCommissionCents = $isLast
                ? $commissionRemaining
                : (int) round($grossCents * $commissionRate / 10000);
            $commissionRemaining -= $sellerCommissionCents;
            $sellerItems = $order->items->filter(fn ($item) => (int) $item->product?->merchant_id === (int) $merchantId)
                ->map(fn ($item) => [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'unit_price_cents' => (int) round((float) $item->price * 100),
                    'line_total_cents' => (int) round((float) $item->price * 100) * (int) $item->quantity,
                ])->values()->all();

            $suborder = MerchantSuborder::firstOrCreate(
                ['order_id' => $order->id, 'merchant_id' => $merchantId],
                [
                    'reference' => 'FS-SUB-' . strtoupper((string) \Illuminate\Support\Str::ulid()),
                    'items_snapshot' => $sellerItems,
                    'gross_cents' => $grossCents,
                    'commission_cents' => $sellerCommissionCents,
                    'net_cents' => max(0, $grossCents - $sellerCommissionCents),
                    'currency' => $order->currency,
                    'status' => 'processing',
                    'payout_status' => 'available',
                    'paid_at' => $order->paid_at ?? now(),
                ]
            );

            MerchantWalletEntry::firstOrCreate(
                ['idempotency_key' => 'sale-suborder-' . $suborder->id],
                [
                    'merchant_id' => $merchantId,
                    'order_id' => $order->id,
                    'suborder_id' => $suborder->id,
                    'entry_type' => 'sale_credit',
                    'direction' => 'credit',
                    'amount_cents' => $suborder->net_cents,
                    'currency' => $order->currency,
                    'metadata' => [
                        'gross_cents' => $suborder->gross_cents,
                        'commission_cents' => $suborder->commission_cents,
                        'wompi_transaction_id' => $order->wompi_transaction_id,
                    ],
                    'posted_at' => $order->paid_at ?? now(),
                ]
            );
        }
    }

    public function balanceCents(int $merchantId): int
    {
        return (int) DB::table('merchant_wallet_entries')
            ->where('merchant_id', $merchantId)
            ->selectRaw("COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount_cents ELSE -amount_cents END), 0) as balance")
            ->value('balance');
    }
}
