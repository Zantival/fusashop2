@extends('layouts.app')
@section('title','Análisis ML de Reseñas')
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
  <div class="mb-8">
    <h1 class="text-3xl font-['Manrope'] font-extrabold text-on-background flex items-center gap-2">
      <span class="material-symbols-outlined text-tertiary text-3xl">psychology</span> Análisis Inteligente de Clientes
    </h1>
    <p class="text-on-surface-variant mt-1">Descubre qué opinan los clientes de cada tienda y evalúa la calidad de los comerciantes de manera fácil.</p>
  </div>

  @if(empty($report))
    <div class="text-center py-20 bg-surface-container-lowest rounded-2xl shadow-card">
      <span class="material-symbols-outlined text-6xl text-on-surface-variant/40 mb-3 block">bar_chart</span>
      <p class="text-on-surface-variant font-semibold">No hay datos de reseñas suficientes para generar el reporte.</p>
    </div>
  @else

  <!-- Summary cards -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    @php
      $elite    = collect($report)->where('tier','Excelente')->count();
      $globalAvg= count($report) ? round(collect($report)->avg('avg_rating'),2) : 0;
      $totalReviews = collect($report)->sum('total_reviews');
    @endphp
    @foreach([
      ['storefront','Tiendas Analizadas',count($report),'text-tertiary','bg-tertiary-container/30'],
      ['rate_review','Total de Reseñas',$totalReviews,'text-primary','bg-primary-container/30'],
      ['star','Calidad Global',$globalAvg.'★','text-[#7c5800]','bg-[#ffdea8]/60'],
      ['verified','Tiendas Excelentes',$elite,'text-[#006c47]','bg-[#6efcb9]/30'],
    ] as [$icon,$label,$val,$col,$bg])
    <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-card">
      <div class="w-11 h-11 {{ $bg }} rounded-xl flex items-center justify-center mb-3">
        <span class="material-symbols-outlined {{ $col }}">{{ $icon }}</span>
      </div>
      <p class="text-2xl font-['Manrope'] font-extrabold text-on-background">{{ $val }}</p>
      <p class="text-on-surface-variant text-xs mt-1">{{ $label }}</p>
    </div>
    @endforeach
  </div>

  <!-- Visual Breakdown per Company -->
  <h2 class="font-['Manrope'] font-bold text-on-background text-2xl mb-4">Análisis Visual por Empresa</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    @foreach($report as $row)
    <div class="bg-surface-container-lowest rounded-2xl p-5 shadow-card border border-outline-variant/30">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-['Manrope'] font-bold text-on-background truncate flex-1">{{ $row['company_name'] }}</h3>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-surface-container text-on-surface-variant whitespace-nowrap">{{ $row['total_reviews'] }} reseñas</span>
      </div>
      
      <div class="flex items-center gap-2 mb-4">
        <div class="text-3xl font-black text-[#7c5800]">{{ number_format($row['avg_rating'], 1) }}</div>
        <div class="flex text-[#feb700]">
            @for($i=1; $i<=5; $i++)
                <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' {{ $row['avg_rating'] >= $i ? 1 : 0 }},'wght' 400">star</span>
            @endfor
        </div>
      </div>

      <div class="space-y-2.5">
        @foreach([5,4,3,2,1] as $star)
        @php 
            $count = $row['stars'][$star] ?? 0;
            $percent = $row['total_reviews'] > 0 ? ($count / $row['total_reviews']) * 100 : 0;
        @endphp
        <div class="flex items-center gap-3 text-sm">
          <div class="flex items-center justify-end text-on-surface-variant font-medium w-10 gap-0.5">
            {{ $star }} <span class="material-symbols-outlined text-[14px]">star</span>
          </div>
          <div class="flex-1 bg-surface-container-high rounded-full h-2.5 overflow-hidden">
            <div class="bg-[#feb700] h-full rounded-full" style="width: {{ $percent }}%"></div>
          </div>
          <div class="w-8 text-right text-on-surface-variant font-bold">{{ $count }}</div>
        </div>
        @endforeach
      </div>
    </div>
    @endforeach
  </div>

  <!-- Table -->
  <div class="bg-surface-container-lowest rounded-2xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-outline-variant/20">
      <h2 class="font-['Manrope'] font-bold text-on-background text-xl">Reporte Detallado por Comerciante</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-surface-container-low">
          <tr>
            @foreach(['Tienda','Reseñas','Estrellas Promedio','Actitud del Cliente','😊 Positivos','😐 Neutrales','😞 Negativos','Mejor Producto','Peor Producto','Nivel de Calidad','Puntaje General'] as $h)
            <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase whitespace-nowrap">{{ $h }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($report as $row)
          @php
            $tierColors = ['Excelente'=>'bg-[#6efcb9]/40 text-[#006c47]','Aceptable'=>'bg-[#ffdea8]/60 text-[#7c5800]','Peligro'=>'bg-error-container text-error'];
            $sentColors = ['Positivo'=>'text-[#006c47]','Neutral'=>'text-[#7c5800]','Negativo'=>'text-error'];
          @endphp
          <tr class="border-t border-outline-variant/10 hover:bg-surface-container-low/50 transition-colors">
            <td class="px-4 py-3 font-semibold text-on-background">{{ e($row['company_name']) }}</td>
            <td class="px-4 py-3 text-on-surface-variant">{{ $row['total_reviews'] }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                <span class="font-bold text-[#7c5800]">{{ $row['avg_rating'] }}</span>
                <span class="material-symbols-outlined text-[#feb700] text-sm" style="font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 18">star</span>
              </div>
            </td>
            <td class="px-4 py-3 font-semibold {{ $sentColors[$row['sentiment']] ?? '' }}">{{ $row['sentiment'] }}</td>
            <td class="px-4 py-3 text-[#006c47] font-bold">{{ $row['positive'] }}</td>
            <td class="px-4 py-3 text-[#7c5800]">{{ $row['neutral'] }}</td>
            <td class="px-4 py-3 text-error">{{ $row['negative'] }}</td>
            <td class="px-4 py-3 text-xs text-on-surface-variant max-w-[120px] truncate">{{ $row['best_product']['name'] ?? '-' }}</td>
            <td class="px-4 py-3 text-xs text-on-surface-variant max-w-[120px] truncate">{{ $row['worst_product']['name'] ?? '-' }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-bold {{ $tierColors[$row['tier']] ?? 'bg-surface-container text-on-surface-variant' }}">{{ $row['tier'] }}</span>
            </td>
            <td class="px-4 py-3 font-bold text-tertiary">{{ $row['score'] }}%</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif
</div>

@endsection
