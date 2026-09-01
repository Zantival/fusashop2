@extends('layouts.app')
@section('title','Dashboard Comerciante')
@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
  <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
      <h1 class="text-4xl font-['Manrope'] font-black text-[#1b1c1c] tracking-tight">Mi Negocio</h1>
      <p class="text-[#3c4a41] text-lg font-medium">Hola, {{ auth()->user()->name }} 👋 ¡Qué bueno verte!</p>
    </div>
    <div class="flex flex-wrap gap-3">
      <a href="{{ route('consumer.merchant.profile', auth()->id()) }}" target="_blank" class="px-5 py-3 bg-primary/5 border-2 border-primary/20 text-primary font-bold rounded-2xl hover:bg-primary/10 transition-all flex items-center gap-2 text-sm shadow-sm">
        <span class="material-symbols-outlined text-[20px]">visibility</span> Ver Mi Tienda
      </a>
      <a href="{{ route('merchant.products') }}" class="px-5 py-3 bg-white border-2 border-surface-container-highest text-[#1b1c1c] font-bold rounded-2xl hover:border-primary hover:text-primary transition-all flex items-center gap-2 text-sm shadow-sm">
        <span class="material-symbols-outlined text-[20px]">inventory_2</span> Mis Productos
      </a>
      <a href="{{ route('merchant.inventory') }}" class="px-5 py-3 bg-white border-2 border-surface-container-highest text-[#1b1c1c] font-bold rounded-2xl hover:border-primary hover:text-primary transition-all flex items-center gap-2 text-sm shadow-sm">
        <span class="material-symbols-outlined text-[20px]">warehouse</span> Stock Rápido
      </a>
      <a href="{{ route('merchant.orders') }}" class="px-5 py-3 bg-white border-2 border-surface-container-highest text-[#1b1c1c] font-bold rounded-2xl hover:border-primary hover:text-primary transition-all flex items-center gap-2 text-sm shadow-sm">
        <span class="material-symbols-outlined text-[20px]">receipt</span> Ver Pedidos
      </a>
      <a href="{{ route('merchant.products.create') }}" class="px-6 py-3 text-white font-bold rounded-2xl hover:opacity-95 active:scale-95 transition-all flex items-center gap-2 shadow-lg shadow-primary/20" style="background:linear-gradient(135deg,#006c47,#00b67a)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span> Nuevo Producto
      </a>
    </div>
  </div>

  @if($outOfStock > 0)
    <div class="bg-error-container text-error p-5 rounded-2xl mb-8 flex items-center gap-4 shadow-sm border border-error/20">
      <span class="material-symbols-outlined text-4xl">warning</span>
      <div>
        <h3 class="font-bold text-lg">¡Inventario Crítico!</h3>
        <p class="text-sm mt-1">Tienes <strong>{{ $outOfStock }}</strong> producto(s) totalmente agotado(s). Los clientes no pueden comprarlos.</p>
      </div>
      <a href="{{ route('merchant.products') }}" class="ml-auto px-4 py-2 bg-error text-white font-bold rounded-xl text-sm hover:opacity-90 transition-all shadow-md">Ver inventario</a>
    </div>
  @endif

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    @foreach([['inventory_2','Productos',$totalProducts,'#006c47','rgba(0,108,71,.1)'],['payments','Ventas','$'.number_format($totalSales??0,0,',','.'),'#7c5800','rgba(254,183,0,.15)'],['pending_actions','Pendientes',$pendingOrders,'#1d4ed8','rgba(29,78,216,.1)'],['warning','Sin stock',$outOfStock,'#dc2626','rgba(220,38,38,.1)']] as [$icon,$label,$value,$color,$bg])
    <div class="bg-white rounded-2xl p-5 shadow-sm">
      <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background:{{ $bg }}">
        <span class="material-symbols-outlined" style="color:{{ $color }}">{{ $icon }}</span>
      </div>
      <p class="text-3xl font-['Manrope'] font-extrabold text-[#1b1c1c]">{{ $value }}</p>
      <p class="text-[#3c4a41] text-sm mt-1">{{ $label }}</p>
    </div>
    @endforeach
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 flex items-center justify-between border-b border-[#f6f3f2]">
        <h2 class="font-['Manrope'] font-bold text-[#1b1c1c]">Pedidos Recientes</h2>
        <a href="{{ route('merchant.orders') }}" class="text-sm font-semibold hover:underline" style="color:#006c47">Ver todos</a>
      </div>
      @if($recentOrders->isEmpty())
        <div class="text-center py-10 text-[#3c4a41] text-sm">Sin pedidos aún.</div>
      @else
      <table class="w-full">
        <thead class="bg-[#f6f3f2]"><tr>
          @foreach(['#','Cliente','Total','Estado','Fecha'] as $h)
          <th class="px-4 py-3 text-left text-xs font-semibold text-[#3c4a41] uppercase">{{ $h }}</th>
          @endforeach
        </tr></thead>
        <tbody>
          @foreach($recentOrders as $o)
          @php $sc=['pending'=>['#fef3c7','#92400e'],'processing'=>['#d1fae5','#065f46'],'shipped'=>['#dbeafe','#1e40af'],'delivered'=>['#dcfce7','#166534'],'cancelled'=>['#fee2e2','#991b1b']]; $c=$sc[$o->status]??['#f3f4f6','#374151']; @endphp
          <tr class="border-t border-[#f6f3f2] hover:bg-[#fcf9f8] transition-colors">
            <td class="px-4 py-3 text-xs text-[#3c4a41] font-mono">#{{ $o->id }}</td>
            <td class="px-4 py-3 text-sm font-semibold text-[#1b1c1c]">{{ e($o->user->name) }}</td>
            <td class="px-4 py-3 text-sm font-bold" style="color:#006c47">${{ number_format($o->total,0,',','.') }}</td>
            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold" style="background:{{ $c[0] }};color:{{ $c[1] }}">{{ ucfirst($o->status) }}</span></td>
            <td class="px-4 py-3 text-xs text-[#3c4a41]">{{ $o->created_at->format('d/m/Y') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-[#f6f3f2] flex items-center justify-between">
        <h2 class="font-['Manrope'] font-bold text-[#1b1c1c]">Mis Productos</h2>
        <a href="{{ route('merchant.products') }}" class="text-sm font-semibold hover:underline" style="color:#006c47">Ver todos</a>
      </div>
      @php $myProducts = auth()->user()->products()->latest()->take(5)->get(); @endphp
      <div class="p-4 space-y-3">
        @forelse($myProducts as $p)
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0">
            <x-product-image :product="$p" class="w-full h-full"/>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-[#1b1c1c] text-sm truncate">{{ e($p->name) }}</p>
            <p class="text-xs text-[#3c4a41]">Stock: <strong class="{{ $p->stock==0?'text-red-600':($p->stock<=5?'text-amber-600':'') }}">{{ $p->stock }}</strong></p>
          </div>
          <span class="font-bold text-sm shrink-0" style="color:#006c47">${{ number_format($p->price,0,',','.') }}</span>
        </div>
        @empty
        <p class="text-center text-[#3c4a41] py-4 text-sm">Sin productos aún.</p>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Recent Reviews --}}
  <div class="mt-6 bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-[#f6f3f2] flex items-center justify-between">
      <h2 class="font-['Manrope'] font-bold text-[#1b1c1c] flex items-center gap-2">
        <span class="material-symbols-outlined" style="color:#feb700">star</span> Últimas Reseñas
        <span class="ml-2 text-sm text-[#3c4a41] font-normal">(Promedio: <b>{{ number_format($avgRating,1) }}★</b> · {{ $totalReviews }} reseñas)</span>
      </h2>
      <a href="{{ route('merchant.reviews') }}" class="text-sm font-semibold hover:underline" style="color:#006c47">Ver todas</a>
    </div>
    @if($recentReviews->isEmpty())
      <div class="text-center py-8 text-[#3c4a41] text-sm">Aún no tienes reseñas en tus productos.</div>
    @else
    <div class="divide-y divide-[#f6f3f2]">
      @foreach($recentReviews as $rev)
      <div class="px-6 py-4 flex gap-3">
        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-xs shrink-0" style="background:linear-gradient(135deg,#006c47,#00b67a)">{{ strtoupper(substr($rev->user->name,0,1)) }}</div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold text-sm text-[#1b1c1c]">{{ e($rev->user->name) }}</span>
            <span class="text-xs text-[#3c4a41]">en <em>{{ e($rev->product->name) }}</em></span>
          </div>
          @if($rev->comment)<p class="text-xs text-[#3c4a41] mt-1">{{ e(\Illuminate\Support\Str::limit($rev->comment, 120)) }}</p>@endif
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>

  {{-- GESTIÓN DE MARCA Y PUBLICIDAD --}}
  <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 pb-12">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-outline-variant/30 flex items-center gap-6 group hover:border-primary/30 transition-all">
      <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
        <span class="material-symbols-outlined text-primary text-3xl">storefront</span>
      </div>
      <div class="flex-1">
        <h3 class="font-['Manrope'] font-bold text-[#1b1c1c] text-lg">Personalizar Mi Tienda</h3>
        <p class="text-sm text-[#3c4a41] mb-3">Actualiza tu logo, banners publicitarios propios y descripción de marca.</p>
        <a href="{{ route('merchant.store.edit') }}" class="inline-flex items-center gap-2 text-primary font-bold text-sm hover:underline">
          Configurar ahora <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
      </div>
    </div>

    <div class="bg-primary-gradient rounded-3xl p-6 text-white shadow-lg overflow-hidden relative group">
      <span class="material-symbols-outlined absolute -bottom-4 -right-4 text-8xl opacity-20 group-hover:scale-110 transition-transform">campaign</span>
      <h3 class="font-['Manrope'] font-bold text-lg mb-1">Banner Principal</h3>
      <p class="text-white/80 text-sm mb-4">Aparece en la página principal de FusaShop para miles de usuarios.</p>
      <a href="{{ route('merchant.banner.request') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-primary font-bold rounded-xl hover:bg-opacity-95 transition-all shadow-md text-sm">
        <span class="material-symbols-outlined text-sm">add_photo_alternate</span> Solicitar espacio
      </a>
    </div>

    <div class="bg-indigo-600 rounded-3xl p-6 text-white shadow-lg overflow-hidden relative group">
      <span class="material-symbols-outlined absolute -bottom-4 -right-4 text-8xl opacity-20 group-hover:scale-110 transition-transform">support_agent</span>
      <h3 class="font-['Manrope'] font-bold text-lg mb-1">Soporte Técnico</h3>
      <p class="text-white/80 text-sm mb-4">¿Tienes dudas o problemas? Comunícate directamente con un administrador.</p>
      <a href="{{ route('merchant.support.contact') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-indigo-600 font-bold rounded-xl hover:bg-opacity-95 transition-all shadow-md text-sm">
        <span class="material-symbols-outlined text-sm">chat</span> Hablar con Admin
      </a>
    </div>
  </div>
</div>
@endsection
