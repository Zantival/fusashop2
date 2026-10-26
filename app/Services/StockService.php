<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendLowStockAlert;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Reservas de inventario con bloqueo pesimista.
 * reserve() al crear el pedido -> commit() al aprobarse el pago -> release() si se rechaza/expira.
 */
class StockService
{
    /** @throws InsufficientStockException */
    public function reserve(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                $variantIndex = $this->variantIndex($product, $item->selected_options ?? []);
                if ($variantIndex !== null) {
                    $variants = $product->variants;
                    $variant = $variants[$variantIndex];
                    $available = max(0, (int) $variant['stock'] - (int) ($variant['reserved_stock'] ?? 0));
                    if ($available < $item->quantity) {
                        throw new InsufficientStockException("Stock insuficiente para la combinación seleccionada de \"{$product->name}\".");
                    }
                    $variants[$variantIndex]['reserved_stock'] = (int) ($variant['reserved_stock'] ?? 0) + $item->quantity;
                    $product->variants = $variants;
                    $product->save();
                } elseif ($product->variants) {
                    throw new InsufficientStockException("La combinación seleccionada de \"{$product->name}\" ya no está disponible.");
                }
                if ($product->availableStock() < $item->quantity) {
                    throw new InsufficientStockException("Stock insuficiente para \"{$product->name}\" (disponible: {$product->availableStock()}).");
                }
                $product->increment('reserved_stock', $item->quantity);
            }
            $order->forceFill(['stock_reserved' => true])->save();
        });
    }

    /** Pago aprobado: la reserva se convierte en descuento real de stock. */
    public function commit(Order $order): void
    {
        if (!$order->stock_reserved) {
            return;
        }

        foreach ($order->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            if (!$product) {
                continue;
            }
            $product->stock = max(0, $product->stock - $item->quantity);
            $product->reserved_stock = max(0, $product->reserved_stock - $item->quantity);
            $variantIndex = $this->variantIndex($product, $item->selected_options ?? []);
            if ($variantIndex !== null) {
                $variants = $product->variants;
                $variants[$variantIndex]['stock'] = max(0, (int) $variants[$variantIndex]['stock'] - $item->quantity);
                $variants[$variantIndex]['reserved_stock'] = max(0, (int) ($variants[$variantIndex]['reserved_stock'] ?? 0) - $item->quantity);
                $product->variants = $variants;
            }
            $product->save();

            if ($product->stock <= 0) {
                event(new \App\Events\ProductOutOfStock($product));
            }
            if ($product->isLowStock()) {
                SendLowStockAlert::dispatch($product->id)->afterCommit();
            }
        }
        $order->forceFill(['stock_reserved' => false])->save();
    }

    /** Pago rechazado / error / reserva vencida: libera lo reservado. */
    public function release(Order $order): void
    {
        if (!$order->stock_reserved) {
            return;
        }

        foreach ($order->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            if ($product) {
                $product->reserved_stock = max(0, $product->reserved_stock - $item->quantity);
                $variantIndex = $this->variantIndex($product, $item->selected_options ?? []);
                if ($variantIndex !== null) {
                    $variants = $product->variants;
                    $variants[$variantIndex]['reserved_stock'] = max(0, (int) ($variants[$variantIndex]['reserved_stock'] ?? 0) - $item->quantity);
                    $product->variants = $variants;
                }
                $product->save();
            }
        }
        $order->forceFill(['stock_reserved' => false])->save();
    }

    private function variantIndex(Product $product, array $options): ?int
    {
        foreach ($product->variants ?? [] as $index => $variant) {
            $expected = $variant['options'] ?? [];
            ksort($expected);
            $selected = $options;
            ksort($selected);
            if ($expected === $selected) {
                return $index;
            }
        }
        return null;
    }
}
