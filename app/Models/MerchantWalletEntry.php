<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantWalletEntry extends Model
{
    protected $fillable = [
        'merchant_id', 'order_id', 'suborder_id', 'entry_type', 'direction',
        'amount_cents', 'currency', 'idempotency_key', 'metadata', 'posted_at',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'posted_at' => 'datetime'];
    }

    public function merchant() { return $this->belongsTo(User::class, 'merchant_id'); }
    public function order() { return $this->belongsTo(Order::class); }
    public function suborder() { return $this->belongsTo(MerchantSuborder::class, 'suborder_id'); }
}
