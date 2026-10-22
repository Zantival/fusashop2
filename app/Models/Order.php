<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id','merchant_id','total','discount','points_used','points_earned',
        'status','shipping_address','payment_method',
        'payment_status','notes',
        'reference','subtotal','shipping_cost','commission_amount','currency',
        'shipping_address_id','shipping_snapshot','shipping_postal_code','shipping_carrier','shipping_service',
        'customer_document_type','customer_document',
        'wompi_transaction_id','wompi_status','stock_reserved','reservation_expires_at','paid_at','packed_at',
        'tracking_number','tracking_url','label_url','tracking_status','shipped_at','delivered_at',
        'invoice_number','invoice_cufe','invoice_pdf_path','invoice_xml_path','invoiced_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'shipping_snapshot' => 'array',
            'stock_reserved' => 'boolean',
            'reservation_expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'invoiced_at' => 'datetime',
        ];
    }

    // status: pending | processing | shipped | delivered | cancelled
    // payment_status: pending | paid | failed
    // Etapas visibles: Pendiente(pending) · Pagado(processing) · En empaque(processing+packed_at)
    //                  · Enviado(shipped) · Entregado(delivered)

    public function user()     { return $this->belongsTo(User::class); }
    public function merchant() { return $this->belongsTo(User::class, 'merchant_id')->withTrashed(); }
    public function items()    { return $this->hasMany(OrderItem::class); }
    public function transactions() { return $this->hasMany(PaymentTransaction::class); }
    public function suborders() { return $this->hasMany(MerchantSuborder::class); }
    public function integrationLogs() { return $this->hasMany(IntegrationLog::class); }

    public function merchantProfile(): ?CompanyProfile
    {
        $merchantIds = $this->items
            ->map(fn ($item) => $item->product?->merchant_id)
            ->filter()
            ->unique();

        // Las credenciales de un vendedor no deben cobrar un pedido que incluye productos de otros vendedores.
        if ($merchantIds->count() > 1) {
            return null;
        }

        if ($this->merchant && $this->merchant->companyProfile) {
            return $this->merchant->companyProfile;
        }

        $merchantUser = $this->items->first()?->product?->merchant;
        return $merchantUser?->companyProfile;
    }

    public function hasServiceItems(): bool
    {
        return $this->items->contains(fn ($item) => $item->product?->isService());
    }

    public function hasShippableItems(): bool
    {
        return $this->items->contains(fn ($item) => $item->product && !$item->product->isService());
    }

    public function isServiceReservation(): bool
    {
        return $this->items->isNotEmpty() && !$this->hasShippableItems();
    }

    public function totalInCents(): int
    {
        return (int) round(((float) $this->total) * 100);
    }

    public function stageLabel(): string
    {
        return match (true) {
            $this->status === 'cancelled' => 'Cancelado',
            $this->status === 'delivered' => 'Entregado',
            $this->status === 'shipped' => 'Enviado',
            $this->status === 'processing' && $this->packed_at !== null => 'En empaque',
            $this->payment_status === 'paid' => 'Pagado',
            default => 'Pendiente',
        };
    }

    public function trackingStep(): int
    {
        return match (true) {
            $this->status === 'delivered' => 4,
            $this->status === 'shipped' || !empty($this->tracking_number) => 3,
            $this->packed_at !== null || $this->status === 'processing' => 2,
            $this->payment_status === 'paid' => 1,
            default => 0,
        };
    }

    /**
     * Determina si el comprador puede cancelar el pedido directamente
     * (mientras esté pendiente o en preparación y no tenga guía de transporte ni haya sido despachado).
     */
    public function canBeCancelledByBuyer(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true)
            && empty($this->tracking_number);
    }
}
