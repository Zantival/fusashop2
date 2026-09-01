@extends('layouts.app')
@section('title','Dashboard Analista')
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
  <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <h1 class="text-3xl font-['Manrope'] font-extrabold text-on-background">Panel de Analítica</h1>
      <p class="text-on-surface-variant mt-1">Gestión administrativa y rendimiento de FusaShop</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('analyst.users') }}" class="px-4 py-2 bg-surface-container border border-surface-container-highest text-on-surface font-bold rounded-xl hover:bg-surface-container-high transition-all flex items-center gap-2 text-sm shadow-sm">
        <span class="material-symbols-outlined text-sm">group</span> Gestionar Usuarios
      </a>
      <a href="{{ route('analyst.banners') }}" class="px-4 py-2 bg-surface-container border border-surface-container-highest text-on-surface font-bold rounded-xl hover:bg-surface-container-high transition-all flex items-center gap-2 text-sm shadow-sm">
        <span class="material-symbols-outlined text-sm">image</span> Gestionar Banners
      </a>
      <a href="{{ route('analyst.orders') }}" class="px-4 py-2 bg-surface-container border border-surface-container-highest text-on-surface font-bold rounded-xl hover:bg-surface-container-high transition-all flex items-center gap-2 text-sm shadow-sm">
        <span class="material-symbols-outlined text-sm">shopping_bag</span> Ver Todos los Pedidos
      </a>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    @php $kpis=[
      ['payments','Ventas Totales','$'.number_format($totalSales,0,',','.'),'text-[#006c47]','bg-[#6efcb9]/30','vs mes anterior'],
      ['receipt_long','Total Pedidos',$totalOrders,'text-[#7c5800]','bg-[#ffdea8]','pedidos procesados'],
      ['group','Consumidores',$totalConsumers,'text-blue-600','bg-blue-50','clientes registrados'],
      ['storefront','Comerciantes',$totalMerchants,'text-[#a6383b]','bg-[#fa7777]/20','vendedores activos'],
    ]; @endphp
    @foreach($kpis as [$icon,$label,$value,$color,$bg,$sub])
    <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-[0_12px_32px_rgba(27,28,28,.06)]">
      <div class="flex items-start justify-between mb-4">
        <div class="w-11 h-11 {{ $bg }} rounded-xl flex items-center justify-center">
          <span class="material-symbols-outlined {{ $color }}">{{ $icon }}</span>
        </div>
        <span class="material-symbols-outlined text-[#6efcb9] text-sm">trending_up</span>
      </div>
      <p class="text-3xl font-['Manrope'] font-extrabold text-on-background">{{ $value }}</p>
      <p class="text-on-surface font-semibold text-sm mt-1">{{ $label }}</p>
      <p class="text-on-surface-variant text-xs mt-0.5">{{ $sub }}</p>
    </div>
    @endforeach
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Monthly Sales Chart -->
    <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] flex flex-col">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
           <span class="material-symbols-outlined text-primary text-2xl">bar_chart</span>
           <h2 class="font-['Manrope'] font-bold text-on-background text-xl">Ventas Totales del Año {{ date('Y') }}</h2>
        </div>
        <div class="flex items-center gap-3">
           <a href="{{ route('analyst.sales-report.print') }}" target="_blank" class="px-4 py-2 bg-primary-gradient text-white text-xs font-bold rounded-xl shadow hover:opacity-90 transition-all flex items-center gap-1">
             <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> PDF Mensual
           </a>
        </div>
      </div>
      <div class="bg-surface-container-low rounded-xl p-4 flex-1">
        <canvas id="salesChart" style="max-height: 250px;"></canvas>
      </div>
    </div>

    <!-- Company Sales Chart -->
    <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] flex flex-col">
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

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Top Products -->
    <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)]">
      <h2 class="font-['Manrope'] font-bold text-on-background text-xl mb-6">Productos Más Vendidos</h2>
      <div class="space-y-4">
        @foreach($topProducts as $i => $tp)
        <div class="flex items-center gap-4">
          <span class="w-8 h-8 bg-primary-gradient rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">{{ $i+1 }}</span>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-on-background text-sm truncate">{{ e($tp->product?->name ?? 'Producto eliminado') }}</p>
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
    <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)]">
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

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- KYC Pendientes -->
    <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)]">
      <h2 class="font-['Manrope'] font-bold text-on-background text-xl mb-6">Comerciantes KYC Pendientes</h2>
      @if($pendingKyc->isEmpty())
        <p class="text-sm text-on-surface-variant">No hay comerciantes en proceso de validación.</p>
      @else
        <div class="space-y-4">
          @foreach($pendingKyc as $kyc)
          <div class="p-5 bg-surface-container-low border border-surface-container-high rounded-2xl shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
              <div>
                <p class="font-bold text-on-background text-lg flex items-center gap-2">
                  {{ e($kyc->company_name) }} 
                  <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-black rounded-full uppercase tracking-tighter">Pendiente</span>
                </p>
                <p class="text-xs text-on-surface-variant">Representante: {{ e($kyc->user->name) }} · {{ e($kyc->user->email) }}</p>
              </div>
              <div class="flex gap-2">
                <a href="{{ route('analyst.users.rut', $kyc->merchant_id) }}" target="_blank" class="px-3 py-2 bg-surface-container-high text-on-surface text-xs font-bold rounded-xl hover:bg-surface-container-highest flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px]">description</span> Ver RUT
                </a>
                <a href="{{ route('analyst.users.edit', $kyc->merchant_id) }}" class="px-3 py-2 bg-surface-container-high text-on-surface text-xs font-bold rounded-xl hover:bg-surface-container-highest flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px]">visibility</span> Ver Todo
                </a>
              </div>
            </div>

            <form method="POST" action="{{ route('analyst.users.kyc', $kyc->merchant_id) }}" class="space-y-3">
              @csrf
              <textarea name="kyc_notes" placeholder="Motivo de rechazo (opcional si apruebas)" class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-none" rows="2"></textarea>
              
              <div class="flex gap-2">
                <button type="submit" name="kyc_status" value="approved" class="flex-1 py-2 bg-[#00b67a] text-white text-xs font-bold rounded-xl hover:opacity-90 shadow-md shadow-[#00b67a]/20 transition-all flex items-center justify-center gap-1">
                  <span class="material-symbols-outlined text-[16px]">check_circle</span> Aprobar Cuenta
                </button>
                <button type="submit" name="kyc_status" value="rejected" class="flex-1 py-2 bg-error text-white text-xs font-bold rounded-xl hover:opacity-90 shadow-md shadow-error/20 transition-all flex items-center justify-center gap-1">
                  <span class="material-symbols-outlined text-[16px]">cancel</span> Rechazar con Comentario
                </button>
              </div>
            </form>
          </div>
          @endforeach
        </div>
      @endif
    </div>

    <!-- Solicitudes de Banners Pendientes -->
    <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)]">
      <div class="flex items-center justify-between mb-6">
        <h2 class="font-['Manrope'] font-bold text-on-background text-xl">Solicitudes de Banners</h2>
        <a href="{{ route('analyst.banners') }}" class="text-primary text-sm font-semibold hover:underline">Gestionar</a>
      </div>
      @if($pendingBanners->isEmpty())
        <p class="text-sm text-on-surface-variant">No hay solicitudes de banners pendientes.</p>
      @else
        <div class="space-y-4">
          @foreach($pendingBanners as $pb)
          <div class="flex items-center justify-between p-4 bg-primary/5 border border-primary/20 rounded-xl">
            <div class="flex items-center gap-3">
              <div class="w-12 h-8 bg-surface-container rounded overflow-hidden">
                <img src="{{ asset('storage/'.$pb->image_path) }}" class="w-full h-full object-cover">
              </div>
              <div>
                <p class="font-bold text-on-background text-sm">{{ e($pb->user->companyProfile->company_name ?? $pb->user->name) }}</p>
                <p class="text-[10px] text-on-surface-variant">{{ $pb->created_at->diffForHumans() }}</p>
              </div>
            </div>
            <a href="{{ route('analyst.banner-requests.show', $pb->id) }}" class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:opacity-90">Revisar</a>
          </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

    <!-- ML Reviews Report -->
    <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-primary/20">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-12 h-12 bg-[#6efcb9]/30 flex items-center justify-center rounded-xl">
          <span class="material-symbols-outlined text-3xl text-[#006c47]">rate_review</span>
        </div>
        <div>
          <h2 class="font-['Manrope'] font-bold text-on-background text-xl">Análisis de Reseñas</h2>
          <span class="text-[10px] uppercase font-bold text-[#006c47] tracking-wider px-2 bg-[#6efcb9]/30 rounded-full">ML REPORT</span>
        </div>
      </div>
      <p class="text-sm text-on-surface-variant mb-4">Reporte de sentimiento, rating y clustering por comerciante basado en las valoraciones de los compradores.</p>
      <a href="{{ route('analyst.reviews-report') }}" class="w-full flex items-center justify-center gap-2 py-3 bg-primary text-white font-bold rounded-xl shadow-md hover:opacity-90 transition-all text-sm">
        <span class="material-symbols-outlined text-[18px]">insights</span> Ver Reporte ML de Reseñas
      </a>
    </div>

    <!-- ML Integration UI -->
    <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-[#a6383b]/20">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-12 h-12 bg-tertiary-container/30 text-tertiary flex items-center justify-center rounded-xl">
          <span class="material-symbols-outlined text-3xl text-tertiary">psychology</span>
        </div>
        <div>
           <h2 class="font-['Manrope'] font-bold text-on-background text-xl">Motor de Inteligencia Artificial</h2>
           <span class="text-[10px] uppercase font-bold text-tertiary tracking-wider px-2 bg-tertiary-container/50 rounded-full">BETA ENGINE</span>
        </div>
      </div>
      <p class="text-sm text-on-surface-variant mb-6 leading-relaxed">Ejecuta el script puente compilado hacia el motor de Machine Learning en Python. Se evalúan y estructuran recomendaciones de negocios en base al Social Proof Rating y Volumen de Ventas en la red distribuida.</p>
      <form method="POST" action="{{ route('analyst.run-ml') }}">
        @csrf
        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-tertiary text-white font-bold rounded-xl shadow-md hover:opacity-90 active:scale-95 transition-all text-sm">
          <span class="material-symbols-outlined text-[18px]">smart_toy</span> Alimentar Data & Ejecutar Script ML
        </button>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const salesData = @json($monthlySales);
const companySalesData = @json($salesByCompany);

// Monthly Sales Chart
const salesLabels = Array.from({length:12},(_,i)=>months[i]);
const salesValues = Array(12).fill(0);
salesData.forEach(d => { salesValues[d.month-1] = parseFloat(d.total); });

new Chart(document.getElementById('salesChart'), {
  type: 'line',
  data: {
    labels: salesLabels,
    datasets: [{
      label: 'Ventas Totales (COP)',
      data: salesValues,
      fill: true,
      backgroundColor: 'rgba(0,108,71,0.2)',
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

// Company Sales Chart
if(companySalesData.length > 0) {
  new Chart(document.getElementById('companySalesChart'), {
    type: 'bar',
    data: {
      labels: companySalesData.map(s=>s.company_name),
      datasets: [{
        label: 'Ventas Totales (COP)',
        data: companySalesData.map(s=>s.total_sales),
        backgroundColor: 'rgba(0,182,122,0.8)', // Primary-container
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y', // Grafica de barras horizontales
      plugins: { 
        legend: { display: false } 
      },
      scales: {
        x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$'+v.toLocaleString('es-CO') } },
        y: { grid: { display: false } }
      }
    }
  });
}
</script>
@endpush
@endsection
