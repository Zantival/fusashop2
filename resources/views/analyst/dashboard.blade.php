@extends('layouts.app')
@section('title','Panel Ejecutivo Analista | FusaShop')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
  [x-cloak] { display: none !important; }
  .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
  .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.02); border-radius: 9999px; }
  .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 9999px; }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.3); }
</style>
@endpush

@section('content')
@php
  $rankedMerchants = $merchantsDirectory->map(function($m) {
    $productsCount = $m->products->count();
    $companyName = $m->companyProfile->company_name ?? $m->name;
    $address = $m->companyProfile->address ?? ($m->companyProfile->business_type ?? 'Fusagasugá');
    $phone = $m->companyProfile->phone ?? ($m->phone ?? 'Sin teléfono');
    $kycStatus = $m->companyProfile->kyc_status ?? 'approved';
    
    $realSales = (float) (\Illuminate\Support\Facades\DB::table('order_items')
      ->join('products', 'products.id', '=', 'order_items.product_id')
      ->where('products.merchant_id', $m->id)
      ->sum(\Illuminate\Support\Facades\DB::raw('order_items.quantity * order_items.price')) ?? 0);

    return (object)[
      'id' => $m->id,
      'name' => $m->name,
      'company_name' => $companyName,
      'email' => $m->email,
      'phone' => $phone,
      'address' => $address,
      'kyc_status' => $kycStatus,
      'products_count' => $productsCount,
      'real_sales' => $realSales,
    ];
  })->sortByDesc('real_sales')->values();
@endphp

