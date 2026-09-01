@props(['product', 'showStock' => true])
@php
use Illuminate\Support\Facades\Storage;

$cats = [
    'Ropa'        => ['checkroom',     '#7c3aed', '#a855f7', 'rgba(124,58,237,0.12)'],
    'Electrónica' => ['devices',       '#1d4ed8', '#06b6d4', 'rgba(29,78,216,0.12)'],
    'Hogar'       => ['chair',         '#d97706', '#f59e0b', 'rgba(217,119,6,0.12)'],
    'Deportes'    => ['sports_soccer', '#16a34a', '#22c55e', 'rgba(22,163,74,0.12)'],
    'Alimentos'   => ['lunch_dining',  '#ca8a04', '#84cc16', 'rgba(202,138,4,0.12)'],
    'Belleza'     => ['spa',           '#db2777', '#f43f5e', 'rgba(219,39,119,0.12)'],
    'Juguetes'    => ['toys',          '#ea580c', '#fb923c', 'rgba(234,88,12,0.12)'],
    'Libros'      => ['menu_book',     '#0f766e', '#14b8a6', 'rgba(15,118,110,0.12)'],
];
$c = $cats[$product->category] ?? ['inventory_2', '#006c47', '#00b67a', 'rgba(0,108,71,0.12)'];
$icon = $c[0];

$hasImage = $product->image && Storage::disk('public')->exists($product->image);
$imageUrl = $hasImage ? url('files/' . $product->image) : null;
@endphp

<div class="product-card" style="--clr:{{ $c[1] }};--clr2:{{ $c[2] }};--clr-bg:{{ $c[3] }};">
    <a href="{{ route('consumer.product', $product->id) }}" class="card__image-wrap">

        {{-- Imagen o ícono - ocupa TODO el espacio, se encoge al hover --}}
        <div class="card__img">
            @if($hasImage)
                <img src="{{ $imageUrl }}" alt="{{ e($product->name) }}"
                     loading="lazy" decoding="async"
                     onerror="this.style.display='none';this.nextElementSibling.style.removeProperty('display')"/>
                <div class="card__icon-fallback" style="display:none;">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1,'wght' 300">{{ $icon }}</span>
                </div>
            @else
                <div class="card__icon-fallback">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1,'wght' 300">{{ $icon }}</span>
                </div>
            @endif
        </div>

        {{-- Overlay con el nombre al hacer hover --}}
        <div class="card__hover-label">
            <span class="material-symbols-outlined text-[18px]">visibility</span>
            Ver producto
        </div>
    </a>

    <div class="card__body">
        <span class="card__category">{{ $product->category }}</span>
        <a href="{{ route('consumer.product', $product->id) }}" class="card__name">{{ e($product->name) }}</a>
        <div class="card__footer">
            <span class="card__price">${{ number_format($product->price, 0, ',', '.') }}</span>
            @if($product->stock > 0)
                <form method="POST" action="{{ route('consumer.cart.add') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}"/>
                    <button type="submit" class="card__btn" title="Agregar al carrito">
                        <span class="material-symbols-outlined">add_shopping_cart</span>
                    </button>
                </form>
            @else
                <span class="card__out">Agotado</span>
            @endif
        </div>
        @if($showStock)
        <span class="card__stock {{ $product->stock == 0 ? 'out' : ($product->stock <= 5 ? 'low' : 'ok') }}">
            {{ $product->stock == 0 ? 'Sin stock' : ($product->stock <= 5 ? '⚠ Solo '.$product->stock.' uds.' : '✓ '.$product->stock.' disp.') }}
        </span>
        @endif
    </div>
</div>
