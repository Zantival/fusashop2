@extends('layouts.app')
@section('title','Inicio')
@section('content')

@php
  // Map category names to Material Symbols icons
  $iconMap = [
      'Tecnología' => 'devices',
      'Celulares' => 'smartphone',
      'Computadores' => 'laptop_mac',
      'Televisores' => 'tv',
      'Electrónica' => 'devices',
      'Electrohogar' => 'kitchen',
      'Electro' => 'kitchen',
      'Hogar' => 'home',
      'Cocina' => 'kitchen',
      'Moda' => 'apparel',
      'Ropa' => 'apparel',
      'Deportes' => 'sports_soccer',
      'Alimentos' => 'eco',
      'Orgánico' => 'local_mall',
  ];
@endphp

{{-- ── 1. Banner Carousel Principal (Inicio del Dashboard) ── --}}
@if(isset($globalBanners) && $globalBanners->isNotEmpty())
  @php
    // Use the first 2 banners for the top carousel
    $topBanners = $globalBanners->take(2);
    // The rest will go in the middle of the dashboard
    $midBanners = $globalBanners->slice(2);
  @endphp
  <div class="w-full relative bg-surface border-b border-surface-container-high shadow-sm" id="global-banner-wrap">
    <div class="grid w-full">
      @foreach($topBanners as $gi => $gb)
        @php
          $url = asset('storage/'.$gb->image_path);
          $isVideo = in_array(pathinfo($gb->image_path, PATHINFO_EXTENSION), ['mp4', 'webm', 'mov']);
        @endphp
        <div class="col-start-1 row-start-1 transition-opacity duration-1000 ease-in-out {{ $gi===0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" id="gb-{{$gi}}">
          @if($gb->link_url)
            <a href="{{ $gb->link_url }}" target="_blank" class="block w-full h-full">
              @if($isVideo)
                <video src="{{ $url }}" class="w-full h-auto block" autoplay loop muted playsinline></video>
              @else
                <img src="{{ $url }}" class="w-full h-auto block" alt="{{ $gb->title ?? 'Banner' }}" 
                     loading="{{ $gi===0 ? 'eager' : 'lazy' }}" 
                     fetchpriority="{{ $gi===0 ? 'high' : 'auto' }}"
                     decoding="async">
              @endif
            </a>
          @else
            @if($isVideo)
              <video src="{{ $url }}" class="w-full h-auto block" autoplay loop muted playsinline></video>
            @else
              <img src="{{ $url }}" class="w-full h-auto block" alt="{{ $gb->title ?? 'Banner' }}" 
                   loading="{{ $gi===0 ? 'eager' : 'lazy' }}" 
                   fetchpriority="{{ $gi===0 ? 'high' : 'auto' }}"
                   decoding="async">
            @endif
          @endif

          @if(($gb->title && $gb->title !== 'Bienvenido') || (isset($gb->subtitle) && $gb->subtitle))
            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-10 pointer-events-none bg-gradient-to-t from-black/50 to-transparent">
              <div class="max-w-7xl mx-auto">
                @if($gb->title && $gb->title !== 'Bienvenido')
                  <p class="text-white font-black text-2xl md:text-5xl drop-shadow-lg leading-tight mb-2" style="font-family:'Manrope',sans-serif">{{ $gb->title }}</p>
                @endif
                @if(isset($gb->subtitle) && $gb->subtitle)
                  <p class="text-white/85 text-sm md:text-xl font-medium drop-shadow">{{ $gb->subtitle }}</p>
                @endif
              </div>
            </div>
          @endif
        </div>
      @endforeach
    </div>

    {{-- Dot indicators --}}
    @if($topBanners->count() > 1)
      <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2" id="gb-dots">
        @foreach($topBanners as $gi => $gb)
          <button onclick="gbGoTo({{ $gi }})" id="gb-dot-{{ $gi }}" class="w-2 h-2 rounded-full transition-all duration-300 {{ $gi===0 ? 'bg-white scale-125' : 'bg-white/40' }}"></button>
        @endforeach
      </div>
    @endif
  </div>

  @if($topBanners->count() > 1)
  <script>
  (function(){
    let ci = 0, total = {{ $topBanners->count() }}, timer;
    function gbGoTo(idx) {
      document.getElementById('gb-'+ci)?.classList.replace('opacity-100','opacity-0');
      document.getElementById('gb-'+ci)?.classList.replace('z-10','z-0');
      document.getElementById('gb-dot-'+ci)?.classList.remove('bg-white','scale-125');
      document.getElementById('gb-dot-'+ci)?.classList.add('bg-white/40');
      ci = (idx + total) % total;
      document.getElementById('gb-'+ci)?.classList.replace('opacity-0','opacity-100');
      document.getElementById('gb-'+ci)?.classList.replace('z-0','z-10');
      document.getElementById('gb-dot-'+ci)?.classList.remove('bg-white/40');
      document.getElementById('gb-dot-'+ci)?.classList.add('bg-white','scale-125');
    }
    window.gbGoTo = gbGoTo;
    timer = setInterval(() => gbGoTo(ci + 1), 5000);
  })();
  </script>
  @endif