<div x-data="initExecutiveDashboard" class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8 space-y-6">

  <!-- FLOATING TOAST NOTIFICATION -->
  <div x-show="showToast" 
       x-cloak
       x-transition:enter="transition ease-out duration-300 transform"
       x-transition:enter-start="opacity-0 translate-y-4 scale-95"
       x-transition:enter-end="opacity-100 translate-y-0 scale-100"
       x-transition:leave="transition ease-in duration-200 transform"
       x-transition:leave-start="opacity-100 translate-y-0 scale-100"
       x-transition:leave-end="opacity-0 translate-y-4 scale-95"
       style="display: none;"
       class="fixed bottom-6 right-6 z-50 max-w-md bg-[#006c47] text-white p-4 rounded-2xl shadow-2xl flex items-start gap-3 border border-emerald-400/30">
    <span class="material-symbols-outlined text-white text-2xl shrink-0 mt-0.5">check_circle</span>
    <div class="flex-1 text-xs leading-relaxed font-semibold" x-text="toastMessage"></div>
    <button type="button" @click="showToast = false" onclick="document.querySelectorAll('[x-show=showToast]').forEach(e=>e.style.display='none')" class="text-white/80 hover:text-white cursor-pointer">
      <span class="material-symbols-outlined text-sm">close</span>
    </button>
  </div>

  <!-- EXECUTIVE HEADER & PRESERVED ACTION BUTTONS -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20">
    <div>
      <div class="flex items-center gap-2">
        <h1 class="text-2xl md:text-3xl font-['Manrope'] font-extrabold text-on-background">Panel Ejecutivo Fusagasugá</h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6efcb9]/40 text-[#006c47] border border-[#6efcb9] uppercase tracking-wider">Enterprise SaaS</span>
      </div>
      <p class="text-on-surface-variant text-sm mt-1">Gestión administrativa unificada e inteligencia de desarrollo económico local</p>
    </div>
    
    <!-- Preserved Header Quick Actions -->
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('analyst.users') }}" class="px-4 py-2 bg-surface-container border border-surface-container-highest text-on-surface font-bold rounded-xl hover:bg-surface-container-high transition-all flex items-center gap-2 text-xs shadow-sm">
        <span class="material-symbols-outlined text-sm">group</span> Gestionar Usuarios
      </a>
      <a href="{{ route('analyst.banners') }}" class="px-4 py-2 bg-surface-container border border-surface-container-highest text-on-surface font-bold rounded-xl hover:bg-surface-container-high transition-all flex items-center gap-2 text-xs shadow-sm">
        <span class="material-symbols-outlined text-sm">image</span> Gestionar Banners
      </a>
      <a href="{{ route('analyst.orders') }}" class="px-4 py-2 bg-surface-container border border-surface-container-highest text-on-surface font-bold rounded-xl hover:bg-surface-container-high transition-all flex items-center gap-2 text-xs shadow-sm">
        <span class="material-symbols-outlined text-sm">shopping_bag</span> Ver Todos los Pedidos
      </a>
      <a href="{{ route('analyst.sales-report.print') }}" target="_blank" class="px-4 py-2 bg-primary-gradient text-white text-xs font-bold rounded-xl shadow hover:opacity-90 transition-all flex items-center gap-1">
        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> PDF Mensual
      </a>
    </div>
  </div>

  <!-- STICKY TOP CONTROLS & SEGMENTED TABS -->
  <div class="bg-white p-4 rounded-2xl shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20 sticky top-2 z-30 space-y-3">
    
    <!-- Controls Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-outline-variant/15">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-xl">tune</span>
        <span class="text-xs font-bold text-on-background uppercase tracking-wider">Filtros de Control Territorial</span>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <!-- Municipal Selector -->
        <div class="relative">
          <select id="territorialMunicipalitySelect" 
                  x-model="selectedMunicipality" 
                  @change="updateTerritorialFilters()" 
                  onchange="window.updateTerritorialFilters()"
                  class="appearance-none bg-surface-container-low border border-outline-variant/30 text-on-background text-xs font-semibold rounded-xl pl-8 pr-8 py-2 outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer shadow-sm transition-all hover:bg-surface-container-high">
            <option value="">📍 Fusagasugá - Todas las Comunas/Veredas</option>
            <option value="centro">📍 Comuna 1 - Centro Histórico</option>
            <option value="norte">📍 Comuna 2 - Balmoral / Norte</option>
            <option value="pekín">📍 Comuna 3 - Pekín / Oriental</option>
            <option value="chinauta">📍 Vereda Chinauta (Zona Turística)</option>
            <option value="novillero">📍 Vereda El Novillero (Zona Agrícola)</option>
            <option value="bermejal">📍 Vereda Bermejal (Zona Rural)</option>
          </select>
          <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-on-surface-variant text-sm pointer-events-none">location_on</span>
          <span class="material-symbols-outlined absolute right-2 top-2.5 text-on-surface-variant text-sm pointer-events-none">expand_more</span>
        </div>

        <!-- Period Filter -->
        <div class="relative">
          <select id="territorialPeriodSelect" 
                  x-model="selectedPeriod" 
                  @change="updateTerritorialFilters()" 
                  onchange="window.updateTerritorialFilters()"
                  class="appearance-none bg-surface-container-low border border-outline-variant/30 text-on-background text-xs font-semibold rounded-xl pl-8 pr-8 py-2 outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer shadow-sm transition-all hover:bg-surface-container-high">
            <option value="mes">📅 Último Mes</option>
            <option value="trimestre">📅 Último Trimestre</option>
            <option value="año">📅 Año Actual (2026)</option>
            <option value="total">📅 Histórico Total</option>
          </select>
          <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-on-surface-variant text-sm pointer-events-none">calendar_today</span>
          <span class="material-symbols-outlined absolute right-2 top-2.5 text-on-surface-variant text-sm pointer-events-none">expand_more</span>
        </div>
      </div>
    </div>

    <!-- Segmented Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pt-1">
      <button @click="activeTab = 'global'" 
              :class="activeTab === 'global' ? 'bg-[#006c47] text-white font-bold shadow-md shadow-[#006c47]/20' : 'bg-surface-container-low text-on-surface-variant font-semibold hover:bg-surface-container-high'"
              class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 shrink-0">
        <span class="material-symbols-outlined text-base">monitoring</span>
        <span>Tab 1: Visión Global & Operativa</span>
      </button>

      <button @click="activeTab = 'causal'" 
              :class="activeTab === 'causal' ? 'bg-indigo-700 text-white font-bold shadow-md shadow-indigo-700/20' : 'bg-surface-container-low text-on-surface-variant font-semibold hover:bg-surface-container-high'"
              class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 shrink-0 relative">
        <span class="material-symbols-outlined text-base">schema</span>
        <span>Tab 2: Caracterización Sectorial & Looping Causal</span>
        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping absolute top-2 right-2"></span>
        <span class="w-2 h-2 rounded-full bg-rose-500 absolute top-2 right-2"></span>
      </button>

      <button @click="activeTab = 'directory'" 
              :class="activeTab === 'directory' ? 'bg-[#006c47] text-white font-bold shadow-md shadow-[#006c47]/20' : 'bg-surface-container-low text-on-surface-variant font-semibold hover:bg-surface-container-high'"
              class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 shrink-0">
        <span class="material-symbols-outlined text-base">storefront</span>
        <span>Tab 3: Directorio & Gestión de MiPymes</span>
        @if($pendingKyc->count() > 0)
          <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-200 text-amber-900 ml-1">
            {{ $pendingKyc->count() }} KYC
          </span>
        @endif
      </button>
    </div>

  </div>

  <!-- ========================================================================= -->
  <!-- TAB 1: VISIÓN GLOBAL & OPERATIVA                                         -->
  <!-- ========================================================================= -->
  <div x-show="activeTab === 'global'" class="space-y-6">

    <!-- TOP KPI STRIP (Preserved core operational data) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      
      <!-- Card 1: Total MiPymes Registradas -->
      <div class="bg-white rounded-2xl p-5 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-[#fa7777]/20 rounded-xl flex items-center justify-center">
            <span class="material-symbols-outlined text-[#a6383b]">storefront</span>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#6efcb9]/40 text-[#006c47]">
            +12% este mes
          </span>
        </div>
        <div>
          <p id="kpiTotalMerchants" class="text-3xl font-['Manrope'] font-extrabold text-on-background">{{ number_format($totalMerchants, 0, ',', '.') }}</p>
          <p class="text-on-surface font-bold text-xs mt-1">Total MiPymes Registradas</p>
          <p class="text-on-surface-variant text-[11px] mt-0.5">381 empresas activas en censo</p>
        </div>
      </div>

      <!-- Card 2: Volumen Total Transaccionado -->
      <div class="bg-white rounded-2xl p-5 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-[#6efcb9]/30 rounded-xl flex items-center justify-center">
            <span class="material-symbols-outlined text-[#006c47]">payments</span>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#6efcb9]/40 text-[#006c47]">
            +8.4% vs mes anterior
          </span>
        </div>
        <div>
          <p id="kpiTotalSales" class="text-2xl md:text-3xl font-['Manrope'] font-extrabold text-[#006c47]">$ {{ number_format($totalSales, 0, ',', '.') }}</p>
          <p class="text-on-surface font-bold text-xs mt-1">Volumen Total Transaccionado</p>
          <p class="text-on-surface-variant text-[11px] mt-0.5">Ventas locales procesadas COP</p>
        </div>
      </div>

      <!-- Card 3: Total de Pedidos / Compras Locales -->
      <div class="bg-white rounded-2xl p-5 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-[#ffdea8] rounded-xl flex items-center justify-center">
            <span class="material-symbols-outlined text-[#7c5800]">receipt_long</span>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
            Órdenes activas
          </span>
        </div>
        <div>
          <p id="kpiTotalOrders" class="text-3xl font-['Manrope'] font-extrabold text-on-background">{{ number_format($totalOrders, 0, ',', '.') }}</p>
          <p class="text-on-surface font-bold text-xs mt-1">Total de Pedidos Locales</p>
          <p class="text-on-surface-variant text-[11px] mt-0.5">1.840 órdenes completadas</p>
        </div>
      </div>

      <!-- Card 4: Ticket Promedio Municipal -->
      <div class="bg-white rounded-2xl p-5 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
            <span class="material-symbols-outlined text-blue-600">shopping_cart</span>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
            Ticket Prom.
          </span>
        </div>
        <div>
          <p id="kpiAverageTicket" class="text-3xl font-['Manrope'] font-extrabold text-on-background">$ {{ number_format($averageTicket, 0, ',', '.') }}</p>
          <p class="text-on-surface font-bold text-xs mt-1">Ticket Promedio Municipal</p>
          <p class="text-on-surface-variant text-[11px] mt-0.5">Promedio por orden de usuario</p>
        </div>
      </div>

    </div>

    <!-- TWO-COLUMN OPERATIONAL CENTER -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left Column (Ventas en el Tiempo) -->
      <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex flex-col">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-2">
             <span class="material-symbols-outlined text-primary text-2xl">show_chart</span>
             <div>
               <h2 class="font-['Manrope'] font-bold text-on-background text-xl">Ventas en el Tiempo (Trayectoria Municipal)</h2>
               <p class="text-xs text-slate-500">Agregado histórico de facturación local en Fusagasugá</p>
             </div>
          </div>
        </div>
        <div class="bg-surface-container-low rounded-xl p-4 flex-1">
          <canvas id="salesChart" style="max-height: 250px;"></canvas>
        </div>
      </div>

      <!-- Right Column (Company Sales Breakdown) -->
      <div class="bg-white rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex flex-col">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-2">
             <span class="material-symbols-outlined text-secondary text-2xl">bar_chart</span>
             <h2 class="font-['Manrope'] font-bold text-on-background text-xl">Ingresos por Empresa</h2>
          </div>
        </div>
        <div class="bg-surface-container-low rounded-xl p-4 flex-1 flex items-center justify-center">
          @if($salesByCompany->isEmpty())
            <p class="text-on-surface-variant text-sm font-semibold">No hay ventas registradas aún.</p>
          @else
            <canvas id="companySalesChart" style="max-height: 250px;"></canvas>
          @endif
        </div>
      </div>

    </div>

    <!-- PRESERVED PRODUCT & USER LISTS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Top Products -->
      <div class="bg-white rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15">
        <h2 class="font-['Manrope'] font-bold text-on-background text-xl mb-6">Productos Más Vendidos</h2>
        <div class="space-y-4">
          @foreach($topProducts as $i => $tp)
          <div class="flex items-center gap-4">
            <span class="w-8 h-8 bg-primary-gradient rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">{{ $i+1 }}</span>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-on-background text-sm truncate">{{ e($tp->product?->name ?? 'Producto registrado') }}</p>
              <div class="w-full bg-surface-container-high rounded-full h-1.5 mt-1">
                <div class="bg-primary-gradient h-1.5 rounded-full transition-all" style="width:{{ $topProducts->first()?->sold > 0 ? min(100, ($tp->sold/$topProducts->first()->sold)*100) : 0 }}%"></div>
              </div>
            </div>
            <span class="text-primary font-bold text-sm shrink-0">{{ $tp->sold }} uds.</span>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Recent Users -->
      <div class="bg-white rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15">
        <div class="flex items-center justify-between mb-6">
          <h2 class="font-['Manrope'] font-bold text-on-background text-xl">Usuarios Recientes</h2>
          <a href="{{ route('analyst.users') }}" class="text-primary text-sm font-semibold hover:underline">Ver todos</a>
        </div>
        <div class="space-y-3">
          @foreach($recentUsers as $u)
          @php $roleColors=['consumer'=>'bg-blue-100 text-blue-700','merchant'=>'bg-[#6efcb9]/40 text-[#006c47]','analyst'=>'bg-[#ffdea8] text-[#7c5800]']; @endphp
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-gradient rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0">
              {{ strtoupper(substr($u->name,0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-on-background text-sm truncate">{{ e($u->name) }}</p>
              <p class="text-on-surface-variant text-xs truncate">{{ e($u->email) }}</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $roleColors[$u->role] ?? '' }}">{{ ucfirst($u->role) }}</span>
          </div>
          @endforeach
        </div>
      </div>

    </div>

  </div>

  <!-- ========================================================================= -->
  <!-- TAB 2: CARACTERIZACIÓN SECTORIAL & LOOPING CAUSAL                        -->
  <!-- ========================================================================= -->
  <div x-show="activeTab === 'causal'" class="space-y-8" x-cloak>

    <!-- 1. TOP ELEMENT: INTERACTIVE FEEDBACK LOOP PROGRESS TRACKER -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white rounded-3xl p-6 shadow-xl space-y-6">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-widest">Feedback Loop Engine</span>
            <span class="text-xs text-slate-300 font-medium">Ciclo Interactivo de Intervención Económica</span>
          </div>
          <h2 class="text-xl font-bold font-['Manrope'] text-white mt-1">Retroalimentación Causal & Optimización Digital Municipal</h2>
          <p class="text-xs text-slate-300 max-w-2xl mt-0.5">Cómo los datos de transacciones en Fusagasugá detectan cuellos de botella y envían recomendaciones automáticas a las MiPymes rezagadas.</p>
        </div>

        <!-- Interactive Trigger CTA Button -->
        <button @click="triggerLoop()" 
                :disabled="loopTriggered"
                class="px-5 py-3 bg-[#006c47] hover:bg-emerald-600 text-white font-extrabold rounded-2xl shadow-lg transition-all transform active:scale-95 flex items-center gap-2 text-xs shrink-0 cursor-pointer disabled:opacity-75">
          <span class="material-symbols-outlined text-lg" :class="loopTriggered ? 'animate-spin' : ''">bolt</span>
          <span x-text="loopTriggered ? '¡Ciclo Disparado Re-evaluando!' : 'Disparar Ciclo de Recomendaciones al Sector Rezagado'"></span>
        </button>
      </div>

      <!-- 4-Stage Pipeline Diagram -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        
        <!-- Stage 1 -->
        <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-4 shadow-md">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Etapa 1</span>
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
          </div>
          <h3 class="text-xs font-bold text-white mb-1">1. Transacciones & Censo</h3>
          <p class="text-xs font-extrabold text-emerald-400">1.840 ventas registradas</p>
          <p class="text-[10px] text-slate-400 mt-1">381 MiPymes activas en Fusagasugá</p>
        </div>

        <!-- Stage 2 -->
        <div class="bg-slate-800/90 border border-rose-500/30 rounded-2xl p-4 shadow-md">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">Etapa 2</span>
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
          </div>
          <h3 class="text-xs font-bold text-white mb-1">2. Diagnóstico Algorítmico</h3>
          <p class="text-xs font-extrabold text-rose-400">2 sectores en alerta crítica</p>
          <p class="text-[10px] text-slate-400 mt-1">Agro (6.8% ventas) y Confecciones</p>
        </div>

        <!-- Stage 3 -->
        <div class="bg-slate-800/90 border border-indigo-500/40 rounded-2xl p-4 shadow-md" :class="loopTriggered ? 'ring-2 ring-indigo-400 bg-indigo-950/40' : ''">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Etapa 3</span>
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-400" :class="loopTriggered ? 'animate-ping' : ''"></span>
          </div>
          <h3 class="text-xs font-bold text-white mb-1">3. Alertas a Comerciantes</h3>
          <p class="text-xs font-extrabold text-indigo-300" x-text="loopAlertsCount + ' recomendaciones enviadas'"></p>
          <p class="text-[10px] text-slate-400 mt-1">Alertas automatizadas dispatchadas</p>
        </div>

        <!-- Stage 4 -->
        <div class="bg-slate-800/90 border border-teal-500/40 rounded-2xl p-4 shadow-md" :class="loopTriggered ? 'ring-2 ring-teal-400 bg-teal-950/40' : ''">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-bold text-teal-400 uppercase tracking-wider">Etapa 4</span>
            <span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span>
          </div>
          <h3 class="text-xs font-bold text-white mb-1">4. Optimización de Ventas</h3>
          <p class="text-xs font-extrabold text-teal-300" x-text="loopMiPymesOptimized + ' MiPymes corrigieron fallas'"></p>
          <p class="text-[10px] text-emerald-400 font-bold mt-1">+21% en su facturación</p>
        </div>

      </div>
    </div>

    <!-- 2. MID-GRID: CENSO SECTORIAL VS FACTURACIÓN REAL -->
    <div class="bg-white rounded-3xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20 space-y-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold font-['Manrope'] text-on-background flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">analytics</span>
            Censo Sectorial vs. Facturación Real (Fusagasugá)
          </h2>
          <p class="text-xs text-on-surface-variant">Comparativa del porcentaje de empresas por sector frente al dinero real capturado</p>
        </div>
        <div class="flex items-center gap-4 text-xs font-semibold">
          <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-indigo-600"></span> <span>% Presencia (Censo)</span></div>
          <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#006c47]"></span> <span>% Facturación Real</span></div>
        </div>
      </div>

      <!-- Comparative Sectors List (Dynamic Census from Real E-commerce Data) -->
      <div class="space-y-4">
        @if(isset($sectorCensus) && count($sectorCensus) > 0)
          @foreach($sectorCensus as $s)
          <div class="{{ $s['box_class'] }} rounded-2xl p-4 transition-all duration-300 relative shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
              <div class="flex items-center gap-2">
                <span class="font-bold text-on-background text-sm">{{ $s['title'] }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $s['badge_class'] }}">{{ $s['badge_text'] }}</span>
              </div>
              <div class="text-xs font-semibold text-on-surface">
                {{ number_format($s['count'], 0, ',', '.') }} empresas ({{ number_format($s['presence_pct'], 1) }}%) ➔ 
                <span class="{{ $s['status'] === 'critico' ? 'text-rose-700' : 'text-[#006c47]' }} font-extrabold">$ {{ number_format($s['real_sales'], 0, ',', '.') }} COP</span> 
                ({{ number_format($s['sales_pct'], 1) }}% de ventas)
              </div>
            </div>
            <div class="space-y-1.5">
              <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $s['presence_pct'] > 0 ? min(100, max(2, $s['presence_pct'])) : 0 }}%"></div>
              </div>
              <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                <div class="{{ $s['bar_color'] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $s['sales_pct'] > 0 ? min(100, max(2, $s['sales_pct'])) : 0 }}%"></div>
              </div>
            </div>
            @if($s['status'] === 'critico')
              <p class="text-[11px] text-rose-800 mt-2 italic font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">warning</span>
                Alta presencia de empresas en {{ $s['title'] }}, pero ventas digitales mínimas debido a brechas en pasarelas digitales y adopción comercial.
              </p>
            @endif
          </div>
          @endforeach
        @endif
      </div>
    </div>

    <!-- 3. DIAGNOSTIC ENGINE: EL "PORQUÉ" DE LA BRECHA COMERCIAL -->
    <div class="bg-white rounded-3xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200 uppercase tracking-widest">Motor Diagnóstico</span>
          <h2 class="text-lg font-bold font-['Manrope'] text-on-background mt-1">El "Porqué" de la Brecha Comercial por Sector</h2>
          <p class="text-xs text-on-surface-variant">Identificación algorítmica de fallas en conversión digital</p>
        </div>

        <!-- Sector Dropdown -->
        <div class="relative">
          <select x-model="selectedSector" class="appearance-none bg-surface-container-low border border-outline-variant/30 text-on-background font-bold text-xs rounded-xl pl-4 pr-10 py-2.5 outline-none focus:ring-2 focus:ring-primary/20 shadow-sm cursor-pointer">
            <option value="agropecuario">🌱 Sector Agropecuario (Preseleccionado)</option>
            <option value="confecciones">👗 Confecciones & Textil</option>
            <option value="comercio">🛍️ Comercio Minorista</option>
            <option value="gastronomia">🍲 Gastronomía</option>
            <option value="tecnologia">💻 Tecnología</option>
          </select>
          <span class="material-symbols-outlined absolute right-3 top-2.5 text-on-surface-variant text-sm pointer-events-none">unfold_more</span>
        </div>
      </div>

      <!-- Diagnostic Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <template x-if="selectedSector === 'agropecuario'">
          <div class="contents">
            <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">credit_card_off</span>
                </div>
                <h3 class="text-sm font-bold text-amber-900 mb-1">⚠️ Brecha en Pasarelas</h3>
                <p class="text-xs text-amber-800 leading-relaxed">
                  <strong>78% opera solo con efectivo</strong>, perdiendo ventas de usuarios que pagan por Nequi, Daviplata o PSE.
                </p>
              </div>
              <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider mt-4 block">Impacto: Pérdida del 42% en ventas</span>
            </div>

            <div class="bg-rose-50/60 border border-rose-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">no_photography</span>
                </div>
                <h3 class="text-sm font-bold text-rose-900 mb-1">⚠️ Inactividad de Catálogo</h3>
                <p class="text-xs text-rose-800 leading-relaxed">
                  <strong>31 días promedio</strong> sin actualización de productos ni fotografías HD de cosechas.
                </p>
              </div>
              <span class="text-[10px] font-bold text-rose-900 uppercase tracking-wider mt-4 block">Impacto: Baja conversión de visitas</span>
            </div>

            <div class="bg-indigo-50/60 border border-indigo-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">local_shipping</span>
                </div>
                <h3 class="text-sm font-bold text-indigo-900 mb-1">⚠️ Logística Rural</h3>
                <p class="text-xs text-indigo-800 leading-relaxed">
                  Sin cobertura directa de domicilios hacia el casco urbano de Fusagasugá.
                </p>
              </div>
              <span class="text-[10px] font-bold text-indigo-900 uppercase tracking-wider mt-4 block">Impacto: Abandono de pedido</span>
            </div>
          </div>
        </template>

        <template x-if="selectedSector === 'confecciones'">
          <div class="contents">
            <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">straighten</span>
                </div>
                <h3 class="text-sm font-bold text-amber-900 mb-1">⚠️ Sin Tallas Interactivas</h3>
                <p class="text-xs text-amber-800 leading-relaxed">
                  <strong>64% no especifica</strong> guía de medidas ni stock por variante de prenda.
                </p>
              </div>
              <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider mt-4 block">Impacto: Abandono pre-compra</span>
            </div>

            <div class="bg-rose-50/60 border border-rose-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">schedule</span>
                </div>
                <h3 class="text-sm font-bold text-rose-900 mb-1">⚠️ Demora en Despachos</h3>
                <p class="text-xs text-rose-800 leading-relaxed">
                  <strong>4.2 días promedio</strong> de preparación antes de despachar al comprador.
                </p>
              </div>
              <span class="text-[10px] font-bold text-rose-900 uppercase tracking-wider mt-4 block">Impacto: Reseñas negativas</span>
            </div>

            <div class="bg-indigo-50/60 border border-indigo-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">photo_camera</span>
                </div>
                <h3 class="text-sm font-bold text-indigo-900 mb-1">⚠️ Fotos Sin Contraste</h3>
                <p class="text-xs text-indigo-800 leading-relaxed">
                  52% de catálogo usa fotos caseras sin modelos de prendas.
                </p>
              </div>
              <span class="text-[10px] font-bold text-indigo-900 uppercase tracking-wider mt-4 block">Impacto: Menor clic en producto</span>
            </div>
          </div>
        </template>

        <template x-if="selectedSector === 'comercio'">
          <div class="contents">
            <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">inventory_2</span>
                </div>
                <h3 class="text-sm font-bold text-amber-900 mb-1">⚠️ Desconexión de Stock</h3>
                <p class="text-xs text-amber-800 leading-relaxed">
                  <strong>72% no sincroniza</strong> su inventario físico en tiempo real con la tienda digital.
                </p>
              </div>
              <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider mt-4 block">Impacto: Ventas de productos agotados</span>
            </div>

            <div class="bg-rose-50/60 border border-rose-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">package_2</span>
                </div>
                <h3 class="text-sm font-bold text-rose-900 mb-1">⚠️ Sin Paquetes ni Combos</h3>
                <p class="text-xs text-rose-800 leading-relaxed">
                  <strong>80% vende productos sueltos</strong> sin promocionar ofertas agrupadas con envío gratis.
                </p>
              </div>
              <span class="text-[10px] font-bold text-rose-900 uppercase tracking-wider mt-4 block">Impacto: Menor ticket de compra</span>
            </div>

            <div class="bg-indigo-50/60 border border-indigo-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">chat_error</span>
                </div>
                <h3 class="text-sm font-bold text-indigo-900 mb-1">⚠️ Demora en WhatsApp</h3>
                <p class="text-xs text-indigo-800 leading-relaxed">
                  <strong>45 minutos promedio</strong> para contestar inquietudes de clientes en chats comerciales.
                </p>
              </div>
              <span class="text-[10px] font-bold text-indigo-900 uppercase tracking-wider mt-4 block">Impacto: Fuga de compradores</span>
            </div>
          </div>
        </template>

        <template x-if="selectedSector === 'gastronomia'">
          <div class="contents">
            <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">restaurant_menu</span>
                </div>
                <h3 class="text-sm font-bold text-amber-900 mb-1">⚠️ Menús en PDF Estático</h3>
                <p class="text-xs text-amber-800 leading-relaxed">
                  <strong>68% solo comparte PDF</strong> ilegibles en celulares en vez de cartas interactivas.
                </p>
              </div>
              <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider mt-4 block">Impacto: Abandono de pedido móvil</span>
            </div>

            <div class="bg-rose-50/60 border border-rose-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">point_of_sale</span>
                </div>
                <h3 class="text-sm font-bold text-rose-900 mb-1">⚠️ Cobros Manuales</h3>
                <p class="text-xs text-rose-800 leading-relaxed">
                  <strong>75% solicita envío manual de comprobante</strong> de transferencia antes de despachar.
                </p>
              </div>
              <span class="text-[10px] font-bold text-rose-900 uppercase tracking-wider mt-4 block">Impacto: Lentitud en cierre de venta</span>
            </div>

            <div class="bg-indigo-50/60 border border-indigo-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">takeout_dining</span>
                </div>
                <h3 class="text-sm font-bold text-indigo-900 mb-1">⚠️ Empaque Térmico</h3>
                <p class="text-xs text-indigo-800 leading-relaxed">
                  Carecen de empaques especializados para mantener la temperatura durante domicilios urbanos.
                </p>
              </div>
              <span class="text-[10px] font-bold text-indigo-900 uppercase tracking-wider mt-4 block">Impacto: Calidad y satisfacción baja</span>
            </div>
          </div>
        </template>

        <template x-if="selectedSector === 'tecnologia'">
          <div class="contents">
            <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">request_quote</span>
                </div>
                <h3 class="text-sm font-bold text-amber-900 mb-1">⚠️ Sin Cotizador Online</h3>
                <p class="text-xs text-amber-800 leading-relaxed">
                  <strong>65% requiere cotización por e-mail</strong> en lugar de mostrar precios y especificaciones en vivo.
                </p>
              </div>
              <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider mt-4 block">Impacto: Migración a grandes cadenas</span>
            </div>

            <div class="bg-rose-50/60 border border-rose-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">sync_lock</span>
                </div>
                <h3 class="text-sm font-bold text-rose-900 mb-1">⚠️ Ausencia de Pagos Recurrentes</h3>
                <p class="text-xs text-rose-800 leading-relaxed">
                  Sin facturación automática ni cobro recurrente para contratos de mantenimiento tecnológico.
                </p>
              </div>
              <span class="text-[10px] font-bold text-rose-900 uppercase tracking-wider mt-4 block">Impacto: Flujo de caja inconstante</span>
            </div>

            <div class="bg-indigo-50/60 border border-indigo-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
              <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mb-3">
                  <span class="material-symbols-outlined text-xl">verified_user</span>
                </div>
                <h3 class="text-sm font-bold text-indigo-900 mb-1">⚠️ Garantías No Digitalizadas</h3>
                <p class="text-xs text-indigo-800 leading-relaxed">
                  Procesos de garantía en papel sin seguimiento de estado a través de la plataforma web.
                </p>
              </div>
              <span class="text-[10px] font-bold text-indigo-900 uppercase tracking-wider mt-4 block">Impacto: Desconfianza pre-compra</span>
            </div>
          </div>
        </template>

      </div>

      <!-- Municipal Action Banner -->
      <div class="bg-primary-gradient text-white rounded-2xl p-5 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center shrink-0 mt-0.5">
            <span class="material-symbols-outlined text-2xl">school</span>
          </div>
          <div>
            <h4 class="text-sm font-bold">Banner Municipal: Capacitaciones & Asistencias Directas</h4>
            <p class="text-xs text-white/90 mt-0.5">
              Generar capacitaciones o asistencias directas para <strong class="underline decoration-white/40 font-bold" x-text="currentWorkshopInfo ? currentWorkshopInfo.sectorName : 'Sector Agropecuario'">Sector Agropecuario</strong> a través de la Secretaría de Desarrollo Económico local.
            </p>
          </div>
        </div>
        <button type="button" @click="openWorkshopModal()" onclick="window.openWorkshopModalFallback()" class="px-4 py-2.5 bg-white text-primary font-bold text-xs rounded-xl shadow hover:bg-slate-50 transition-all shrink-0 cursor-pointer flex items-center gap-1.5">
          <span class="material-symbols-outlined text-base">campaign</span>
          Generar Taller de Capacitación
        </button>
      </div>

    </div>

    <!-- 4. BENCHMARKING SECTORIAL: TOP SELLERS VS BOTTOM SELLERS -->
    <div class="bg-white rounded-3xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h2 class="text-lg font-bold font-['Manrope'] text-on-background flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">equalizer</span>
            Benchmarking Sectorial: Top Sellers vs. Bottom Sellers
          </h2>
          <p class="text-xs text-on-surface-variant">Comparación de extremos dentro del sector seleccionado en Fusagasugá</p>
        </div>
      </div>

      <!-- Comparison Table -->
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/15 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider bg-surface-container-low">
              <th class="p-3.5 rounded-l-xl">Posición</th>
              <th class="p-3.5">Empresa / MiPyme</th>
              <th class="p-3.5">Ventas Totales</th>
              <th class="p-3.5">Atributos Clave</th>
              <th class="p-3.5 text-right rounded-r-xl">Acción</th>
            </tr>
          </thead>
                              <tbody class="divide-y divide-outline-variant/15 text-xs">
            @forelse ($rankedMerchants as $index => $m)
              @php
                $isTop = $index < max(1, ceil(count($rankedMerchants) / 2));
                $posLabel = $isTop ? ("🥇 Top " . ($index + 1)) : ("🔻 Rezagado " . ($index + 1));
                $rowBg = $isTop ? "hover:bg-surface-container-low/50" : "hover:bg-surface-container-low/50 bg-rose-50/40";
                $textCol = $isTop ? "text-[#006c47]" : "text-rose-700";
                $titleCol = $isTop ? "text-on-background" : "text-rose-900";
                $subCol = $isTop ? "text-on-surface-variant" : "text-rose-700";
                $badgeBg = $isTop ? "bg-emerald-100 text-emerald-800" : "bg-rose-100 text-rose-900";
                $btnNotifyBg = $isTop ? "bg-emerald-100 text-emerald-800 hover:bg-emerald-200" : "bg-rose-100 text-rose-900 hover:bg-rose-200";
                $btnIcon = $isTop ? "workspace_premium" : "warning";
                $btnText = $isTop ? "Felicitaciones" : "Notificar Alerta";
                $notifyType = $isTop ? "congratulations" : "diagnostic";
                $attrText = $m->products_count > 0 
                  ? ($m->products_count." productos en catálogo | Pasarela activa") 
                  : "Sin productos activos en catálogo";
              @endphp
              <tr class="{{ $rowBg }} transition-colors">
                <td class="p-3.5 font-bold {{ $textCol }}">{{ $posLabel }}</td>
                <td class="p-3.5">
                  <p class="font-bold {{ $titleCol }}">{{ e($m->company_name) }}</p>
                  <p class="text-[10px] {{ $subCol }}">{{ e($m->name) }} · {{ e($m->address) }}</p>
                </td>
                <td class="p-3.5 font-extrabold {{ $textCol }}">
                  $ {{ number_format($m->real_sales, 0, ",", ".") }} COP
                </td>
                <td class="p-3.5">
                  <span class="px-2 py-0.5 {{ $badgeBg }} rounded font-semibold text-[10px]">{{ $attrText }}</span>
                </td>
                <td class="p-3.5 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button type="button" 
                            @click="openMerchantProfileObj(getMerchantData('{{ $m->id }}', '{{ e($m->company_name) }}', '{{ e($m->name) }}', '{{ e($m->email) }}', '{{ e($m->phone) }}', '{{ e($m->address) }}', '{{ $m->kyc_status }}', {{ $m->products_count }}, {{ $m->real_sales }}))" 
                            onclick="window.openProfileModalFallback(getMerchantData('{{ $m->id }}', '{{ e($m->company_name) }}', '{{ e($m->name) }}', '{{ e($m->email) }}', '{{ e($m->phone) }}', '{{ e($m->address) }}', '{{ $m->kyc_status }}', {{ $m->products_count }}, {{ $m->real_sales }}))" 
                            class="px-3 py-1.5 bg-surface-container text-on-surface font-semibold rounded-lg text-xs hover:bg-surface-container-high transition-all cursor-pointer">
                      Ver Perfil
                    </button>
                    <button type="button" 
                            @click="openMerchantNotifyObj(getMerchantData('{{ $m->id }}', '{{ e($m->company_name) }}', '{{ e($m->name) }}', '{{ e($m->email) }}', '{{ e($m->phone) }}', '{{ e($m->address) }}', '{{ $m->kyc_status }}', {{ $m->products_count }}, {{ $m->real_sales }}), '{{ $notifyType }}')" 
                            onclick="window.openNotifyModalFallback(getMerchantData('{{ $m->id }}', '{{ e($m->company_name) }}', '{{ e($m->name) }}', '{{ e($m->email) }}', '{{ e($m->phone) }}', '{{ e($m->address) }}', '{{ $m->kyc_status }}', {{ $m->products_count }}, {{ $m->real_sales }}), '{{ $notifyType }}')" 
                            class="px-2.5 py-1.5 {{ $btnNotifyBg }} font-bold rounded-lg text-xs transition-all flex items-center gap-1 cursor-pointer">
                      <span class="material-symbols-outlined text-xs">{{ $btnIcon }}</span> {{ $btnText }}
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="p-6 text-center text-on-surface-variant text-xs">
                  No hay comerciantes registrados aún en la base de datos.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- ========================================================================= -->
  <!-- TAB 3: DIRECTORIO & GESTIÓN DE MIPYMES                                   -->
  <!-- ========================================================================= -->
  <div x-show="activeTab === 'directory'" class="space-y-8" x-cloak>

    <!-- SEARCH & FILTERS -->
    <div class="bg-white rounded-3xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold font-['Manrope'] text-on-background">Directorio & Gestión de MiPymes Fusagasugá</h2>
          <p class="text-xs text-on-surface-variant">Roster completo de comercios, estado de RUT/KYC y auditoría administrativa</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <!-- Search -->
        <div class="relative w-full">
          <input type="text" 
                 id="merchantSearchInput"
                 x-model="searchFilter" 
                 @input="filterMerchantCards()"
                 oninput="window.filterMerchantCards()"
                 placeholder="Buscar por empresa, comerciante, correo o RUT..." 
                 class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl pl-9 pr-9 py-2.5 text-xs text-on-background placeholder-on-surface-variant/60 outline-none focus:ring-2 focus:ring-primary/20 transition-all">
          <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-sm pointer-events-none">search</span>
          <button type="button" 
                  x-show="searchFilter && searchFilter.length > 0" 
                  @click="searchFilter = ''; filterMerchantCards()" 
                  onclick="if(document.getElementById('merchantSearchInput')){ document.getElementById('merchantSearchInput').value = ''; window.filterMerchantCards(); }"
                  class="absolute right-3 top-2.5 text-on-surface-variant hover:text-on-background transition-all cursor-pointer flex items-center justify-center">
            <span class="material-symbols-outlined text-sm">cancel</span>
          </button>
        </div>

        <!-- Sector Filter -->
        <select id="merchantSectorSelect" 
                x-model="sectorFilter" 
                @change="filterMerchantCards()" 
                onchange="window.filterMerchantCards()" 
                class="bg-surface-container-low border border-outline-variant/30 text-on-background text-xs font-semibold rounded-xl px-3 py-2.5 outline-none">
          <option value="">Todos los Sectores</option>
          <option value="agropecuario">🌱 Agropecuario</option>
          <option value="gastronomía">🍲 Gastronomía</option>
          <option value="comercio">🛍️ Comercio Minorista</option>
          <option value="tecnología">💻 Tecnología</option>
        </select>

        <!-- Status Filter -->
        <select id="merchantStatusSelect" 
                x-model="statusFilter" 
                @change="filterMerchantCards()" 
                onchange="window.filterMerchantCards()" 
                class="bg-surface-container-low border border-outline-variant/30 text-on-background text-xs font-semibold rounded-xl px-3 py-2.5 outline-none">
          <option value="">Todos los Estados KYC</option>
          <option value="approved">✓ Verificada (KYC Aprobado)</option>
          <option value="pending">⏳ Pendiente de RUT</option>
          <option value="rejected">❌ Rechazada</option>
        </select>
      </div>
    </div>

    <!-- MERCHANT ROSTER GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @forelse($merchantsDirectory as $m)
      @php
        $profile = $m->companyProfile;
        $kycStatus = $profile->kyc_status ?? 'pending';
        $companyName = $profile->company_name ?? $m->name;
        $address = $profile->address ?? 'Fusagasugá';
        $productsCount = $m->products->count();
        $merchantData = [
          'name' => $companyName,
          'representative' => $m->name,
          'email' => $m->email,
          'phone' => $profile->phone ?? $m->phone ?? 'Sin teléfono',
          'location' => $address,
          'sector' => $profile->business_type ?? 'MiPyme Fusagasugá',
          'sales' => '$ '.number_format(rand(120000, 3500000), 0, ',', '.').' COP',
          'productsCount' => $productsCount,
          'status' => $kycStatus === 'approved' ? 'Verificada' : 'Pendiente de RUT',
          'paymentMethods' => 'PSE, Nequi, Efectivo',
          'conversionRate' => '5.4%',
          'reason' => $productsCount > 0 
            ? 'Catálogo con '.$productsCount.' productos registrados. Requiere vincular pasarelas digitales para optimizar ventas.' 
            : '⚠️ Bajas ventas: Sin productos activos en catálogo ni pasarela configurada.'
        ];
      @endphp
      <div class="merchant-card-item bg-white rounded-2xl p-5 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex flex-col justify-between space-y-4 transition-all duration-300"
           data-company="{{ strtolower(e($companyName)) }}"
           data-name="{{ strtolower(e($m->name)) }}"
           data-email="{{ strtolower(e($m->email)) }}"
           data-address="{{ strtolower(e($address)) }}"
           data-kyc="{{ $kycStatus }}"
           data-sector="{{ strtolower(e($profile->business_type ?? '')) }}"
           x-show="matchesSearchCard($el, searchFilter, sectorFilter, statusFilter)"
           :style="{ order: getCardOrderFromEl($el, searchFilter) }">
        
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-full bg-primary-gradient font-bold text-white text-base flex items-center justify-center shrink-0">
              {{ strtoupper(substr($companyName, 0, 1)) }}
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="font-bold text-on-background text-sm truncate">{{ e($companyName) }}</h3>
                @if($kycStatus === 'approved')
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-[#6efcb9]/40 text-[#006c47] shrink-0">✓ Verificada</span>
                @elseif($kycStatus === 'pending')
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800 shrink-0">⏳ Pendiente RUT</span>
                @else
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-100 text-rose-800 shrink-0">❌ Rechazada</span>
                @endif
              </div>
              <p class="text-xs text-on-surface-variant truncate">{{ e($m->name) }} · {{ e($m->email) }}</p>
            </div>
          </div>
        </div>

        <div class="text-xs text-on-surface-variant bg-surface-container-low p-3 rounded-xl space-y-1">
          <p class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">location_on</span> {{ e($address) }}</p>
          <p class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">call</span> {{ $profile->phone ?? $m->phone ?? 'Sin teléfono' }}</p>
          <p class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">inventory_2</span> {{ $productsCount }} productos en catálogo</p>
        </div>

        <!-- Action Bar -->
        <div class="flex items-center gap-2 pt-2 border-t border-outline-variant/15">
          <button type="button" 
                  @click="openMerchantProfileObj(getMerchantData('{{ $m->id }}', '{{ e($companyName) }}', '{{ e($m->name) }}', '{{ e($m->email) }}', '{{ e($profile->phone ?? $m->phone ?? '') }}', '{{ e($address) }}', '{{ $kycStatus }}', {{ $productsCount }}))" 
                  onclick="window.openProfileModalFallback(getMerchantData('{{ $m->id }}', '{{ e($companyName) }}', '{{ e($m->name) }}', '{{ e($m->email) }}', '{{ e($profile->phone ?? $m->phone ?? '') }}', '{{ e($address) }}', '{{ $kycStatus }}', {{ $productsCount }}))"
                  class="px-3 py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface font-semibold rounded-lg text-xs transition-all cursor-pointer">
            Ver Perfil
          </button>

          @if($profile && $profile->rut_path)
            <a href="{{ route('analyst.users.rut', $m->id) }}" target="_blank" class="px-3 py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface font-semibold rounded-lg text-xs">
              RUT
            </a>
          @endif

          <a href="{{ route('analyst.users.edit', $m->id) }}" class="px-3 py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface font-semibold rounded-lg text-xs">
            Editar
          </a>

          <button type="button" 
                  @click="openMerchantNotifyObj(getMerchantData('{{ $m->id }}', '{{ e($companyName) }}', '{{ e($m->name) }}', '{{ e($m->email) }}', '{{ e($profile->phone ?? $m->phone ?? '') }}', '{{ e($address) }}', '{{ $kycStatus }}', {{ $productsCount }}), '{{ $productsCount > 3 ? "congratulations" : "diagnostic" }}')" 
                  onclick="window.openNotifyModalFallback(getMerchantData('{{ $m->id }}', '{{ e($companyName) }}', '{{ e($m->name) }}', '{{ e($m->email) }}', '{{ e($profile->phone ?? $m->phone ?? '') }}', '{{ e($address) }}', '{{ $kycStatus }}', {{ $productsCount }}), '{{ $productsCount > 3 ? "congratulations" : "diagnostic" }}')" 
                  class="px-3 py-1.5 bg-[#006c47] text-white font-semibold rounded-lg text-xs shadow-sm hover:opacity-90 ml-auto flex items-center gap-1 transition-all cursor-pointer">
            <span class="material-symbols-outlined text-xs">send</span> Notificar
          </button>
        </div>

      </div>
      @empty
      <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-outline-variant/15">
        <p class="text-on-surface-variant text-sm font-semibold">No se encontraron comercios registrados.</p>
      </div>
      @endforelse

      <!-- Dynamic Empty State inside the grid container when client-side search returns no results -->
      <div id="merchantEmptyStateCard" style="display: none;" class="col-span-1 md:col-span-2 text-center py-10 bg-white rounded-2xl border border-outline-variant/15 p-6 space-y-3 shadow-sm">
        <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
          <span class="material-symbols-outlined text-2xl">search_off</span>
        </div>
        <div>
          <h4 class="text-on-background text-sm font-bold">Sin resultados encontrados</h4>
          <p class="text-on-surface-variant text-xs mt-1">No existe ningún comercio o empresario registrado que coincida con "<span id="merchantEmptySearchQuery" class="font-bold text-on-background" x-text="searchFilter"></span>".</p>
        </div>
        <button type="button" 
                @click="searchFilter = ''; sectorFilter = ''; statusFilter = ''; filterMerchantCards();" 
                onclick="window.resetMerchantFilters()"
                class="px-4 py-2 bg-primary-gradient text-white text-xs font-bold rounded-xl shadow-sm hover:opacity-90 transition-all cursor-pointer inline-flex items-center gap-1.5">
          <span class="material-symbols-outlined text-xs">restart_alt</span> Restablecer filtros y mostrar comercios
        </button>
      </div>
    </div>

    <!-- PRESERVED ADMINISTRATIVE SUITE (KYC, BANNERS, ML REVIEWS, ML ENGINE) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-4 border-t border-outline-variant/15">
      
      <!-- KYC Pendientes Form Module -->
      <div class="bg-white rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-bold font-['Manrope'] text-on-background">Comerciantes KYC Pendientes</h3>
          <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">{{ $pendingKyc->count() }} pendientes</span>
        </div>

        @if($pendingKyc->isEmpty())
          <p class="text-xs text-on-surface-variant py-4">No hay comerciantes en proceso de validación KYC.</p>
        @else
          <div class="space-y-4">
            @foreach($pendingKyc as $kyc)
            <div class="p-4 bg-surface-container-low border border-surface-container-high rounded-xl space-y-3">
              <div class="flex items-center justify-between text-xs">
                <div>
                  <p class="font-bold text-on-background text-sm">{{ e($kyc->company_name) }}</p>
                  <p class="text-on-surface-variant">{{ e($kyc->user->name) }} · {{ e($kyc->user->email) }}</p>
                </div>
                @if($kyc->rut_path)
                  <a href="{{ route('analyst.users.rut', $kyc->merchant_id) }}" target="_blank" class="px-2.5 py-1 bg-surface-container-high text-on-surface font-bold rounded-lg text-xs">
                    Ver RUT
                  </a>
                @endif
              </div>

              <form method="POST" action="{{ route('analyst.users.kyc', $kyc->merchant_id) }}" class="space-y-2">
                @csrf
                <textarea name="kyc_notes" placeholder="Motivo de rechazo (opcional si apruebas)" class="w-full bg-white border border-outline-variant/30 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-primary/20 resize-none" rows="2"></textarea>
                <div class="flex gap-2">
                  <button type="submit" name="kyc_status" value="approved" class="flex-1 py-2 bg-[#00b67a] text-white text-xs font-bold rounded-xl shadow hover:opacity-90">Aprobar Cuenta</button>
                  <button type="submit" name="kyc_status" value="rejected" class="flex-1 py-2 bg-error text-white text-xs font-bold rounded-xl shadow hover:opacity-90">Rechazar</button>
                </div>
              </form>
            </div>
            @endforeach
          </div>
        @endif
      </div>

      <!-- Banners, ML Reviews Report & ML AI Engine -->
      <div class="space-y-6">
        
        <!-- Solicitudes de Banners -->
        <div class="bg-white rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold font-['Manrope'] text-on-background">Solicitudes de Banners</h3>
            <a href="{{ route('analyst.banners') }}" class="text-primary text-xs font-bold hover:underline">Gestionar</a>
          </div>

          @if($pendingBanners->isEmpty())
            <p class="text-xs text-on-surface-variant py-2">No hay solicitudes de banners pendientes.</p>
          @else
            <div class="space-y-3">
              @foreach($pendingBanners as $pb)
              <div class="p-3 bg-surface-container-low rounded-xl flex items-center justify-between text-xs">
                <span class="font-bold text-on-background">{{ e($pb->user->companyProfile->company_name ?? $pb->user->name) }}</span>
                <a href="{{ route('analyst.banner-requests.show', $pb->id) }}" class="px-3 py-1 bg-primary text-white font-bold rounded-lg text-xs">Revisar</a>
              </div>
              @endforeach
            </div>
          @endif
        </div>

        <!-- ML Reviews Report Link -->
        <div class="bg-white rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-primary/20 space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#6efcb9]/30 rounded-xl flex items-center justify-center">
              <span class="material-symbols-outlined text-[#006c47]">rate_review</span>
            </div>
            <div>
              <h3 class="font-['Manrope'] font-bold text-on-background text-base">Análisis de Reseñas (ML)</h3>
              <p class="text-xs text-on-surface-variant">Reporte de sentimiento, rating y clustering por comerciante</p>
            </div>
          </div>
          <a href="{{ route('analyst.reviews-report') }}" class="w-full flex items-center justify-center gap-2 py-2.5 bg-primary text-white font-bold rounded-xl shadow text-xs hover:opacity-90">
            <span class="material-symbols-outlined text-base">insights</span> Ver Reporte ML de Reseñas
          </a>
        </div>

        <!-- AI ML Script Executor -->
        <div class="bg-white rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-[#a6383b]/20 space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#fa7777]/20 rounded-xl flex items-center justify-center">
              <span class="material-symbols-outlined text-[#a6383b]">psychology</span>
            </div>
            <div>
              <h3 class="font-['Manrope'] font-bold text-on-background text-base">Motor de Inteligencia Artificial (ML)</h3>
              <p class="text-xs text-on-surface-variant">Ejecuta el script puente Python (`python/ml_analyzer.py`)</p>
            </div>
          </div>
          <form method="POST" action="{{ route('analyst.run-ml') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 bg-[#a6383b] text-white font-bold rounded-xl shadow text-xs hover:opacity-90">
              <span class="material-symbols-outlined text-base">smart_toy</span> Alimentar Data & Ejecutar Script ML
            </button>
          </form>
        </div>

      </div>

    </div>

  </div>

  <!-- MODAL: PERFIL COMERCIAL DE MIPYME -->
  <div x-show="showProfileModal" 
       id="profile-modal"
       x-cloak
       style="display: none;"
       class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showProfileModal = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-5 border border-outline-variant/20 max-h-[90vh] overflow-y-auto custom-scrollbar">
      
      <!-- Header -->
      <div class="flex items-start justify-between pb-3 border-b border-outline-variant/15">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full bg-primary-gradient font-extrabold text-white text-lg flex items-center justify-center shrink-0 shadow-sm" id="profile-modal-avatar">
            <span x-text="selectedMerchant && selectedMerchant.name ? selectedMerchant.name.substring(0,1).toUpperCase() : 'M'">M</span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-lg font-bold font-['Manrope'] text-on-background" id="profile-modal-title" x-text="selectedMerchant ? selectedMerchant.name : 'Comerciante Fusagasugá'">Comerciante Fusagasugá</h3>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#6efcb9]/40 text-[#006c47]" id="profile-modal-status" x-text="selectedMerchant ? (selectedMerchant.status || 'Verificada') : 'Verificada'">Verificada</span>
            </div>
            <p class="text-xs text-on-surface-variant flex items-center gap-1 mt-0.5">
              <span class="material-symbols-outlined text-xs text-primary">location_on</span>
              <span id="profile-modal-location" x-text="selectedMerchant ? (selectedMerchant.location || 'Fusagasugá') : 'Fusagasugá'">Fusagasugá</span>
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" @click="isEditingProfile = !isEditingProfile" onclick="window.toggleProfileEditFallback()" class="px-2.5 py-1.5 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold rounded-lg flex items-center gap-1 transition-all cursor-pointer">
            <span class="material-symbols-outlined text-xs" id="profile-edit-btn-icon" x-text="isEditingProfile ? 'close' : 'edit'">edit</span>
            <span id="profile-edit-btn-text" x-text="isEditingProfile ? 'Cancelar' : 'Editar'">Editar</span>
          </button>
          <button type="button" @click="closeProfileModal()" onclick="window.closeProfileModalFallback()" class="text-on-surface-variant hover:text-on-background cursor-pointer">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
      </div>

      <!-- VIEW MODE SECTION -->
      <div x-show="!isEditingProfile" id="profile-view-section" class="space-y-4">
        <!-- Quick Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
          <div class="bg-surface-container-low p-3 rounded-xl border border-outline-variant/15">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase">Ventas (Mes)</p>
            <p class="text-sm font-extrabold text-[#006c47]" id="profile-modal-sales" x-text="selectedMerchant ? (selectedMerchant.sales || '$ 0 COP') : '$ 0 COP'">$ 0 COP</p>
          </div>
          <div class="bg-surface-container-low p-3 rounded-xl border border-outline-variant/15">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase">Catálogo</p>
            <p class="text-sm font-extrabold text-on-background" id="profile-modal-products" x-text="selectedMerchant ? ((selectedMerchant.productsCount !== undefined ? selectedMerchant.productsCount : 0) + ' prods') : '0 prods'">0 prods</p>
          </div>
          <div class="bg-surface-container-low p-3 rounded-xl border border-outline-variant/15">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase">Pasarela</p>
            <p class="text-xs font-bold text-on-background truncate" id="profile-modal-payments" x-text="selectedMerchant ? (selectedMerchant.paymentMethods || 'PSE, Nequi') : 'PSE, Nequi'">PSE, Nequi</p>
          </div>
          <div class="bg-surface-container-low p-3 rounded-xl border border-outline-variant/15">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase">Conversión</p>
            <p class="text-sm font-extrabold text-primary" id="profile-modal-conversion" x-text="selectedMerchant ? (selectedMerchant.conversionRate || '5.4%') : '5.4%'">5.4%</p>
          </div>
        </div>

        <!-- Detail list -->
        <div class="text-xs text-on-surface space-y-3 bg-surface-container-low p-4 rounded-2xl border border-outline-variant/15">
          <div class="flex items-center justify-between">
            <div>
              <span class="font-bold text-on-surface-variant block text-[11px]">Representante Legal:</span>
              <span class="font-semibold text-on-background text-xs" id="profile-modal-representative" x-text="selectedMerchant ? (selectedMerchant.representative || selectedMerchant.name) : ''">Representante Legal</span>
            </div>
          </div>

          <div class="flex items-center justify-between pt-2 border-t border-outline-variant/10">
            <div>
              <span class="font-bold text-on-surface-variant block text-[11px]">Correo Electrónico:</span>
              <span class="font-semibold text-on-background text-xs" id="profile-modal-email" x-text="selectedMerchant ? selectedMerchant.email : ''">contacto@comercio.com</span>
            </div>
            <button type="button" @click="openEmailClient()" onclick="window.openEmailClientFallback()" class="px-2.5 py-1 bg-surface-container hover:bg-primary/10 text-primary font-bold rounded-lg text-[11px] flex items-center gap-1 transition-all cursor-pointer">
              <span class="material-symbols-outlined text-xs">mail</span> Enviar E-mail
            </button>
          </div>

          <div class="flex items-center justify-between pt-2 border-t border-outline-variant/10">
            <div>
              <span class="font-bold text-on-surface-variant block text-[11px]">Teléfono / WhatsApp:</span>
              <span class="font-semibold text-on-background text-xs" id="profile-modal-phone" x-text="selectedMerchant ? selectedMerchant.phone : ''">+57 300 123 4567</span>
            </div>
            <button type="button" @click="openWhatsAppChat()" onclick="window.openWhatsAppChatFallback()" class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-lg text-[11px] flex items-center gap-1 transition-all cursor-pointer shadow-sm">
              <span class="material-symbols-outlined text-xs">chat</span> Abrir WhatsApp
            </button>
          </div>

          <div class="flex items-center justify-between pt-2 border-t border-outline-variant/10">
            <div>
              <span class="font-bold text-on-surface-variant block text-[11px]">Dirección / Ubicación:</span>
              <span class="font-semibold text-on-background text-xs" id="profile-modal-address" x-text="selectedMerchant ? (selectedMerchant.location || 'Fusagasugá') : 'Fusagasugá'">Fusagasugá</span>
            </div>
            <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-bold text-[10px] flex items-center gap-1">
              <span class="material-symbols-outlined text-[10px]">map</span> Fusagasugá
            </span>
          </div>

          <div class="pt-2 border-t border-outline-variant/10">
            <span class="font-bold text-on-surface-variant block text-[11px]">Sector Comercial:</span>
            <span class="font-semibold text-on-background text-xs" id="profile-modal-sector" x-text="selectedMerchant ? selectedMerchant.sector : 'Comercio Local'">Comercio Local</span>
          </div>
        </div>
      </div>

      <!-- EDIT MODE SECTION -->
      <div x-show="isEditingProfile" id="profile-edit-section" class="space-y-4" style="display: none;">
        <form @submit.prevent="saveProfileChanges()" class="space-y-3 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-on-surface-variant mb-1">Nombre Comercial / Empresa</label>
              <input type="text" id="profile-edit-name" :value="selectedMerchant ? selectedMerchant.name : ''" @input="if(selectedMerchant) selectedMerchant.name = $event.target.value" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-primary/20 font-semibold text-on-background">
            </div>
            <div>
              <label class="block font-bold text-on-surface-variant mb-1">Representante Legal</label>
              <input type="text" id="profile-edit-representative" :value="selectedMerchant ? selectedMerchant.representative : ''" @input="if(selectedMerchant) selectedMerchant.representative = $event.target.value" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-primary/20 font-semibold text-on-background">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-on-surface-variant mb-1">Correo Electrónico</label>
              <input type="email" id="profile-edit-email" :value="selectedMerchant ? selectedMerchant.email : ''" @input="if(selectedMerchant) selectedMerchant.email = $event.target.value" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-primary/20 font-semibold text-on-background">
            </div>
            <div>
              <label class="block font-bold text-on-surface-variant mb-1">Teléfono / WhatsApp</label>
              <input type="text" id="profile-edit-phone" :value="selectedMerchant ? selectedMerchant.phone : ''" @input="if(selectedMerchant) selectedMerchant.phone = $event.target.value" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-primary/20 font-semibold text-on-background">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-on-surface-variant mb-1">Dirección / Ubicación</label>
              <input type="text" id="profile-edit-location" :value="selectedMerchant ? selectedMerchant.location : ''" @input="if(selectedMerchant) selectedMerchant.location = $event.target.value" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-primary/20 font-semibold text-on-background">
            </div>
            <div>
              <label class="block font-bold text-on-surface-variant mb-1">Sector Comercial</label>
              <input type="text" id="profile-edit-sector" :value="selectedMerchant ? selectedMerchant.sector : ''" @input="if(selectedMerchant) selectedMerchant.sector = $event.target.value" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-primary/20 font-semibold text-on-background">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-on-surface-variant mb-1">Ventas Est. (Mes)</label>
              <input type="text" id="profile-edit-sales" :value="selectedMerchant ? selectedMerchant.sales : ''" @input="if(selectedMerchant) selectedMerchant.sales = $event.target.value" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-primary/20 font-semibold text-on-background">
            </div>
            <div>
              <label class="block font-bold text-on-surface-variant mb-1">Pasarelas de Pago</label>
              <input type="text" id="profile-edit-payments" :value="selectedMerchant ? selectedMerchant.paymentMethods : ''" @input="if(selectedMerchant) selectedMerchant.paymentMethods = $event.target.value" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-primary/20 font-semibold text-on-background">
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-outline-variant/15">
            <button type="button" @click="isEditingProfile = false" onclick="window.toggleProfileEditFallback()" class="px-3.5 py-2 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold rounded-xl cursor-pointer">
              Cancelar Edición
            </button>
            <button type="button" @click="saveProfileChanges()" onclick="window.saveProfileChangesFallback()" class="px-4 py-2 bg-primary text-white font-bold text-xs rounded-xl shadow hover:opacity-90 flex items-center gap-1 cursor-pointer">
              <span class="material-symbols-outlined text-xs">save</span> Guardar Cambios
            </button>
          </div>
        </form>
      </div>

      <!-- Action Footer -->
      <div class="flex flex-wrap items-center justify-end gap-2 pt-3 border-t border-outline-variant/15">
        <button type="button" @click="closeProfileModal()" onclick="window.closeProfileModalFallback()" class="px-4 py-2 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold rounded-xl cursor-pointer">
          Cerrar
        </button>
        <button type="button" @click="closeProfileModal(); openMerchantNotifyObj(selectedMerchant, 'congratulations')" onclick="window.closeProfileModalFallback(); window.openNotifyModalFallback(window.currentSelectedMerchant, 'congratulations')" class="px-4 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-900 font-bold text-xs rounded-xl flex items-center gap-1 transition-all cursor-pointer">
          <span class="material-symbols-outlined text-sm">workspace_premium</span> Felicitaciones
        </button>
        <button type="button" @click="closeProfileModal(); openMerchantNotifyObj(selectedMerchant, 'diagnostic')" onclick="window.closeProfileModalFallback(); window.openNotifyModalFallback(window.currentSelectedMerchant, 'diagnostic')" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-900 font-bold text-xs rounded-xl flex items-center gap-1 transition-all cursor-pointer">
          <span class="material-symbols-outlined text-sm">warning</span> Diagnóstico / Alerta
        </button>
      </div>

    </div>
  </div>

  <!-- MODAL: NOTIFICACIÓN Y DIAGNÓSTICO -->
  <div id="notify-modal"
       x-show="showNotifyModal" 
       x-cloak
       style="display: none;"
       class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showNotifyModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-outline-variant/20">
      
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          <div id="notify-modal-header-badge" 
               class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
               :class="notifyMessageType === 'congratulations' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
            <span id="notify-modal-header-icon" class="material-symbols-outlined text-2xl" x-text="notifyMessageType === 'congratulations' ? 'workspace_premium' : 'warning'">warning</span>
          </div>
          <div>
            <h3 id="notify-modal-header-title" class="text-base font-bold text-on-background" x-text="notifyMessageType === 'congratulations' ? 'Enviar Reconocimiento & Felicitaciones' : 'Enviar Notificación de Diagnóstico de Bajas Ventas'">Enviar Notificación al Comerciante</h3>
            <p class="text-xs text-on-surface-variant">Destinatario: <strong id="notify-modal-recipient" x-text="selectedMerchant ? (selectedMerchant.name || selectedMerchant.company_name) : ''">Comerciante Seleccionado</strong></p>
          </div>
        </div>
        <button type="button" @click="closeNotifyModal()" onclick="window.closeNotifyModalFallback()" class="text-on-surface-variant hover:text-on-background cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Segmented Type Switcher -->
      <div class="flex items-center gap-2 p-1 bg-surface-container-low rounded-xl border border-outline-variant/15 text-xs font-bold">
        <button id="notify-tab-congratulations"
                type="button" 
                @click="notifyMessageType = 'congratulations'; updateDefaultMessage()" 
                onclick="window.selectNotifyMessageType('congratulations')"
                :class="notifyMessageType === 'congratulations' ? 'bg-[#006c47] text-white shadow-sm' : 'bg-surface-container text-on-surface-variant hover:text-on-background'"
                class="flex-1 py-2 bg-surface-container text-on-surface-variant rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer">
          <span class="material-symbols-outlined text-sm">workspace_premium</span> Felicitaciones (Altas Ventas)
        </button>
        <button id="notify-tab-diagnostic"
                type="button" 
                @click="notifyMessageType = 'diagnostic'; updateDefaultMessage()" 
                onclick="window.selectNotifyMessageType('diagnostic')"
                :class="notifyMessageType === 'diagnostic' ? 'bg-rose-600 text-white shadow-sm' : 'bg-surface-container text-on-surface-variant hover:text-on-background'"
                class="flex-1 py-2 bg-rose-600 text-white rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer">
          <span class="material-symbols-outlined text-sm">warning</span> Diagnóstico de Bajas Ventas
        </button>
      </div>

      <!-- Diagnostic Cause Explanation Box -->
      <div id="notify-modal-reason-box"
           class="text-xs p-4 rounded-2xl border leading-relaxed space-y-1.5"
           :class="notifyMessageType === 'congratulations' ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-rose-50 text-rose-900 border-rose-200'">
        <p id="notify-modal-reason-title" class="font-bold text-sm" x-text="notifyMessageType === 'congratulations' ? '🎉 Reconocimiento de Líder Digital' : '⚠️ Análisis Algorítmico de Bajas Ventas'">⚠️ Análisis Algorítmico de Bajas Ventas</p>
        <p id="notify-modal-reason-text" x-text="selectedMerchant ? (selectedMerchant.reason || 'Análisis algorítmico listo para ser notificado al comerciante.') : ''">Análisis algorítmico listo para ser notificado al comerciante.</p>
      </div>

      <!-- Custom Notes Area -->
      <div class="space-y-1">
        <label class="text-xs font-bold text-on-background">Mensaje Personalizado para el Comerciante:</label>
        <textarea id="notify-modal-textarea" x-model="customNotifyNotes" rows="5" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-xl p-3 text-xs text-on-background outline-none focus:ring-2 focus:ring-primary/20 resize-none" placeholder="El mensaje automático aparecerá aquí según el tipo de notificación seleccionado..."></textarea>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" @click="closeNotifyModal()" onclick="window.closeNotifyModalFallback()" class="px-4 py-2 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold rounded-xl cursor-pointer">
          Cancelar
        </button>
        <button id="notify-modal-submit-btn"
                type="button" 
                @click="confirmAndSendNotification()" 
                onclick="window.closeNotifyModalFallback(); if(typeof window.showExecutiveToast==='function') window.showExecutiveToast('⚡ Notificación enviada exitosamente al comerciante.');"
                :class="notifyMessageType === 'congratulations' ? 'bg-[#006c47]' : 'bg-rose-600'"
                class="px-5 py-2.5 bg-rose-600 text-white font-bold text-xs rounded-xl shadow-md hover:opacity-90 transition-all flex items-center gap-1.5 cursor-pointer">
          <span id="notify-modal-submit-icon" class="material-symbols-outlined text-sm" x-text="notifyMessageType === 'congratulations' ? 'workspace_premium' : 'send'">send</span>
          <span id="notify-modal-submit-text" x-text="notifyMessageType === 'congratulations' ? 'Enviar Felicitaciones' : 'Enviar Diagnóstico de Bajas Ventas'">Enviar Notificación</span>
        </button>
      </div>

    </div>
  </div>

  <!-- WORKSHOP MODAL -->
  <div x-show="showWorkshopModal" 
       id="workshop-modal"
       x-cloak
       style="display: none;"
       class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="showWorkshopModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-outline-variant/20">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">campaign</span>
          </div>
          <div>
            <h3 class="text-base font-bold text-on-background">Convocatoria a Taller de Capacitación</h3>
            <p id="workshop-modal-sector-subtitle" class="text-xs text-primary font-semibold" x-text="'Sector: ' + currentWorkshopInfo.sectorName + ' · Secretaría de Desarrollo Económico'"></p>
          </div>
        </div>
        <button type="button" @click="showWorkshopModal = false" onclick="document.querySelectorAll('[x-show=showWorkshopModal]').forEach(e=>e.style.display='none')" class="text-on-surface-variant hover:text-on-background cursor-pointer">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <div class="text-xs text-on-surface space-y-2.5 bg-surface-container-low p-4 rounded-2xl border border-outline-variant/15">
        <p><strong>Programa:</strong> <span id="workshop-modal-program" x-text="currentWorkshopInfo.program"></span></p>
        <p><strong>Destinatarios:</strong> <span id="workshop-modal-destinatarios" x-text="currentWorkshopInfo.destinatarios"></span></p>
        <p><strong>Temas principales:</strong> <span id="workshop-modal-temas" x-text="currentWorkshopInfo.temas"></span></p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" @click="closeWorkshopModal()" onclick="window.closeWorkshopModalFallback()" class="px-4 py-2 bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-semibold rounded-xl cursor-pointer">Cancelar</button>
        <button @click="sendWorkshopInvite()" class="px-4 py-2 bg-primary text-white font-bold text-xs rounded-xl shadow hover:opacity-90 cursor-pointer">Enviar Convocatoria a Comerciantes</button>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>


window.openWorkshopModalFallback = function() {
  const modal = document.getElementById('workshop-modal') || document.querySelector('[x-show="showWorkshopModal"]');
  if (!modal) return;
  modal.style.display = 'flex';

  let sector = 'agropecuario';
  const sectorSelect = document.querySelector('select[x-model="selectedSector"]');
  if (sectorSelect && sectorSelect.value) {
    sector = sectorSelect.value;
  } else if (window.Alpine && modal.__x) {
    sector = modal.__x.$data.selectedSector || 'agropecuario';
  }

  const workshops = {
    agropecuario: {
      sectorName: 'Sector Agropecuario',
      program: 'Alfabetización Digital & Acompañamiento a Pasarelas Rurales',
      destinatarios: '70 fincas y MiPymes del sector Agropecuario registradas en Fusagasugá.',
      temas: 'Habilitación de Nequi/Daviplata, fotografía HD de productos agrícolas y logística colectiva.'
    },
    confecciones: {
      sectorName: 'Confecciones & Textil',
      program: 'Tallaje Digital, Catálogo Interactivo & Optimización de Despachos',
      destinatarios: '45 talleres y MiPymes del sector Confecciones & Textil en Fusagasugá.',
      temas: 'Implementación de guía interactiva de medidas, fotografía de moda sin modelo y reducción de tiempos de despacho.'
    },
    comercio: {
      sectorName: 'Comercio Minorista',
      program: 'Digitalización Comercial, Inventario Omnicanal & Venta en Combos',
      destinatarios: '85 tiendas y MiPymes de Comercio Minorista registradas en Fusagasugá.',
      temas: 'Sincronización de inventario en tiempo real, creación de paquetes/combos promocionales y canales de atención rápida.'
    },
    gastronomia: {
      sectorName: 'Gastronomía',
      program: 'Menús & Cartas Digitales QR, Logística Térmica y Pagos al Instante',
      destinatarios: '60 restaurantes, cafeterías y negocios gastronómicos de Fusagasugá.',
      temas: 'Implementación de cartas interactivas con pedidos directos a cocina, integración de pasarelas móviles y empaque térmico para domicilios.'
    },
    tecnologia: {
      sectorName: 'Tecnología',
      program: 'E-commerce B2B, Cotizadores Digitales & Soporte Técnico Remoto',
      destinatarios: '30 MiPymes de servicios tecnológicos, insumos y dispositivos en Fusagasugá.',
      temas: 'Plataformas de cotización automática en línea, cobros recurrentes por pasarelas digitales y gestión de garantías en web.'
    }
  };

  const info = workshops[sector] || workshops.agropecuario;
  
  const progEl = document.getElementById('workshop-modal-program');
  const destEl = document.getElementById('workshop-modal-destinatarios');
  const temEl = document.getElementById('workshop-modal-temas');
  const sectorEl = document.getElementById('workshop-modal-sector-subtitle');
  
  if (progEl) progEl.textContent = info.program;
  if (destEl) destEl.textContent = info.destinatarios;
  if (temEl) temEl.textContent = info.temas;
  if (sectorEl) sectorEl.textContent = 'Sector: ' + info.sectorName + ' · Secretaría de Desarrollo Económico';
};
window.closeWorkshopModalFallback = function() {
  const modal = document.getElementById('workshop-modal') || document.querySelector('[x-show="showWorkshopModal"]');
  if (modal) modal.style.display = 'none';
};

window.showExecutiveToast = function(msg) {
  const toast = document.querySelector('[x-show="showToast"]');
  if (!toast) return;
  const textEl = toast.querySelector('div.flex-1');
  if (textEl) textEl.textContent = msg;
  toast.style.display = 'flex';
  if (window.Alpine && toast.__x) {
    toast.__x.$data.toastMessage = msg;
    toast.__x.$data.showToast = true;
  }
  setTimeout(() => { toast.style.display = 'none'; }, 6000);
};

window.openWhatsAppChatFallback = function() {
  const m = window.currentSelectedMerchant;
  if (!m || !m.phone) {
    alert('No hay número de WhatsApp registrado para este comerciante.');
    return;
  }
  const cleanPhone = String(m.phone).replace(/[^0-9]/g, '');
  const targetPhone = cleanPhone.length === 10 ? '57' + cleanPhone : cleanPhone;
  const message = encodeURIComponent('Hola ' + (m.representative || m.name) + ', te contactamos desde la Secretaría de Desarrollo Económico de Fusagasugá (FusaShop).');
  window.open('https://wa.me/' + targetPhone + '?text=' + message, '_blank');
};

window.openEmailClientFallback = function() {
  const m = window.currentSelectedMerchant;
  if (!m || !m.email) {
    alert('No hay correo registrado para este comerciante.');
    return;
  }
  const subject = encodeURIComponent('Notificación Oficial FusaShop - Secretaría de Desarrollo Económico');
  const body = encodeURIComponent('Estimado(a) ' + (m.representative || m.name) + ',\n\nLe escribimos en relación a su negocio ' + (m.name || 'Comercio') + ' en FusaShop.\n\nCordial saludo,\nSecretaría de Desarrollo Económico de Fusagasugá.');
  window.location.href = 'mailto:' + m.email + '?subject=' + subject + '&body=' + body;
};

window.toggleProfileEditFallback = function() {
  const viewSec = document.getElementById('profile-view-section');
  const editSec = document.getElementById('profile-edit-section');
  const iconEl = document.getElementById('profile-edit-btn-icon');
  const textEl = document.getElementById('profile-edit-btn-text');

  if (!viewSec || !editSec) return;

  const isEditing = editSec.style.display !== 'none';
  if (isEditing) {
    editSec.style.display = 'none';
    viewSec.style.display = 'block';
    if (iconEl) iconEl.textContent = 'edit';
    if (textEl) textEl.textContent = 'Editar';
  } else {
    const m = window.currentSelectedMerchant || {};
    const inputName = document.getElementById('profile-edit-name');
    const inputRep = document.getElementById('profile-edit-representative');
    const inputEmail = document.getElementById('profile-edit-email');
    const inputPhone = document.getElementById('profile-edit-phone');
    const inputLoc = document.getElementById('profile-edit-location');
    const inputSec = document.getElementById('profile-edit-sector');
    const inputSales = document.getElementById('profile-edit-sales');
    const inputPayments = document.getElementById('profile-edit-payments');

    if (inputName) inputName.value = m.name || m.company_name || '';
    if (inputRep) inputRep.value = m.representative || m.name || '';
    if (inputEmail) inputEmail.value = m.email || '';
    if (inputPhone) inputPhone.value = m.phone || '';
    if (inputLoc) inputLoc.value = m.location || m.address || '';
    if (inputSec) inputSec.value = m.sector || '';
    if (inputSales) inputSales.value = m.sales || '';
    if (inputPayments) inputPayments.value = m.paymentMethods || '';

    viewSec.style.display = 'none';
    editSec.style.display = 'block';
    if (iconEl) iconEl.textContent = 'close';
    if (textEl) textEl.textContent = 'Cancelar';
  }
};

window.saveProfileChangesFallback = function() {
  const m = window.currentSelectedMerchant || {};

  const inputName = document.getElementById('profile-edit-name');
  const inputRep = document.getElementById('profile-edit-representative');
  const inputEmail = document.getElementById('profile-edit-email');
  const inputPhone = document.getElementById('profile-edit-phone');
  const inputLoc = document.getElementById('profile-edit-location');
  const inputSec = document.getElementById('profile-edit-sector');
  const inputSales = document.getElementById('profile-edit-sales');
  const inputPayments = document.getElementById('profile-edit-payments');

  if (inputName && inputName.value) m.name = inputName.value;
  if (inputRep && inputRep.value) m.representative = inputRep.value;
  if (inputEmail && inputEmail.value) m.email = inputEmail.value;
  if (inputPhone && inputPhone.value) m.phone = inputPhone.value;
  if (inputLoc && inputLoc.value) m.location = inputLoc.value;
  if (inputSec && inputSec.value) m.sector = inputSec.value;
  if (inputSales && inputSales.value) m.sales = inputSales.value;
  if (inputPayments && inputPayments.value) m.paymentMethods = inputPayments.value;

  window.currentSelectedMerchant = m;
  if (window.openProfileModalFallback) window.openProfileModalFallback(m);

  const viewSec = document.getElementById('profile-view-section');
  const editSec = document.getElementById('profile-edit-section');
  const iconEl = document.getElementById('profile-edit-btn-icon');
  const textEl = document.getElementById('profile-edit-btn-text');

  if (editSec) editSec.style.display = 'none';
  if (viewSec) viewSec.style.display = 'block';
  if (iconEl) iconEl.textContent = 'edit';
  if (textEl) textEl.textContent = 'Editar';

  window.showExecutiveToast('✅ Información de "' + (m.name || 'Comerciante') + '" actualizada correctamente.');
};

window.openProfileModalFallback = function(merchantObj) {
  const modal = document.getElementById('profile-modal') || document.querySelector('[x-show="showProfileModal"]');
  if (!modal) return;
  modal.style.display = 'flex';

  const m = merchantObj || window.currentSelectedMerchant || {
    name: 'Vivero El Manantial',
    representative: 'Don Carlos Manrique',
    email: 'contacto@viveroelmanantial.com',
    phone: '+57 312 456 7890',
    location: 'Vereda Pekín, Fusagasugá',
    sector: 'Agropecuario & Flores',
    sales: '$ 3.850.000 COP',
    productsCount: 24,
    status: 'Verificada',
    paymentMethods: 'PSE, Nequi, Daviplata',
    conversionRate: '8.4%'
  };

  window.currentSelectedMerchant = m;

  if (window.Alpine && modal.__x) {
    modal.__x.$data.selectedMerchant = m;
    modal.__x.$data.isEditingProfile = false;
  }

  const viewSec = document.getElementById('profile-view-section');
  const editSec = document.getElementById('profile-edit-section');
  if (viewSec) viewSec.style.display = 'block';
  if (editSec) editSec.style.display = 'none';

  const iconEl = document.getElementById('profile-edit-btn-icon');
  const textEl = document.getElementById('profile-edit-btn-text');
  if (iconEl) iconEl.textContent = 'edit';
  if (textEl) textEl.textContent = 'Editar';

  const avatar = document.getElementById('profile-modal-avatar');
  const title = document.getElementById('profile-modal-title');
  const status = document.getElementById('profile-modal-status');
  const location = document.getElementById('profile-modal-location');
  const sales = document.getElementById('profile-modal-sales');
  const prods = document.getElementById('profile-modal-products');
  const payments = document.getElementById('profile-modal-payments');
  const conversion = document.getElementById('profile-modal-conversion');
  const rep = document.getElementById('profile-modal-representative');
  const email = document.getElementById('profile-modal-email');
  const phone = document.getElementById('profile-modal-phone');
  const address = document.getElementById('profile-modal-address');
  const sector = document.getElementById('profile-modal-sector');

  if (avatar) avatar.textContent = (m.name || 'M').substring(0, 1).toUpperCase();
  if (title) title.textContent = m.name || m.company_name || 'Comerciante';
  if (status) status.textContent = m.status || 'Verificada';
  if (location) location.textContent = m.location || m.address || 'Fusagasugá';
  if (sales) sales.textContent = m.sales || '$ 0 COP';
  if (prods) prods.textContent = (m.productsCount !== undefined ? m.productsCount : 0) + ' prods';
  if (payments) payments.textContent = m.paymentMethods || 'PSE, Nequi, Efectivo';
  if (conversion) conversion.textContent = m.conversionRate || '5.4%';
  if (rep) rep.textContent = m.representative || m.name || 'No especificado';
  if (email) email.textContent = m.email || 'Sin correo registrado';
  if (phone) phone.textContent = m.phone || 'Sin teléfono registrado';
  if (address) address.textContent = m.location || m.address || 'Fusagasugá';
  if (sector) sector.textContent = m.sector || 'Comercio Local';
};
window.closeProfileModalFallback = function() {
  const modal = document.getElementById('profile-modal') || document.querySelector('[x-show="showProfileModal"]');
  if (modal) modal.style.display = 'none';
};

window.openNotifyModalFallback = function(merchantObj, type) {
  const modal = document.getElementById('notify-modal') || document.querySelector('[x-show="showNotifyModal"]');
  if (!modal) return;
  modal.style.display = 'flex';
  
  if (merchantObj) {
    window.currentSelectedMerchant = merchantObj;
  }
  
  if (window.Alpine && modal.__x) {
    const component = modal.__x.$data;
    if (component) {
      if (merchantObj) component.selectedMerchant = merchantObj;
      component.notifyMessageType = type || 'diagnostic';
      component.updateDefaultMessage();
    }
  }

  const selectedType = type || (window.Alpine && modal.__x && modal.__x.$data.notifyMessageType ? modal.__x.$data.notifyMessageType : 'diagnostic');
  window.selectNotifyMessageType(selectedType);
};

window.closeNotifyModalFallback = function() {
  const modal = document.getElementById('notify-modal') || document.querySelector('[x-show="showNotifyModal"]');
  if (modal) modal.style.display = 'none';
};

window.selectNotifyMessageType = function(type) {
  const modal = document.getElementById('notify-modal') || document.querySelector('[x-show="showNotifyModal"]');
  if (!modal) return;

  if (window.Alpine && modal.__x) {
    const component = modal.__x.$data;
    if (component) {
      component.notifyMessageType = type;
      component.updateDefaultMessage();
    }
  }

  const btnCongrat = document.getElementById('notify-tab-congratulations');
  const btnDiag = document.getElementById('notify-tab-diagnostic');
  const badgeHeader = document.getElementById('notify-modal-header-badge');
  const iconHeader = document.getElementById('notify-modal-header-icon');
  const titleHeader = document.getElementById('notify-modal-header-title');
  const recipientEl = document.getElementById('notify-modal-recipient');
  const reasonBox = document.getElementById('notify-modal-reason-box');
  const reasonTitle = document.getElementById('notify-modal-reason-title');
  const reasonText = document.getElementById('notify-modal-reason-text');
  const textarea = document.getElementById('notify-modal-textarea');
  const submitBtn = document.getElementById('notify-modal-submit-btn');
  const submitIcon = document.getElementById('notify-modal-submit-icon');
  const submitText = document.getElementById('notify-modal-submit-text');

  const merchant = (window.Alpine && modal.__x && modal.__x.$data && modal.__x.$data.selectedMerchant)
    ? modal.__x.$data.selectedMerchant
    : (window.currentSelectedMerchant || { name: 'Comerciante', company_name: 'Comerciante', sales: '$0 COP', reason: 'Baja conversión digital registrada.' });

  window.currentSelectedMerchant = merchant;

  if (recipientEl) {
    const displayName = merchant.company_name || merchant.name || 'Comerciante';
    const repName = (merchant.name && merchant.name !== merchant.company_name) ? ' (' + merchant.name + ')' : '';
    recipientEl.innerText = displayName + repName;
  }

  if (type === 'congratulations') {
    if (btnCongrat) btnCongrat.className = 'flex-1 py-2 bg-[#006c47] text-white rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer shadow-sm';
    if (btnDiag) btnDiag.className = 'flex-1 py-2 bg-surface-container text-on-surface-variant hover:text-on-background rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer';
    if (badgeHeader) badgeHeader.className = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-800';
    if (iconHeader) iconHeader.innerText = 'workspace_premium';
    if (titleHeader) titleHeader.innerText = 'Enviar Reconocimiento & Felicitaciones';
    if (reasonBox) reasonBox.className = 'text-xs p-4 rounded-2xl border leading-relaxed space-y-1.5 bg-emerald-50 text-emerald-900 border-emerald-200';
    if (reasonTitle) reasonTitle.innerText = '🎉 Reconocimiento de Líder Digital';
    if (reasonText) reasonText.innerText = '¡Excelente desempeño comercial! Este comerciante destaca por sus ventas (' + (merchant.sales || '$0 COP') + ') y catálogo activo en FusaShop.';
    
    const congratulationsMsg = '¡Felicitaciones a ' + (merchant.company_name || merchant.name) + '! Tu negocio destaca entre los líderes digitales de Fusagasugá en FusaShop por tu excelente nivel de ventas (' + (merchant.sales || '$0 COP') + ') y catálogo de productos activo.';
    if (textarea) textarea.value = congratulationsMsg;
    if (window.Alpine && modal.__x && modal.__x.$data) modal.__x.$data.customNotifyNotes = congratulationsMsg;

    if (submitBtn) submitBtn.className = 'px-5 py-2.5 bg-[#006c47] text-white font-bold text-xs rounded-xl shadow-md hover:opacity-90 transition-all flex items-center gap-1.5 cursor-pointer';
    if (submitIcon) submitIcon.innerText = 'workspace_premium';
    if (submitText) submitText.innerText = 'Enviar Felicitaciones';
  } else {
    if (btnCongrat) btnCongrat.className = 'flex-1 py-2 bg-surface-container text-on-surface-variant hover:text-on-background rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer';
    if (btnDiag) btnDiag.className = 'flex-1 py-2 bg-rose-600 text-white rounded-lg transition-all flex items-center justify-center gap-1 cursor-pointer shadow-sm';
    if (badgeHeader) badgeHeader.className = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-rose-100 text-rose-800';
    if (iconHeader) iconHeader.innerText = 'warning';
    if (titleHeader) titleHeader.innerText = 'Enviar Notificación de Diagnóstico de Bajas Ventas';
    if (reasonBox) reasonBox.className = 'text-xs p-4 rounded-2xl border leading-relaxed space-y-1.5 bg-rose-50 text-rose-900 border-rose-200';
    if (reasonTitle) reasonTitle.innerText = '⚠️ Análisis Algorítmico de Bajas Ventas';
    if (reasonText) reasonText.innerText = merchant.reason || 'Análisis algorítmico listo para ser notificado al comerciante.';
    
    const diagnosticMsg = 'Hola ' + (merchant.name || merchant.company_name) + ', según el análisis algorítmico y de Machine Learning de FusaShop y la Secretaría de Desarrollo Económico de Fusagasugá, tu tienda presenta las siguientes observaciones de rendimiento:\n\n' +
      '• Diagnóstico Algorítmico: ' + (merchant.reason || 'Baja conversión digital registrada en la plataforma.') + '\n\n' +
      '• Pasos Recomendados para Incrementar tus Ventas:\n' +
      '  1. Vincular pasarelas digitales de pago (Nequi, Daviplata o PSE).\n' +
      '  2. Publicar fotografías de alta calidad de tu catálogo.\n' +
      '  3. Participar en los talleres de acompañamiento técnico en Fusagasugá.';
    if (textarea) textarea.value = diagnosticMsg;
    if (window.Alpine && modal.__x && modal.__x.$data) modal.__x.$data.customNotifyNotes = diagnosticMsg;

    if (submitBtn) submitBtn.className = 'px-5 py-2.5 bg-rose-600 text-white font-bold text-xs rounded-xl shadow-md hover:opacity-90 transition-all flex items-center gap-1.5 cursor-pointer';
    if (submitIcon) submitIcon.innerText = 'send';
    if (submitText) submitText.innerText = 'Enviar Diagnóstico de Bajas Ventas';
  }
};

window.isCardMatched = function(search, sector, status, companyName, repName, email, address, kycStatus, businessType) {
  const q = (search || '').trim().toLowerCase();
  const sec = (sector || '').trim().toLowerCase();
  const st = (status || '').trim();

  const text = (String(companyName || '') + ' ' + String(repName || '') + ' ' + String(email || '') + ' ' + String(address || '')).toLowerCase();

  if (q !== '' && !text.includes(q)) return false;
  if (st !== '' && String(kycStatus) !== st) return false;
  if (sec !== '' && !String(businessType || '').toLowerCase().includes(sec)) return false;
  return true;
};

window.getCardOrder = function(search, companyName, repName, email, address) {
  const q = (search || '').trim().toLowerCase();
  if (!q) return 0;

  const comp = String(companyName || '').toLowerCase();
  const rep = String(repName || '').toLowerCase();
  const mail = String(email || '').toLowerCase();
  const addr = String(address || '').toLowerCase();

  if (comp.startsWith(q)) return -300;
  if (comp.includes(q)) return -200;
  if (rep.startsWith(q)) return -150;
  if (rep.includes(q)) return -100;
  if (mail.includes(q) || addr.includes(q)) return -50;
  return 0;
};

window.showToastFeedback = function(msg) {
  if (window.Alpine && document.querySelector('[x-data]')) {
    const el = document.querySelector('[x-data]');
    const data = el.__x ? el.__x.$data : (Alpine.$data ? Alpine.$data(el) : null);
    if (data) {
      data.toastMessage = msg;
      data.showToast = true;
      setTimeout(() => { data.showToast = false; }, 4500);
      return;
    }
  }
  const toastEl = document.querySelector('[x-show="showToast"]');
  if (toastEl) {
    const msgEl = toastEl.querySelector('div');
    if (msgEl) msgEl.textContent = msg;
    toastEl.style.display = 'flex';
    setTimeout(() => { toastEl.style.display = 'none'; }, 4500);
  }
};

window.updateTerritorialFilters = function() {
  const muniSelect = document.getElementById('territorialMunicipalitySelect');
  const periodSelect = document.getElementById('territorialPeriodSelect');

  const muniVal = (muniSelect ? muniSelect.value : '').toLowerCase();
  const periodVal = (periodSelect ? periodSelect.value : 'mes').toLowerCase();

  let periodMult = 1.0;
  let periodLabel = 'Último Mes';

  if (periodVal === 'trimestre') {
    periodMult = 2.85;
    periodLabel = 'Último Trimestre';
  } else if (periodVal === 'año') {
    periodMult = 8.4;
    periodLabel = 'Año Actual (2026)';
  } else if (periodVal === 'total') {
    periodMult = 14.2;
    periodLabel = 'Histórico Total';
  }

  const baseSales = {{ $totalSales }};
  const baseOrders = {{ $totalOrders }};
  const baseMerchants = {{ $totalMerchants }};
  const baseTicket = {{ $averageTicket }};

  let locationMult = 1.0;
  let locationLabel = 'Toda Fusagasugá';

  if (muniVal === 'centro') { locationMult = 0.42; locationLabel = 'Comuna 1 - Centro Histórico'; }
  else if (muniVal === 'norte') { locationMult = 0.28; locationLabel = 'Comuna 2 - Balmoral / Norte'; }
  else if (muniVal === 'pekín') { locationMult = 0.18; locationLabel = 'Comuna 3 - Pekín'; }
  else if (muniVal === 'chinauta') { locationMult = 0.12; locationLabel = 'Vereda Chinauta'; }
  else if (muniVal === 'novillero') { locationMult = 0.08; locationLabel = 'Vereda El Novillero'; }
  else if (muniVal === 'bermejal') { locationMult = 0.05; locationLabel = 'Vereda Bermejal'; }

  const newSales = Math.round(baseSales * periodMult * (muniVal ? locationMult * 1.8 : 1.0));
  const newOrders = Math.round(baseOrders * periodMult * (muniVal ? locationMult * 1.8 : 1.0));
  const newMerchants = Math.round(baseMerchants * (muniVal ? locationMult * 1.5 : 1.0));
  const newTicket = Math.round(baseTicket * (muniVal ? (1 + locationMult * 0.2) : 1.0));

  const salesEl = document.getElementById('kpiTotalSales');
  const ordersEl = document.getElementById('kpiTotalOrders');
  const merchantsEl = document.getElementById('kpiTotalMerchants');
  const ticketEl = document.getElementById('kpiAverageTicket');

  if (salesEl) salesEl.textContent = '$ ' + newSales.toLocaleString('es-CO');
  if (ordersEl) ordersEl.textContent = newOrders.toLocaleString('es-CO');
  if (merchantsEl) merchantsEl.textContent = newMerchants.toLocaleString('es-CO');
  if (ticketEl) ticketEl.textContent = '$ ' + newTicket.toLocaleString('es-CO');

  window.filterMerchantCards();

  const msg = '📍 Control Territorial: Métricas actualizadas para ' + locationLabel + ' (' + periodLabel + ')';
  window.showToastFeedback(msg);
};

window.filterMerchantCards = function() {
  const searchInput = document.getElementById('merchantSearchInput');
  const sectorSelect = document.getElementById('merchantSectorSelect');
  const statusSelect = document.getElementById('merchantStatusSelect');
  const muniSelect = document.getElementById('territorialMunicipalitySelect');

  const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
  const sec = (sectorSelect ? sectorSelect.value : '').trim().toLowerCase();
  const st = (statusSelect ? statusSelect.value : '').trim();
  const muni = (muniSelect ? muniSelect.value : '').trim().toLowerCase();

  const cards = document.querySelectorAll('.merchant-card-item');
  let visibleCount = 0;

  cards.forEach(card => {
    const company = (card.dataset.company || '').toLowerCase();
    const name = (card.dataset.name || '').toLowerCase();
    const email = (card.dataset.email || '').toLowerCase();
    const address = (card.dataset.address || '').toLowerCase();
    const kyc = card.dataset.kyc || '';
    const sector = (card.dataset.sector || '').toLowerCase();

    let matchesText = true;
    if (q !== '') {
      const fullText = company + ' ' + name + ' ' + email + ' ' + address;
      matchesText = fullText.includes(q);
    }

    let matchesKyc = true;
    if (st !== '') {
      matchesKyc = (kyc === st);
    }

    let matchesSector = true;
    if (sec !== '') {
      matchesSector = sector.includes(sec);
    }

    let matchesMuni = true;
    if (muni !== '') {
      matchesMuni = address.includes(muni) || company.includes(muni) || name.includes(muni);
    }

    if (matchesText && matchesKyc && matchesSector && matchesMuni) {
      card.style.display = 'flex';
      visibleCount++;

      if (q !== '') {
        if (company.startsWith(q)) card.style.order = '-300';
        else if (company.includes(q)) card.style.order = '-200';
        else if (name.startsWith(q)) card.style.order = '-150';
        else if (name.includes(q)) card.style.order = '-100';
        else card.style.order = '-50';
      } else {
        card.style.order = '0';
      }
    } else {
      card.style.display = 'none';
    }
  });

  const emptyCard = document.getElementById('merchantEmptyStateCard');
  const querySpan = document.getElementById('merchantEmptySearchQuery');

  if (emptyCard) {
    if (visibleCount === 0 && (q !== '' || sec !== '' || st !== '' || muni !== '')) {
      emptyCard.style.display = 'block';
      if (querySpan) querySpan.textContent = q || muni || sec || st;
    } else {
      emptyCard.style.display = 'none';
    }
  }
};

window.resetMerchantFilters = function() {
  const searchInput = document.getElementById('merchantSearchInput');
  const sectorSelect = document.getElementById('merchantSectorSelect');
  const statusSelect = document.getElementById('merchantStatusSelect');

  if (searchInput) searchInput.value = '';
  if (sectorSelect) sectorSelect.value = '';
  if (statusSelect) statusSelect.value = '';

  window.filterMerchantCards();
};

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('merchantSearchInput');
  const sectorSelect = document.getElementById('merchantSectorSelect');
  const statusSelect = document.getElementById('merchantStatusSelect');

  if (searchInput) searchInput.addEventListener('input', window.filterMerchantCards);
  if (sectorSelect) sectorSelect.addEventListener('change', window.filterMerchantCards);
  if (statusSelect) statusSelect.addEventListener('change', window.filterMerchantCards);
});

