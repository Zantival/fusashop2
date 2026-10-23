@extends('layouts.app')
@section('title','Catálogo')
@section('content')
<div class="w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 pt-3 pb-8 min-w-0">

  <!-- Breadcrumb hacia Inicio -->
  <nav class="flex items-center gap-1.5 text-[11px] text-slate-500 mb-3 overflow-x-auto whitespace-nowrap scrollbar-none pb-0.5">
    <a href="{{ route('consumer.home') }}" class="flex items-center gap-1 font-bold text-slate-700 hover:text-[#10B981] transition-colors shrink-0">
      <span class="material-symbols-outlined text-xs">home</span>
      <span>Inicio</span>
    </a>
    <span>/</span>
    <span class="text-slate-400">Catálogo</span>
    @if(request('category'))
      <span>/</span>
      <span class="text-[#10B981] font-bold">{{ request('category') }}</span>
    @endif
  </nav>

  <div class="w-full min-w-0 flex flex-col md:flex-row gap-4 md:gap-6" x-data="{ mobileFiltersOpen: false }">
    <aside class="w-full md:w-56 shrink-0">
      <!-- Botón para alternar filtros en móvil -->
      <button type="button" @click="mobileFiltersOpen = !mobileFiltersOpen"
              class="md:hidden w-full flex items-center justify-between p-3.5 bg-white rounded-2xl shadow-sm border border-slate-200/80 font-bold text-xs text-slate-800 mb-2 cursor-pointer transition-all">
        <span class="flex items-center gap-2">
          <span class="material-symbols-outlined text-[#006c47] text-lg">tune</span>
          <span>Filtros y Búsqueda</span>
          @if(request()->anyFilled(['search','category','min_price','max_price','on_sale','allows_points']))
            <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
          @endif
        </span>
        <span class="material-symbols-outlined text-slate-400 text-lg transition-transform duration-200" :class="mobileFiltersOpen ? 'rotate-180' : ''">expand_more</span>
      </button>

      <div :class="mobileFiltersOpen ? 'block' : 'hidden md:block'" class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm md:sticky md:top-20">
        <h2 class="font-['Manrope'] font-bold text-[#1b1c1c] mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined" style="color:#006c47">tune</span> Filtros
        </h2>
        <form method="GET" action="{{ route('consumer.catalog') }}" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-[#3c4a41] uppercase mb-1.5">Buscar</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#3c4a41] text-sm">search</span>
              <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-9 pr-3 py-2 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 text-sm" style="--tw-ring-color:#006c47" placeholder="Buscar..."/>
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-[#3c4a41] uppercase mb-1.5">Categoría</label>
            <select name="category" class="w-full py-2 px-3 bg-[#f0eded] rounded-xl border-0 outline-none text-sm">
              <option value="">Todas</option>
              @foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
            </select>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div><label class="text-xs font-semibold text-[#3c4a41] block mb-1">Mín $</label><input type="number" name="min_price" value="{{ request('min_price') }}" min="0" class="w-full py-2 px-2 bg-[#f0eded] rounded-xl border-0 outline-none text-sm" placeholder="0"/></div>
            <div><label class="text-xs font-semibold text-[#3c4a41] block mb-1">Máx $</label><input type="number" name="max_price" value="{{ request('max_price') }}" min="0" class="w-full py-2 px-2 bg-[#f0eded] rounded-xl border-0 outline-none text-sm" placeholder="∞"/></div>
          </div>
          <div class="space-y-2 pt-1 border-t border-slate-100">
            <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
              <input type="checkbox" name="on_sale" value="1" {{ request()->boolean('on_sale') ? 'checked' : '' }} class="rounded text-[#ba1a1a] focus:ring-[#ba1a1a]">
              <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-[#ba1a1a]">local_offer</span> En Descuento (% OFF)</span>
            </label>
            <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
              <input type="checkbox" name="allows_points" value="1" {{ request()->boolean('allows_points') ? 'checked' : '' }} class="rounded text-amber-500 focus:ring-amber-500">
              <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm text-amber-500">toll</span> Aplica FusaCoins</span>
            </label>
          </div>
          <button type="submit" class="w-full py-2 text-white font-semibold rounded-xl hover:opacity-90 text-sm cursor-pointer" style="background:linear-gradient(135deg,#006c47,#00b67a)">Filtrar</button>
          @if(request()->anyFilled(['search','category','min_price','max_price','on_sale','allows_points']))
            <a href="{{ route('consumer.catalog') }}" class="block text-center text-xs text-[#3c4a41] hover:underline">✕ Limpiar</a>
          @endif
        </form>
      </div>
    </aside>

    <div class="w-full min-w-0 flex-1" id="catalog-products-container">
      @include('consumer.partials.products-grid', ['products' => $products])
    </div>
  </div>

  @if(isset($directory) && count($directory) > 0)
    <div class="mt-8 sm:mt-12 pt-5 sm:pt-6 border-t border-slate-200">
      <div class="flex items-center gap-2 mb-3 sm:mb-4">
        <span class="material-symbols-outlined text-[#10B981]">verified</span>
        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Tiendas y Vendedores Oficiales</h3>
      </div>
      <div class="flex gap-2.5 sm:gap-3 overflow-x-auto pb-2 scrollbar-none">
        @foreach($directory as $brand)
          <a href="{{ route('consumer.merchant.profile', $brand->merchant_id) }}" class="flex items-center gap-2 sm:gap-2.5 p-2 pr-3.5 sm:pr-4 bg-white rounded-xl border border-slate-100 shadow-xs hover:border-emerald-300 hover:shadow-md transition-all group shrink-0">
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
              @if($brand->logo_path)
                <img loading="lazy" decoding="async" src="{{ Storage::url($brand->logo_path) }}" alt="{{ $brand->company_name }}" class="w-full h-full object-cover">
              @else
                <span class="material-symbols-outlined text-slate-400 text-base sm:text-lg group-hover:text-[#10B981] transition-colors">store</span>
              @endif
            </div>
            <span class="text-xs font-bold text-slate-700 group-hover:text-[#10B981] transition-colors">{{ $brand->company_name }}</span>
          </a>
        @endforeach
      </div>
    </div>
  @endif
</div>
@endsection