@else
  {{-- Fallback hero banner when no banners configured --}}
  <div class="w-full relative overflow-hidden" style="height:clamp(220px,35vw,420px);background:linear-gradient(135deg,#004d33 0%,#006c47 50%,#00b67a 100%)">
    <div class="absolute inset-0 opacity-10" style="background-image:url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6">
      <span class="inline-block px-4 py-1.5 rounded-full bg-white/20 text-white text-xs font-bold tracking-widest uppercase mb-4">Bienvenido a FusaShop</span>
      <h2 class="text-white font-black text-3xl md:text-5xl leading-tight mb-3" style="font-family:'Manrope',sans-serif">Cultivando tu<br><span class="text-[#6efcb9]">Crecimiento Digital.</span></h2>
      <p class="text-white/80 text-sm md:text-lg max-w-lg">Descubre productos únicos de comerciantes locales de Fusagasugá.</p>
      <a href="{{ route('consumer.catalog') }}" class="mt-6 inline-flex items-center gap-2 px-7 py-3 bg-white text-[#006c47] font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all text-sm md:text-base no-underline">
        <span class="material-symbols-outlined text-sm">storefront</span> Ver Catálogo
      </a>
    </div>
  </div>
@endif

{{-- ── 2. Cinta Horizontal de Beneficios (Ribbon) ── --}}
<div class="w-full text-white py-4 border-y border-[#6efcb9]/20 shadow-md" style="background-color: #003822;">
  <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8 items-center text-center">
    <div class="flex items-center gap-2.5 justify-center">
      <span class="material-symbols-outlined text-[#6efcb9] text-2xl">storefront</span>
      <div class="text-left">
        <p class="text-xs md:text-sm font-black tracking-wide leading-tight">100% Comercio Local</p>
        <p class="text-[10px] text-white/70">Apoya MiPymes de Fusagasugá</p>
      </div>
    </div>
    <div class="flex items-center gap-2.5 justify-center">
      <span class="material-symbols-outlined text-[#6efcb9] text-2xl">local_shipping</span>
      <div class="text-left">
        <p class="text-xs md:text-sm font-black tracking-wide leading-tight">Envío Rápido y Seguro</p>
        <p class="text-[10px] text-white/70">Entregas en tiempo récord</p>
      </div>
    </div>
    <div class="flex items-center gap-2.5 justify-center">
      <span class="material-symbols-outlined text-[#6efcb9] text-2xl">verified_user</span>
      <div class="text-left">
        <p class="text-xs md:text-sm font-black tracking-wide leading-tight">Compra Garantizada</p>
        <p class="text-[10px] text-white/70">Pago seguro y protegido</p>
      </div>
    </div>
    <div class="flex items-center gap-2.5 justify-center">
      <span class="material-symbols-outlined text-[#6efcb9] text-2xl">redeem</span>
      <div class="text-left">
        <p class="text-xs md:text-sm font-black tracking-wide leading-tight">Puntos de Regalo</p>
        <p class="text-[10px] text-white/70">Acumula y ahorra dinero</p>
      </div>
    </div>
  </div>
</div>