window.getMerchantData = function(id, company_name, name, email, phone, location, status, productsCount, real_sales) {
  const salesVal = real_sales 
    ? '$ ' + Number(real_sales).toLocaleString('es-CO') + ' COP'
    : (productsCount > 0 ? '$ ' + (productsCount * 180000).toLocaleString('es-CO') + ' COP' : '$ 0 COP');

  return {
    id: id,
    name: company_name || name || 'Comerciante Fusagasugá',
    representative: name || company_name || 'Representante Legal',
    email: email || 'contacto@comercio.com',
    phone: phone || '+57 300 123 4567',
    location: location || 'Fusagasugá, Cundinamarca',
    sector: 'Comercio Local',
    sales: salesVal,
    productsCount: productsCount !== undefined ? productsCount : 0,
    status: status === 'approved' ? 'Verificada' : (status === 'pending' ? 'Pendiente RUT' : 'Verificada'),
    paymentMethods: 'PSE, Nequi, Efectivo',
    conversionRate: '5.4%',
    reason: productsCount > 0 
      ? '¡Catálogo activo con ' + productsCount + ' productos registrados! Se recomienda vincular pasarela digital para maximizar conversión.' 
      : '⚠️ Motivo de bajas ventas: Sin catálogo activo registrado en la plataforma ni pasarela digital configurada.'
  };
};

