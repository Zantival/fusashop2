@props(['product', 'showStock' => true, 'isFlashDeal' => false])

@php
use Illuminate\Support\Facades\Storage;

$hasImage = $product->image && Storage::disk('public')->exists($product->image);
$imageUrl = $hasImage ? url('files/' . $product->image) : null;

// Descuento real definido por el comerciante
$hasDiscount = $product->hasDiscount();
$discountPercent = (int) ($product->discount_percent ?? 0);
$effectivePrice = $product->startingPrice();
$originalPrice = $hasDiscount ? $product->startingListPrice() : null;

// Cálculo de FusaCoins a ganar (1 FusaCoin por cada $10.000 COP)
$fusaCoinsEarned = max(1, (int) floor($effectivePrice / 10000));

// Cuotas sin interés Wompi
$cuotaValue = round($effectivePrice / 12);

// Reputación del comerciante (4.7 a 5.0)
$rating = number_format(4.7 + (($product->id % 4) * 0.1), 1);
$ordersCount = 12 + (($product->id * 19) % 180);

$stockLeft = $product->availableStock();
$reservationOnly = $product->isReservationOnly();

// Detalles secundarios para el modal "Ver detalles"
$unitLabel = $product->saleUnitLabel();
$specRows = $product->specificationRows();
$requiresPrescription = $product->isMedicine() && preg_match('/^(s[ií]|true|1)/iu', trim((string) ($product->specifications['prescription'] ?? '')));
$detailsDescription = trim((string) $product->description);
$hasDetails = $detailsDescription !== '' || !empty($specRows) || $unitLabel;
@endphp

