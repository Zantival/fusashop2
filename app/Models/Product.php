<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'merchant_id', 'name', 'slug', 'description', 'price', 'discount_percent', 'stock',
        'category', 'type', 'sector_key', 'subsector_key', 'views_count',
        'image', 'available_options', 'variants', 'images', 'is_active', 'allows_points', 'specifications',
        'reserved_stock', 'low_stock_threshold', 'weight_grams', 'length_cm', 'width_cm', 'height_cm', 'is_taxed',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_percent' => 'integer',
            'is_active' => 'boolean',
            'allows_points' => 'boolean',
            'is_taxed' => 'boolean',
            'available_options' => 'array',
            'variants' => 'array',
            'images' => 'array',
            'specifications' => 'array',
        ];
    }

    public function merchant() { return $this->belongsTo(User::class, 'merchant_id')->withTrashed(); }
    public function cartItems() { return $this->hasMany(CartItem::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeOnSale($q) { return $q->where('discount_percent', '>', 0); }
    public function scopeSearch($q, $term) {
        return $q->where(function($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%");
        });
    }
    public function reviews() { return $this->hasMany(Review::class); }

    public function hasDiscount(): bool
    {
        return (int) ($this->discount_percent ?? 0) > 0;
    }

    public function originalPrice(): float
    {
        return (float) $this->price;
    }

    /** Precio final efectivo aplicando el porcentaje de descuento del comerciante (si existe). */
    public function effectivePrice(): float
    {
        $basePrice = (float) $this->price;
        $pct = min(90, max(0, (int) ($this->discount_percent ?? 0)));
        if ($pct > 0) {
            return round($basePrice * (1 - ($pct / 100)), 0);
        }
        return $basePrice;
    }

    public function discountAmount(): float
    {
        return max(0, $this->originalPrice() - $this->effectivePrice());
    }

    public function isService(): bool
    {
        if (strtolower((string) ($this->type ?? '')) === 'service') {
            return true;
        }

        $cat = mb_strtolower($this->category ?? '', 'UTF-8');
        foreach (['servicio', 'glamping', 'turismo', 'hotel', 'alojamiento', 'restaurante', 'gastronom', 'cafeter', 'reparación', 'clases', 'consultoría', 'transporte', 'evento', 'experiencia', 'spa'] as $kw) {
            if (str_contains($cat, $kw)) {
                return true;
            }
        }

        // Los comercios de restaurante solo reciben solicitudes de mesa desde
        // sus publicaciones; otros comercios pueden vender productos físicos.
        if ($this->merchant?->companyProfile?->isRestaurantBusiness()) {
            return true;
        }

        if (strtolower((string) ($this->type ?? '')) === 'product') {
            return false;
        }

        return (bool) $this->merchant?->companyProfile?->isReservationBusiness();
    }

    public function isReservationOnly(): bool
    {
        return $this->isService();
    }

    /** Unidades realmente vendibles (stock físico menos reservas de pedidos pendientes). */
    public function availableStock(): int
    {
        return max(0, (int) $this->stock - (int) $this->reserved_stock);
    }

    public function variantForOptions(array $options): ?array
    {
        if (!$options && count($this->variants ?? []) === 1) {
            return $this->variants[0];
        }
        foreach ($this->variants ?? [] as $variant) {
            $variantOptions = $variant['options'] ?? [];
            ksort($variantOptions);
            $selected = $options;
            ksort($selected);
            if ($variantOptions === $selected) {
                return $variant;
            }
        }

        return null;
    }

    public function priceForOptions(array $options): float
    {
        $price = $this->listPriceForOptions($options);
        $discount = min(90, max(0, (int) ($this->discount_percent ?? 0)));
        return $discount > 0 ? round($price * (1 - $discount / 100), 0) : $price;
    }

    public function listPriceForOptions(array $options): float
    {
        $variant = $this->variantForOptions($options);
        return (float) ($variant['price'] ?? $this->price);
    }

    public function startingPrice(): float
    {
        if (!$this->variants) {
            return $this->effectivePrice();
        }
        $variants = array_values(array_filter($this->variants, fn ($variant) => (int) ($variant['stock'] ?? 0) > (int) ($variant['reserved_stock'] ?? 0)));
        $variants = $variants ?: $this->variants;
        return min(array_map(fn ($variant) => $this->priceForOptions($variant['options'] ?? []), $variants));
    }

    public function startingListPrice(): float
    {
        if (!$this->variants) {
            return $this->originalPrice();
        }
        $variants = array_values(array_filter($this->variants, fn ($variant) => (int) ($variant['stock'] ?? 0) > (int) ($variant['reserved_stock'] ?? 0)));
        $variants = $variants ?: $this->variants;
        return min(array_map(fn ($variant) => $this->listPriceForOptions($variant['options'] ?? []), $variants));
    }

    public function validOptionSelection(array $options): bool
    {
        $expected = [];
        foreach ($this->available_options ?? [] as $option) {
            $name = (string) ($option['name'] ?? '');
            $values = array_map(fn ($value) => (string) (is_array($value) ? ($value['name'] ?? '') : $value), $option['values'] ?? []);
            if ($name !== '' && $values) {
                $expected[$name] = $values;
            }
        }
        if (!$expected) {
            return $options === [];
        }
        if (array_diff_key($options, $expected) || array_diff_key($expected, $options)) {
            return false;
        }
        foreach ($expected as $name => $values) {
            if (!in_array((string) ($options[$name] ?? ''), $values, true)) {
                return false;
            }
        }
        return true;
    }

    public function isLowStock(): bool
    {
        return $this->availableStock() <= (int) $this->low_stock_threshold;
    }
}