{{-- ── 3. Categorías en Formato Circular Premium ── --}}
<section class="px-6 py-10 bg-[#f6f3f2] border-b border-surface-container-high">
  <div class="max-w-7xl mx-auto">
    <div class="text-center mb-6">
      <h3 class="text-lg font-bold font-['Manrope'] text-on-surface uppercase tracking-wider">Explora por Categorías</h3>
    </div>
    <div class="flex gap-6 overflow-x-auto pb-4 scrollbar-none justify-start md:justify-center px-4">
      <a href="{{ route('consumer.catalog') }}" class="flex flex-col items-center gap-3 group shrink-0 no-underline">
        <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-white border border-surface-container-high shadow-sm flex items-center justify-center transition-all duration-300 transform group-hover:scale-110 group-hover:shadow-md group-hover:border-primary-light"
             style="box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
          <span class="material-symbols-outlined text-primary text-2xl md:text-3xl transition-transform duration-300 group-hover:rotate-12">grid_view</span>
        </div>
        <span class="text-xs font-bold text-on-surface group-hover:text-primary transition-colors text-center">Todos</span>
      </a>
      @foreach($categories as $cat)
        @php
          $icon = 'shopping_bag';
          foreach($iconMap as $key => $val) {
              if(stripos($cat, $key) !== false) {
                  $icon = $val;
                  break;
              }
          }
        @endphp
        <a href="{{ route('consumer.catalog', ['category' => $cat]) }}" class="flex flex-col items-center gap-3 group shrink-0 no-underline">
          <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-white border border-surface-container-high shadow-sm flex items-center justify-center transition-all duration-300 transform group-hover:scale-110 group-hover:shadow-md group-hover:border-primary-light"
               style="box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <span class="material-symbols-outlined text-primary text-2xl md:text-3xl transition-transform duration-300 group-hover:rotate-12">{{ $icon }}</span>
          </div>
          <span class="text-xs font-bold text-on-surface group-hover:text-primary transition-colors text-center max-w-[90px] truncate">{{ $cat }}</span>
        </a>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 4. Sección de 6 Tarjetas de Ofertas Verticales ("Ofertas para celebrar en grande") ── --}}
