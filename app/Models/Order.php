<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id','total','discount','points_used','points_earned',
        'status','shipping_address','payment_method',
        'payment_status','notes',
    ];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    // status: pending | processing | shipped | delivered | cancelled
    // payment_status: pending | paid | failed

    public function user()  { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
}