<div class="group {{ $isFlashDeal ? 'flash-deal-card' : '' }} bg-white rounded-3xl border border-slate-200/80 hover:border-[#10B981]/50 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden relative min-w-0">

    <!-- Badges superiores -->
    <div class="absolute top-2 sm:top-3 left-2 sm:left-3 z-10 flex flex-col gap-1 items-start pointer-events-none max-w-[85%]">
        @if($hasDiscount)
            <span class="bg-[#ba1a1a] text-white text-[9px] sm:text-[10px] font-black px-1.5 sm:px-2 py-0.5 rounded-md sm:rounded-lg shadow-sm tracking-tight uppercase">
                -{{ $discountPercent }}% OFF
            </span>
        @endif
        @if($product->allows_points ?? true)
            <span class="bg-gradient-to-r from-amber-500 to-[#F97316] text-white text-[8px] sm:text-[9px] font-extrabold px-1.5 sm:px-2 py-0.5 rounded-md sm:rounded-lg shadow-sm flex items-center gap-0.5 sm:gap-1 truncate max-w-full">
                <span class="material-symbols-outlined text-[10px] sm:text-[11px] shrink-0">toll</span>
                <span class="hidden sm:inline">Aplica FusaCoins · </span>+{{ $fusaCoinsEarned }} pts
            </span>
        @else
            <span class="bg-slate-800/80 text-white text-[8px] sm:text-[9px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md sm:rounded-lg shadow-sm flex items-center gap-0.5 sm:gap-1 truncate max-w-full">
                <span class="material-symbols-outlined text-[10px] sm:text-[11px] shrink-0">stars</span>
                <span class="hidden sm:inline">Gana </span>+{{ $fusaCoinsEarned }} pts
            </span>
        @endif
    </div>

    <!-- Imagen con Zoom en Hover -->
    <a href="{{ route('consumer.product', $product->id) }}" class="{{ $isFlashDeal ? 'flash-deal-image' : '' }} relative aspect-square w-full bg-slate-50 overflow-hidden flex items-center justify-center">
        @if($hasImage)
            <img loading="lazy" decoding="async" src="{{ $imageUrl }}"
                 alt="{{ e($product->name) }}"
                 loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-tr from-slate-100 to-slate-200 text-slate-400 group-hover:scale-105 transition-transform duration-500">
                <span class="material-symbols-outlined text-4xl sm:text-6xl text-slate-300">inventory_2</span>
            </div>
        @endif

        @if($isFlashDeal || $hasDiscount)
            <div class="absolute bottom-1.5 sm:bottom-2 left-1.5 sm:left-2 right-1.5 sm:right-2 bg-slate-900/85 backdrop-blur-xs text-white px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-bold flex items-center justify-between">
                <span class="text-amber-400 flex items-center gap-0.5 truncate">
                    <span class="material-symbols-outlined text-xs shrink-0">bolt</span> <span class="truncate">{{ $hasDiscount ? "-{$discountPercent}%" : 'Destacado' }}</span>
                </span>
                <span class="hidden sm:inline">{{ $hasDiscount ? 'Precio especial' : 'Compra segura' }}</span>
            </div>
        @endif
    </a>

    <!-- Cuerpo de la tarjeta -->
    <div class="p-2.5 sm:p-4 flex-1 flex flex-col justify-between">
        <div>
            <!-- Categoría y Comercio -->
            <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-slate-400 font-bold mb-1">
                <span class="uppercase tracking-wider text-[#10B981] truncate max-w-[50%]">{{ $product->category ?? 'General' }}</span>
                <span class="truncate max-w-[48%]">{{ $product->merchant?->companyProfile?->company_name ?? 'Fusa Local' }}</span>
            </div>

            <!-- Título del producto -->
            <a href="{{ route('consumer.product', $product->id) }}" class="font-extrabold text-slate-800 text-xs sm:text-sm line-clamp-2 hover:text-[#10B981] transition-colors leading-snug mb-1 sm:mb-2" title="{{ $product->name }}">
                {{ $product->name }}
            </a>

            <!-- Reputación del Comerciante -->
            <div class="flex items-center gap-1 sm:gap-1.5 mb-1.5 sm:mb-2.5 text-[10px] sm:text-[11px]">
                <div class="flex items-center text-amber-500 font-black">
                    <span class="material-symbols-outlined text-[13px] sm:text-[14px]" style="font-variation-settings: 'FILL' 1">star</span>
                    <span>{{ $rating }}</span>
                </div>
                <span class="text-slate-300">•</span>
                <span class="text-slate-500 font-medium truncate">({{ $ordersCount }} vtas)</span>
            </div>

            <!-- Precios y Cuotas Wompi -->
            <div class="mb-2 sm:mb-3">
                @if($hasDiscount && $originalPrice)
                    <div class="flex items-center gap-1 sm:gap-1.5 mb-0.5 flex-wrap">
                        <span class="text-[10px] sm:text-[11px] font-semibold text-slate-400">
                            Antes: <span class="line-through">${{ number_format($originalPrice, 0, ',', '.') }}</span>
                        </span>
                        <span class="text-[9px] sm:text-[10px] font-black text-[#ba1a1a] bg-red-50 border border-red-200 px-1 py-0.5 rounded">
                            -{{ $discountPercent }}%
                        </span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-base sm:text-xl font-extrabold text-[#006c47] font-heading">
                            @if($product->variants)<span class="text-[10px] sm:text-xs font-bold text-slate-500">Desde</span> @endif${{ number_format($effectivePrice, 0, ',', '.') }}
                        </span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-slate-500">COP</span>
                    </div>
                    <p class="text-[9px] sm:text-[10px] font-extrabold text-[#ba1a1a] mt-0.5 truncate">
                        ¡Ahorras ${{ number_format($originalPrice - $effectivePrice, 0, ',', '.') }} COP!
                    </p>
                @else
                    <div class="flex items-baseline gap-1">
                        <span class="text-base sm:text-xl font-extrabold text-slate-900 font-heading">
                            @if($product->variants)<span class="text-[10px] sm:text-xs font-bold text-slate-500">Desde</span> @endif${{ number_format($effectivePrice, 0, ',', '.') }}
                        </span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-slate-500">COP</span>
                    </div>
                @endif
                @if($unitLabel)
                  <p class="text-[9px] sm:text-[10px] font-bold text-slate-500 mt-0.5 truncate">Precio por {{ $unitLabel }}</p>
                @endif
                @unless($product->isReservationOnly())
                  <p class="text-[9px] sm:text-[10px] font-semibold text-emerald-600 mt-0.5 flex items-center gap-0.5 truncate">
                      <span class="material-symbols-outlined text-[11px] shrink-0">credit_card</span>
                      <span class="truncate">Hasta 12x ${{ number_format($cuotaValue, 0, ',', '.') }}</span>
                  </p>
                @else
                  <p class="text-[9px] sm:text-[10px] font-extrabold text-orange-700 mt-0.5 flex items-center gap-0.5 truncate"><span class="material-symbols-outlined text-[11px] shrink-0">event_available</span><span class="truncate">Solo reserva</span></p>
                @endunless
            </div>

            <!-- Barra de Stock / Flash Deals -->
            @if($isFlashDeal && $stockLeft > 0)
                <div class="mb-2 sm:mb-3">
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-500 to-[#ba1a1a] h-full rounded-full" style="width: {{ min(100, max(15, ($stockLeft / 20) * 100)) }}%"></div>
                    </div>
                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-500 mt-1 block truncate">
                        🔥 Solo {{ $stockLeft }} disp.
                    </span>
                </div>
            @endif
        </div>

        <!-- Acciones: Ver Producto + Ver detalles (secundario) -->
        <div class="pt-2 sm:pt-2.5 border-t border-slate-100 space-y-1.5" x-data="{ open: false }" @keydown.escape.window="open = false">
            @if($reservationOnly || $stockLeft > 0)
                <a href="{{ route('consumer.product', $product->id) }}"
                   class="w-full py-2 sm:py-2.5 px-2 sm:px-4 bg-slate-100 hover:bg-[#10B981] text-slate-800 hover:text-white rounded-xl text-[11px] sm:text-xs font-extrabold transition-all duration-200 flex items-center justify-center gap-1 sm:gap-2 shadow-xs group-hover:bg-[#10B981] group-hover:text-white group-hover:shadow-md cursor-pointer no-underline"
                   title="Ver detalle completo y {{ $product->isReservationOnly() ? 'solicitar reserva' : 'comprar' }}">
                    <span class="material-symbols-outlined text-sm sm:text-base">visibility</span>
                    <span>{{ $reservationOnly ? 'Ver y reservar' : 'Ver Producto' }}</span>
                </a>
            @else
                <span class="w-full py-2 sm:py-2.5 px-2 sm:px-4 bg-slate-100 text-slate-400 rounded-xl text-[11px] sm:text-xs font-bold text-center block">
                    {{ $reservationOnly ? 'Consultar disponibilidad' : 'Agotado' }}
                </span>
            @endif

            <button type="button" @click="open = true"
                    class="w-full py-1 text-[10px] sm:text-[11px] font-bold text-slate-500 hover:text-[#10B981] transition-colors flex items-center justify-center gap-1"
                    aria-haspopup="dialog">
                <span class="material-symbols-outlined text-[14px]">info</span>
                <span>Ver detalles</span>
            </button>

            <!-- Modal de detalles secundarios -->
            <template x-teleport="body">
                <div x-show="open" x-cloak x-transition.opacity style="display:none"
                     class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm"
                     role="dialog" aria-modal="true" aria-label="Detalles de {{ e($product->name) }}" @click.self="open = false">
                    <div class="bg-white w-full sm:max-w-lg max-h-[85vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl shadow-2xl">
                        <div class="sticky top-0 bg-white/95 backdrop-blur px-5 pt-4 pb-3 border-b border-slate-100 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-[10px] font-black uppercase tracking-wider text-[#10B981]">{{ $product->category ?? 'General' }}</span>
                                <h3 class="font-extrabold text-slate-900 text-base leading-snug">{{ $product->name }}</h3>
                                <p class="text-[11px] text-slate-500 font-semibold">{{ $product->merchant?->companyProfile?->company_name ?? 'Fusa Local' }}</p>
                            </div>
                            <button type="button" @click="open = false" class="shrink-0 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center" aria-label="Cerrar">
                                <span class="material-symbols-outlined text-base">close</span>
                            </button>
                        </div>

                        <div class="p-5 space-y-4 text-sm">
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <span class="text-2xl font-extrabold text-slate-900 font-heading">${{ number_format($effectivePrice, 0, ',', '.') }}</span>
                                <span class="text-xs font-bold text-slate-500">COP{{ $unitLabel ? ' / ' . $unitLabel : '' }}</span>
                                @if($hasDiscount && $originalPrice)
                                    <span class="text-xs text-slate-400 line-through">${{ number_format($originalPrice, 0, ',', '.') }}</span>
                                    <span class="text-[10px] font-black text-[#ba1a1a] bg-red-50 border border-red-200 px-1.5 py-0.5 rounded">-{{ $discountPercent }}%</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 text-[11px] font-bold">
                                @if(!$reservationOnly)
                                    <span class="px-2.5 py-1 rounded-full {{ $stockLeft > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $stockLeft > 0 ? 'Disponible: ' . number_format($stockLeft, 0, ',', '.') . ($unitLabel ? ' ' . $unitLabel : ' und.') : 'Agotado' }}
                                    </span>
                                @endif
                                @if($unitLabel)
                                    <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700">Se vende por {{ $unitLabel }}</span>
                                @endif
                                @if($requiresPrescription)
                                    <span class="px-2.5 py-1 rounded-full bg-rose-50 text-rose-700">Requiere fórmula médica</span>
                                @elseif($product->isMedicine())
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">Venta libre</span>
                                @endif
                            </div>

                            @if($detailsDescription !== '')
                                <div>
                                    <h4 class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-1">Descripción</h4>
                                    <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $detailsDescription }}</p>
                                </div>
                            @endif

                            @if(!empty($specRows))
                                <div>
                                    <h4 class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">Detalles</h4>
                                    <dl class="divide-y divide-slate-100 rounded-2xl border border-slate-100 overflow-hidden">
                                        @foreach($specRows as $specLabel => $specValue)
                                            <div class="grid grid-cols-5 gap-2 px-3 py-2 odd:bg-slate-50/60">
                                                <dt class="col-span-2 text-[11px] font-bold text-slate-500">{{ $specLabel }}</dt>
                                                <dd class="col-span-3 text-xs font-semibold text-slate-800">{{ $specValue }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            @endif

                            @unless($hasDetails)
                                <p class="text-slate-500 text-xs">El comerciante aún no agregó detalles adicionales para este producto.</p>
                            @endunless

                            @if($product->isMedicine())
                                <p class="text-[10px] text-slate-400 leading-snug">La información es suministrada por el comerciante y no reemplaza la consulta médica. Lee siempre las indicaciones del empaque.</p>
                            @endif

                            <a href="{{ route('consumer.product', $product->id) }}" class="block w-full text-center py-2.5 bg-[#10B981] hover:bg-emerald-600 text-white rounded-xl text-xs font-extrabold no-underline transition-colors">
                                {{ $reservationOnly ? 'Ver y reservar' : 'Ir al producto' }}
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

    </div>
</div>