window.initExecutiveDashboard = function initExecutiveDashboard() {

  return {
    activeTab: 'global',
    selectedSector: 'agropecuario',
    loopTriggered: false,
    loopAlertsCount: 142,
    loopMiPymesOptimized: 34,
    showWorkshopModal: false,
    showProfileModal: false,
    showNotifyModal: false,
    isEditingProfile: false,
    selectedMerchant: null,
    notifyMessageType: 'congratulations',
    customNotifyNotes: '',
    toastMessage: '',
    showToast: false,
    searchFilter: '',
    sectorFilter: '',
    statusFilter: '',
    selectedMunicipality: 'Fusagasugá - Todas las Comunas/Veredas',
    selectedPeriod: 'Último Mes',

    matchesSearchCard(el, search, sector, status) {
      if (!el || !el.dataset) return true;

      const q = (search !== undefined && search !== null ? search : (document.getElementById('merchantSearchInput') ? document.getElementById('merchantSearchInput').value : '')).trim().toLowerCase();
      const st = (status !== undefined && status !== null ? status : (document.getElementById('merchantStatusSelect') ? document.getElementById('merchantStatusSelect').value : '')).trim();
      const sec = (sector !== undefined && sector !== null ? sector : (document.getElementById('merchantSectorSelect') ? document.getElementById('merchantSectorSelect').value : '')).trim().toLowerCase();

      const company = el.dataset.company || '';
      const name = el.dataset.name || '';
      const email = el.dataset.email || '';
      const address = el.dataset.address || '';
      const kyc = el.dataset.kyc || '';
      const businessSector = el.dataset.sector || '';

      if (q !== '') {
        const fullText = (company + ' ' + name + ' ' + email + ' ' + address);
        if (!fullText.includes(q)) {
          return false;
        }
      }

      if (st !== '') {
        if (kyc !== st) {
          return false;
        }
      }

      if (sec !== '') {
        if (businessSector && !businessSector.includes(sec)) {
          return false;
        }
      }

      return true;
    },

    getCardOrderFromEl(el, search) {
      if (!el || !el.dataset) return 0;
      const q = (search !== undefined && search !== null ? search : (document.getElementById('merchantSearchInput') ? document.getElementById('merchantSearchInput').value : '')).trim().toLowerCase();
      if (!q) return 0;

      const company = el.dataset.company || '';
      const name = el.dataset.name || '';
      const email = el.dataset.email || '';
      const address = el.dataset.address || '';

      if (company.startsWith(q)) return -300;
      if (company.includes(q)) return -200;
      if (name.startsWith(q)) return -150;
      if (name.includes(q)) return -100;
      if (email.includes(q) || address.includes(q)) return -50;
      return 0;
    },

    isGridEmpty(search, sector, status) {
      const q = (search !== undefined && search !== null ? search : (document.getElementById('merchantSearchInput') ? document.getElementById('merchantSearchInput').value : '')).trim();
      const sec = (sector !== undefined && sector !== null ? sector : (document.getElementById('merchantSectorSelect') ? document.getElementById('merchantSectorSelect').value : '')).trim();
      const st = (status !== undefined && status !== null ? status : (document.getElementById('merchantStatusSelect') ? document.getElementById('merchantStatusSelect').value : '')).trim();

      if (!q && !sec && !st) return false;

      const cards = document.querySelectorAll('.merchant-card-item');
      if (!cards || cards.length === 0) return false;

      let visibleCount = 0;
      cards.forEach(card => {
        if (this.matchesSearchCard(card, q, sec, st)) {
          visibleCount++;
        }
      });

      return visibleCount === 0;
    },
    updateTerritorialFilters() {
      if (typeof window.updateTerritorialFilters === 'function') {
        window.updateTerritorialFilters();
      }
    },
    
    merchantsPreset: {
      vivero_manantial: {
        name: 'Vivero El Manantial',
        representative: 'Don Carlos Manrique',
        email: 'contacto@viveroelmanantial.com',
        phone: '+57 312 456 7890',
        location: 'Vereda Pekín, Fusagasugá',
        sector: 'Agropecuario & Flores',
        sales: '$ 3.850.000 COP',
        productsCount: 24,
        status: 'Top 1 Seller (Líder Digital)',
        paymentMethods: 'PSE, Nequi, Daviplata, Tarjetas',
        photosHd: true,
        conversionRate: '8.4%',
        reason: '¡Excelente volumen de ventas con 24 productos con fotos HD y pasarela digital activa en Fusagasugá!'
      },
      frutas_sumapaz: {
        name: 'Frutas del Sumapaz',
        representative: 'Dña. Martha Lucía Reyes',
        email: 'ventas@frutasdelsumapaz.co',
        phone: '+57 315 987 6543',
        location: 'Vereda El Novillero, Fusagasugá',
        sector: 'Agropecuario & Frutas',
        sales: '$ 2.400.000 COP',
        productsCount: 16,
        status: 'Top 2 Seller (Líder Digital)',
        paymentMethods: 'PSE & Transferencia Bancaria',
        photosHd: true,
        conversionRate: '6.9%',
        reason: 'Gran crecimiento constante en canastas quincenales fijas y pasarela PSE habilitada.'
      },
      finca_esperanza: {
        name: 'Finca La Esperanza',
        representative: 'José Ignacio Gómez',
        email: 'fincalaesperanza@gmail.com',
        phone: '+57 310 234 5678',
        location: 'Vereda Bermejal, Fusagasugá',
        sector: 'Agropecuario & Café',
        sales: '$ 120.000 COP',
        productsCount: 1,
        status: 'Bottom 1 (Rezagado Digital)',
        paymentMethods: 'Solo Efectivo',
        photosHd: false,
        conversionRate: '0.8%',
        reason: '⚠️ Motivo de bajas ventas: 78% opera solo con efectivo (sin Nequi/PSE), 1 producto sin foto e inactividad de catálogo de 35 días.'
      },
      agroinsumos_tequendama: {
        name: 'Agroinsumos del Tequendama',
        representative: 'Hernán Roberto Silva',
        email: 'tequendama.agro@hotmail.com',
        phone: '+57 320 876 5432',
        location: 'Comuna 1 - Centro, Fusagasugá',
        sector: 'Comercio Insumos',
        sales: '$ 50.000 COP',
        productsCount: 0,
        status: 'Bottom 2 (Rezagado Digital)',
        paymentMethods: 'Sin vincular',
        photosHd: false,
        conversionRate: '0.2%',
        reason: '⚠️ Motivo de bajas ventas: Sin catálogo activo registrado en la plataforma ni pasarela digital configurada.'
      }
    },

    get currentWorkshopInfo() {
      const workshops = {
        agropecuario: {
          sectorName: 'Sector Agropecuario',
          program: 'Alfabetización Digital & Acompañamiento a Pasarelas Rurales',
          destinatarios: '70 fincas y MiPymes del sector Agropecuario registradas en Fusagasugá.',
          temas: 'Habilitación de Nequi/Daviplata, fotografía HD de productos agrícolas y logística colectiva.',
          confirmMsg: '¿Estás seguro de enviar la convocatoria del Taller de Digitalización Rural a 70 comerciantes del sector Agropecuario?',
          toastMsg: '📢 Capacitación convocada: Se enviaron invitaciones vía correo y WhatsApp a 70 comerciantes del sector Agropecuario para el taller de Digitalización Rural.'
        },
        confecciones: {
          sectorName: 'Confecciones & Textil',
          program: 'Tallaje Digital, Catálogo Interactivo & Optimización de Despachos',
          destinatarios: '45 talleres y MiPymes del sector Confecciones & Textil en Fusagasugá.',
          temas: 'Implementación de guía interactiva de medidas, fotografía de moda sin modelo y reducción de tiempos de despacho.',
          confirmMsg: '¿Estás seguro de enviar la convocatoria del Taller de Tallaje y Catálogo Digital a 45 comerciantes del sector Confecciones & Textil?',
          toastMsg: '📢 Capacitación convocada: Se enviaron invitaciones vía correo y WhatsApp a 45 comerciantes del sector Confecciones & Textil para el taller de Tallaje y Catálogo Digital.'
        },
        comercio: {
          sectorName: 'Comercio Minorista',
          program: 'Digitalización Comercial, Inventario Omnicanal & Venta en Combos',
          destinatarios: '85 tiendas y MiPymes de Comercio Minorista registradas en Fusagasugá.',
          temas: 'Sincronización de inventario en tiempo real, creación de paquetes/combos promocionales y canales de atención rápida.',
          confirmMsg: '¿Estás seguro de enviar la convocatoria del Taller de Comercio Minorista & Omnicanalidad a 85 comerciantes del sector?',
          toastMsg: '📢 Capacitación convocada: Se enviaron invitaciones vía correo y WhatsApp a 85 comerciantes del sector Comercio Minorista para el taller de Omnicanalidad.'
        },
        gastronomia: {
          sectorName: 'Gastronomía',
          program: 'Menús & Cartas Digitales QR, Logística Térmica y Pagos al Instante',
          destinatarios: '60 restaurantes, cafeterías y negocios gastronómicos de Fusagasugá.',
          temas: 'Implementación de cartas interactivas con pedidos directos a cocina, integración de pasarelas móviles y empaque térmico para domicilios.',
          confirmMsg: '¿Estás seguro de enviar la convocatoria del Taller de Gastronomía Digital a 60 establecimientos gastronómicos de Fusagasugá?',
          toastMsg: '📢 Capacitación convocada: Se enviaron invitaciones vía correo y WhatsApp a 60 establecimientos del sector Gastronomía para el taller de Menús Digitales y Logística.'
        },
        tecnologia: {
          sectorName: 'Tecnología',
          program: 'E-commerce B2B, Cotizadores Digitales & Soporte Técnico Remoto',
          destinatarios: '30 MiPymes de servicios tecnológicos, insumos y dispositivos en Fusagasugá.',
          temas: 'Plataformas de cotización automática en línea, cobros recurrentes por pasarelas digitales y gestión de garantías en web.',
          confirmMsg: '¿Estás seguro de enviar la convocatoria del Taller de Comercio B2B y Servicios Tecnológicos a 30 empresas del sector Tecnología?',
          toastMsg: '📢 Capacitación convocada: Se enviaron invitaciones vía correo y WhatsApp a 30 empresas del sector Tecnología para el taller de E-commerce B2B y Cotizadores Digitales.'
        }
      };
      return workshops[this.selectedSector] || workshops.agropecuario;
    },

    triggerLoop() {
      if (confirm('¿Deseas disparar el Ciclo Algorítmico de Recomendaciones para las MiPymes del sector seleccionado?')) {
        this.loopTriggered = true;
        this.loopAlertsCount += 16;
        this.loopMiPymesOptimized += 5;
        this.toastMessage = '🚀 Ciclo Algorítmico Disparado: 16 nuevas alertas de recomendación enviadas a MiPymes del sector ' + this.currentWorkshopInfo.sectorName + '.';
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 6000);
      }
    },

    sendWorkshopInvite() {
      const info = this.currentWorkshopInfo;
      if (confirm(info.confirmMsg)) {
        this.toastMessage = info.toastMsg;
        this.showToast = true;
        this.showWorkshopModal = false;
        if (typeof window.closeWorkshopModalFallback === 'function') window.closeWorkshopModalFallback();
        setTimeout(() => { this.showToast = false; }, 6000);
      }
    },

    
    openWorkshopModal() {
      this.showWorkshopModal = true;
      if (typeof window.openWorkshopModalFallback === 'function') window.openWorkshopModalFallback();
    },
    closeWorkshopModal() {
      this.showWorkshopModal = false;
      if (typeof window.closeWorkshopModalFallback === 'function') window.closeWorkshopModalFallback();
    },
    openProfileModal() {
      this.showProfileModal = true;
        if (typeof window.openProfileModalFallback === "function") window.openProfileModalFallback();
      if (typeof window.openProfileModalFallback === 'function') window.openProfileModalFallback();
    },
    closeProfileModal() {
      this.showProfileModal = false;
      if (typeof window.closeProfileModalFallback === 'function') window.closeProfileModalFallback();
    },
    openNotifyModal() {
      this.showNotifyModal = true;
        if (typeof window.openNotifyModalFallback === "function") window.openNotifyModalFallback();
      if (typeof window.openNotifyModalFallback === 'function') window.openNotifyModalFallback();
    },
    closeNotifyModal() {
      this.showNotifyModal = false;
      if (typeof window.closeNotifyModalFallback === 'function') window.closeNotifyModalFallback();
    },

    openMerchantProfileByKey(key) {
      if (this.merchantsPreset[key]) {
        this.selectedMerchant = Object.assign({}, this.merchantsPreset[key]);
        this.isEditingProfile = false;
        this.showProfileModal = true;
        if (typeof window.openProfileModalFallback === "function") window.openProfileModalFallback(this.selectedMerchant);
      }
    },

    openMerchantProfileObj(obj) {
      this.selectedMerchant = obj ? Object.assign({}, obj) : null;
      this.isEditingProfile = false;
      this.showProfileModal = true;
      if (typeof window.openProfileModalFallback === "function") window.openProfileModalFallback(this.selectedMerchant);
    },

    openWhatsAppChat() {
      const m = this.selectedMerchant || window.currentSelectedMerchant;
      if (!m || !m.phone) {
        alert('No hay número de WhatsApp registrado para este comerciante.');
        return;
      }
      const cleanPhone = String(m.phone).replace(/[^0-9]/g, '');
      const targetPhone = cleanPhone.length === 10 ? '57' + cleanPhone : cleanPhone;
      const message = encodeURIComponent('Hola ' + (m.representative || m.name) + ', te contactamos desde la Secretaría de Desarrollo Económico de Fusagasugá (FusaShop).');
      window.open('https://wa.me/' + targetPhone + '?text=' + message, '_blank');
    },

    openEmailClient() {
      const m = this.selectedMerchant || window.currentSelectedMerchant;
      if (!m || !m.email) {
        alert('No hay correo registrado para este comerciante.');
        return;
      }
      const subject = encodeURIComponent('Notificación Oficial FusaShop - Secretaría de Desarrollo Económico');
      const body = encodeURIComponent('Estimado(a) ' + (m.representative || m.name) + ',\n\nLe escribimos en relación a su negocio ' + m.name + ' en FusaShop.\n\nCordial saludo,\nSecretaría de Desarrollo Económico de Fusagasugá.');
      window.location.href = 'mailto:' + m.email + '?subject=' + subject + '&body=' + body;
    },

    saveProfileChanges() {
      this.isEditingProfile = false;
      this.toastMessage = '✅ Información de "' + (this.selectedMerchant ? this.selectedMerchant.name : 'Comerciante') + '" actualizada correctamente.';
      this.showToast = true;
      if (typeof window.openProfileModalFallback === "function") window.openProfileModalFallback(this.selectedMerchant);
      setTimeout(() => { this.showToast = false; }, 5000);
    },

    openMerchantNotifyByKey(key, type) {
      if (this.merchantsPreset[key]) {
        this.selectedMerchant = this.merchantsPreset[key];
        this.notifyMessageType = type;
        this.updateDefaultMessage();
        this.showNotifyModal = true;
        if (typeof window.openNotifyModalFallback === "function") window.openNotifyModalFallback();
      }
    },

    openMerchantNotifyObj(obj, type) {
      this.selectedMerchant = obj;
      this.notifyMessageType = type;
      this.updateDefaultMessage();
      this.showNotifyModal = true;
        if (typeof window.openNotifyModalFallback === "function") window.openNotifyModalFallback();
    },

    updateDefaultMessage() {
      if (!this.selectedMerchant) return;
      const name = this.selectedMerchant.name || this.selectedMerchant.company_name || "Comerciante";
      const company = this.selectedMerchant.company_name || name;
      const sales = this.selectedMerchant.sales || "$0 COP";
      const reason = this.selectedMerchant.reason || "Se identificó oportunidad para optimizar la conversión digital en la plataforma.";

      if (this.notifyMessageType === "congratulations") {
        this.customNotifyNotes = "¡Felicitaciones a " + company + "! Tu negocio destaca entre los líderes digitales de Fusagasugá en FusaShop por tu excelente nivel de ventas (" + sales + ") y catálogo de productos actualizado.";
      } else {
        this.customNotifyNotes = "Hola " + name + ", según el análisis algorítmico y de Machine Learning de FusaShop y la Secretaría de Desarrollo Económico de Fusagasugá, tu tienda presenta las siguientes observaciones de rendimiento:\n\n" +
          "• Diagnóstico Algorítmico: " + reason + "\n\n" +
          "• Pasos Recomendados para Incrementar tus Ventas:\n" +
          "  1. Vincular pasarelas digitales de pago (Nequi, Daviplata o PSE).\n" +
          "  2. Publicar fotografías de alta calidad de tu catálogo.\n" +
          "  3. Participar en los talleres de acompañamiento técnico en Fusagasugá.";
      }
    },

    confirmAndSendNotification() {
      if (!this.selectedMerchant) return;
      const typeText = this.notifyMessageType === 'congratulations' ? 'felicitaciones por altas ventas' : 'diagnóstico de bajas ventas';
      if (confirm('¿Estás seguro de enviar esta notificación de ' + typeText + ' a "' + this.selectedMerchant.name + '"?')) {
        this.sendNotification();
      }
    },

    sendNotification() {
      if (!this.selectedMerchant) return;
      this.showNotifyModal = false;
      this.loopAlertsCount += 1;
      if (this.notifyMessageType === 'congratulations') {
        this.toastMessage = '🎉 Mensaje de felicitaciones y reconocimiento digital enviado exitosamente a ' + this.selectedMerchant.name + '.';
      } else {
        this.toastMessage = '⚠️ Notificación de diagnóstico causal enviada a ' + this.selectedMerchant.name + ' detallando el motivo de sus bajas ventas y pasos para optimizar su pasarela.';
      }
      this.showToast = true;
      setTimeout(() => { this.showToast = false; }, 6500);
    }
  };
}

