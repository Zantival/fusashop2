<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'merchant_id', 'product_id', 'quantity',
        'selected_options', 'preferred_date', 'preferred_time', 'party_size',
        'customer_notes', 'merchant_notes', 'status', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_options' => 'array',
            'preferred_date' => 'date',
            'responded_at' => 'datetime',
        ];
    }

    public function customer() { return $this->belongsTo(User::class, 'user_id'); }
    public function merchant() { return $this->belongsTo(User::class, 'merchant_id')->withTrashed(); }
    public function product() { return $this->belongsTo(Product::class); }
}
