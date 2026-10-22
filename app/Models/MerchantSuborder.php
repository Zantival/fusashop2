<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantSuborder extends Model
{
    protected $fillable = [
        'order_id', 'merchant_id', 'reference', 'items_snapshot', 'gross_cents',
        'commission_cents', 'net_cents', 'currency', 'status', 'payout_status', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['items_snapshot' => 'array', 'paid_at' => 'datetime'];
    }

    public function order() { return $this->belongsTo(Order::class); }
    public function merchant() { return $this->belongsTo(User::class, 'merchant_id'); }
    public function walletEntries() { return $this->hasMany(MerchantWalletEntry::class, 'suborder_id'); }
}