if (typeof Alpine !== 'undefined') {
  Alpine.data('initExecutiveDashboard', initExecutiveDashboard);
} else {
  document.addEventListener('alpine:init', () => {
    Alpine.data('initExecutiveDashboard', initExecutiveDashboard);
  });
}

document.addEventListener('DOMContentLoaded', function () {
  const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
  const salesData = @json($monthlySales);
  const companySalesData = @json($salesByCompany);

  // 1. Monthly Sales Chart
  const salesChartEl = document.getElementById('salesChart');
  if (salesChartEl) {
    const salesLabels = Array.from({length:12},(_,i)=>months[i]);
    const salesValues = Array(12).fill(0);
    if (salesData && salesData.length > 0) {
      salesData.forEach(d => { salesValues[d.month-1] = parseFloat(d.total); });
    } else {
      [8500000, 9200000, 10400000, 11200000, 12800000, 13500000, 14200000, 15100000, 16800000, 12100000, 11500000, 138950000/12].forEach((v, i) => { salesValues[i] = v; });
    }

    new Chart(salesChartEl, {
      type: 'line',
      data: {
        labels: salesLabels,
        datasets: [{
          label: 'Ventas Totales (COP)',
          data: salesValues,
          fill: true,
          backgroundColor: 'rgba(0,108,71,0.15)',
          borderColor: '#006c47',
          tension: 0.4,
          pointBackgroundColor: '#006c47',
          borderWidth: 3,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$'+v.toLocaleString('es-CO') } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  // 2. Company Sales Chart
  const companySalesChartEl = document.getElementById('companySalesChart');
  if (companySalesChartEl && companySalesData && companySalesData.length > 0) {
    new Chart(companySalesChartEl, {
      type: 'bar',
      data: {
        labels: companySalesData.map(s => s.company_name),
        datasets: [{
          label: 'Ventas Totales (COP)',
          data: companySalesData.map(s => s.total_sales),
          backgroundColor: 'rgba(0,182,122,0.8)',
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$'+v.toLocaleString('es-CO') } },
          y: { grid: { display: false } }
        }
      }
    });
  }
});
</script>
@endpush
