<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'merchant_id', 'name', 'slug', 'description', 'price', 'stock',
        'category', 'image', 'available_options', 'images', 'is_active', 'specifications',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'available_options' => 'array',
            'images' => 'array',
            'specifications' => 'array',
        ];
    }

    public function merchant() { return $this->belongsTo(User::class, 'merchant_id'); }
    public function cartItems() { return $this->hasMany(CartItem::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeSearch($q, $term) {
        return $q->where(function($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%");
        });
    }
    public function reviews() { return $this->hasMany(Review::class); }
}