<section class="px-6 py-10 bg-[#fcf9f8] relative overflow-hidden">
  <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full blur-3xl pointer-events-none" style="background:radial-gradient(circle,rgba(0,182,122,.08),transparent 70%)"></div>
  <div class="max-w-7xl mx-auto">
    <div class="text-center mb-8">
      <h2 class="text-2xl md:text-3xl font-black text-[#1b1c1c] font-['Manrope'] flex items-center justify-center gap-2">
        🔥 Ofertas para celebrar en grande 🔥
      </h2>
      <p class="text-xs md:text-sm text-[#3c4a41] mt-1">Aprovecha los descuentos exclusivos y apoya a los productores de Fusagasugá</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
      <!-- Tarjeta 1: Gorras y Accesorios -->
      <a href="{{ route('consumer.catalog') }}" class="group relative rounded-[28px] overflow-hidden p-5 flex flex-col justify-between h-[300px] shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 no-underline text-white"
         style="background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.85) 100%), url('https://images.unsplash.com/photo-1531415074968-036ba1b575da?q=80&w=600&auto=format&fit=crop') center/cover no-repeat; border: 1px solid rgba(255, 255, 255, 0.1);">
        <div>
          <span class="inline-block px-3 py-1 bg-red-600 text-white text-[10px] font-black uppercase rounded-lg tracking-wider mb-2 shadow-sm">¡Gratis!</span>
          <h3 class="text-base font-black text-white leading-tight font-['Manrope'] drop-shadow-md">GORRA FUTBOLERA</h3>
          <p class="text-[11px] text-white/90 font-semibold mt-1 leading-snug drop-shadow-sm">Por compras mayores a $50.000</p>
        </div>
        <div class="flex items-end justify-between mt-auto">
          <span class="text-xs font-black text-white uppercase tracking-wider group-hover:underline drop-shadow-sm">Gorra Gratis</span>
          <div class="w-11 h-11 rounded-full border-2 border-white/90 bg-[#ff3b30] flex items-center justify-center text-white shadow-md transition-all duration-300 group-hover:scale-110 group-hover:bg-[#e02d24]">
            <span class="material-symbols-outlined text-[20px]">sports_soccer</span>
          </div>
        </div>
      </a>

      <!-- Tarjeta 2: Celulares -->
      <a href="{{ route('consumer.catalog', ['category' => 'Celulares']) }}" class="group relative rounded-[28px] overflow-hidden p-5 flex flex-col justify-between h-[300px] shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 no-underline text-white"
         style="background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.85) 100%), url('https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=600&auto=format&fit=crop') center/cover no-repeat; border: 1px solid rgba(255, 255, 255, 0.1);">
        <div>
          <span class="inline-block px-3 py-1 bg-red-600 text-white text-[10px] font-black uppercase rounded-lg tracking-wider mb-2 shadow-sm">49% DTO</span>
          <h3 class="text-base font-black text-white leading-tight font-['Manrope'] drop-shadow-md">CELULARES</h3>
          <p class="text-[11px] text-white/90 font-semibold mt-1 leading-snug drop-shadow-sm">Lo mejor en tecnología móvil</p>
        </div>
        <div class="flex items-end justify-between mt-auto">
          <span class="text-xs font-black text-white uppercase tracking-wider group-hover:underline drop-shadow-sm">Celulares</span>
          <div class="w-11 h-11 rounded-full border-2 border-white/90 bg-[#ff3b30] flex items-center justify-center text-white shadow-md transition-all duration-300 group-hover:scale-110 group-hover:bg-[#e02d24]">
            <span class="material-symbols-outlined text-[20px]">smartphone</span>
          </div>
        </div>
      </a>

      <!-- Tarjeta 3: Computadores -->
      <a href="{{ route('consumer.catalog', ['category' => 'Computadores']) }}" class="group relative rounded-[28px] overflow-hidden p-5 flex flex-col justify-between h-[300px] shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 no-underline text-white"
         style="background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.85) 100%), url('https://images.unsplash.com/photo-1496181130204-7552cc14542d?q=80&w=600&auto=format&fit=crop') center/cover no-repeat; border: 1px solid rgba(255, 255, 255, 0.1);">
        <div>
          <span class="inline-block px-3 py-1 bg-red-600 text-white text-[10px] font-black uppercase rounded-lg tracking-wider mb-2 shadow-sm">49% DTO</span>
          <h3 class="text-base font-black text-white leading-tight font-['Manrope'] drop-shadow-md">COMPUTADORES</h3>
          <p class="text-[11px] text-white/90 font-semibold mt-1 leading-snug drop-shadow-sm">Productividad al máximo nivel</p>
        </div>
        <div class="flex items-end justify-between mt-auto">
          <span class="text-xs font-black text-white uppercase tracking-wider group-hover:underline drop-shadow-sm">Computadores</span>
          <div class="w-11 h-11 rounded-full border-2 border-white/90 bg-[#ff3b30] flex items-center justify-center text-white shadow-md transition-all duration-300 group-hover:scale-110 group-hover:bg-[#e02d24]">
            <span class="material-symbols-outlined text-[20px]">laptop_mac</span>
          </div>
        </div>
      </a>

      <!-- Tarjeta 4: Televisores -->
      <a href="{{ route('consumer.catalog', ['category' => 'Televisores']) }}" class="group relative rounded-[28px] overflow-hidden p-5 flex flex-col justify-between h-[300px] shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 no-underline text-white"
         style="background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.85) 100%), url('https://images.unsplash.com/photo-1593305841991-05c297ba4575?q=80&w=600&auto=format&fit=crop') center/cover no-repeat; border: 1px solid rgba(255, 255, 255, 0.1);">
        <div>
          <span class="inline-block px-3 py-1 bg-red-600 text-white text-[10px] font-black uppercase rounded-lg tracking-wider mb-2 shadow-sm">51% DTO</span>
          <h3 class="text-base font-black text-white leading-tight font-['Manrope'] drop-shadow-md">TV LG 75"</h3>
          <p class="text-[11px] text-white/90 font-semibold mt-1 leading-snug drop-shadow-sm">Cine y series en alta definición</p>
        </div>
        <div class="flex items-end justify-between mt-auto">
          <span class="text-xs font-black text-white uppercase tracking-wider group-hover:underline drop-shadow-sm">TV</span>
          <div class="w-11 h-11 rounded-full border-2 border-white/90 bg-[#ff3b30] flex items-center justify-center text-white shadow-md transition-all duration-300 group-hover:scale-110 group-hover:bg-[#e02d24]">
            <span class="material-symbols-outlined text-[20px]">tv</span>
          </div>
        </div>
      </a>

      <!-- Tarjeta 5: Electrohogar -->
      <a href="{{ route('consumer.catalog', ['category' => 'Electrohogar']) }}" class="group relative rounded-[28px] overflow-hidden p-5 flex flex-col justify-between h-[300px] shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 no-underline text-white"
         style="background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.85) 100%), url('https://images.unsplash.com/photo-1556911220-e15b29be8c8f?q=80&w=600&auto=format&fit=crop') center/cover no-repeat; border: 1px solid rgba(255, 255, 255, 0.1);">
        <div>
          <span class="inline-block px-3 py-1 bg-red-600 text-white text-[10px] font-black uppercase rounded-lg tracking-wider mb-2 shadow-sm">15% DTO</span>
          <h3 class="text-base font-black text-white leading-tight font-['Manrope'] drop-shadow-md">ELECTRO</h3>
          <p class="text-[11px] text-white/90 font-semibold mt-1 leading-snug drop-shadow-sm">Equipa tu cocina con lo mejor</p>
        </div>
        <div class="flex items-end justify-between mt-auto">
          <span class="text-xs font-black text-white uppercase tracking-wider group-hover:underline drop-shadow-sm">Electro</span>
          <div class="w-11 h-11 rounded-full border-2 border-white/90 bg-[#ff3b30] flex items-center justify-center text-white shadow-md transition-all duration-300 group-hover:scale-110 group-hover:bg-[#e02d24]">
            <span class="material-symbols-outlined text-[20px]">kitchen</span>
          </div>
        </div>
      </a>

      <!-- Tarjeta 6: Hogar -->
      <a href="{{ route('consumer.catalog', ['category' => 'Hogar']) }}" class="group relative rounded-[28px] overflow-hidden p-5 flex flex-col justify-between h-[300px] shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 no-underline text-white"
         style="background: linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.85) 100%), url('https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=600&auto=format&fit=crop') center/cover no-repeat; border: 1px solid rgba(255, 255, 255, 0.1);">
        <div>
          <span class="inline-block px-3 py-1 bg-red-600 text-white text-[10px] font-black uppercase rounded-lg tracking-wider mb-2 shadow-sm">55% DTO</span>
          <h3 class="text-base font-black text-white leading-tight font-['Manrope'] drop-shadow-md">EXPOHOGAR</h3>
          <p class="text-[11px] text-white/90 font-semibold mt-1 leading-snug drop-shadow-sm">Renueva tu sala y alcobas</p>
        </div>
        <div class="flex items-end justify-between mt-auto">
          <span class="text-xs font-black text-white uppercase tracking-wider group-hover:underline drop-shadow-sm">Expohogar</span>
          <div class="w-11 h-11 rounded-full border-2 border-white/90 bg-[#ff3b30] flex items-center justify-center text-white shadow-md transition-all duration-300 group-hover:scale-110 group-hover:bg-[#e02d24]">
            <span class="material-symbols-outlined text-[20px]">home</span>
          </div>
        </div>
      </a>
    </div>
  </div>
