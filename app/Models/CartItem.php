<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'selected_options', 'unit_price'];

    protected $casts = [
        'selected_options' => 'array',
        'unit_price' => 'decimal:2',
    ];

    public function cart()    { return $this->belongsTo(Cart::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