</section>

{{-- ── 5. Banners en la Mitad del Dashboard (Publicidad Adicional) ── --}}
@if(isset($midBanners) && $midBanners->isNotEmpty())
  <div class="max-w-7xl mx-auto px-6 py-4">
    <div class="text-center mb-6">
      <span class="inline-block px-3 py-1 bg-primary/10 text-primary text-xs font-extrabold uppercase rounded-full tracking-wider animate-pulse-soft">Comercios Destacados</span>
    </div>
    @foreach($midBanners as $mb)
      @php
        $url = asset('storage/' . $mb->image_path);
        $isVideo = in_array(pathinfo($mb->image_path, PATHINFO_EXTENSION), ['mp4', 'webm', 'mov']);
      @endphp
      <div class="rounded-3xl overflow-hidden shadow-md border border-outline-variant/20 hover:scale-[1.01] transition-transform duration-300 mb-6 bg-white">
        @if($mb->link_url)
          <a href="{{ $mb->link_url }}" target="_blank" class="block w-full">
            @if($isVideo)
              <video src="{{ $url }}" class="w-full h-auto block" autoplay loop muted playsinline></video>
            @else
              <img src="{{ $url }}" class="w-full h-auto block" alt="{{ $mb->title ?? 'Banner Publicitario' }}" loading="lazy">
            @endif
          </a>
        @else
          @if($isVideo)
            <video src="{{ $url }}" class="w-full h-auto block" autoplay loop muted playsinline></video>
          @else
            <img src="{{ $url }}" class="w-full h-auto block" alt="{{ $mb->title ?? 'Banner Publicitario' }}" loading="lazy">
          @endif
        @endif
      </div>
    @endforeach
  </div>
@endif

{{-- ── 6. Productos Destacados ── --}}
<section class="px-6 py-10 bg-[#fcf9f8]">
  <div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-7">
      <h2 class="text-2xl font-['Manrope'] font-extrabold text-[#1b1c1c]">Productos Destacados</h2>
      <a href="{{ route('consumer.catalog') }}" class="font-semibold hover:underline flex items-center gap-1 text-sm text-primary no-underline">
        Ver todos <span class="material-symbols-outlined text-sm">arrow_forward</span>
      </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
      @foreach($featured as $p)
      <x-product-card :product="$p" />
      @endforeach
    </div>
  </div>
</section>

@endsection
