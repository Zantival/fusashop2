@extends('layouts.app')
@section('title','Mis Pedidos')
@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
  <h1 class="text-3xl font-['Manrope'] font-bold text-on-background mb-8">Mis Pedidos</h1>
  @if($orders->isEmpty())
    <div class="text-center py-20 bg-surface-container-lowest rounded-2xl shadow-card">
      <span class="material-symbols-outlined text-on-surface-variant text-8xl mb-4 block">receipt_long</span>
      <p class="text-on-surface-variant text-lg">Aún no tienes pedidos.</p>
      <a href="{{ route('consumer.catalog') }}" class="text-primary font-semibold hover:underline mt-2 inline-block">Empezar a comprar</a>
    </div>
  @else
    <div class="space-y-6">
      @foreach($orders as $order)
      <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)]">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
          <div>
            <span class="text-xs text-on-surface-variant uppercase tracking-wider">Pedido #{{ $order->id }}</span>
            <p class="font-['Manrope'] font-bold text-on-background">{{ $order->created_at->format('d M Y') }}</p>
          </div>
          <div class="flex items-center gap-3">
            @php $statusColors=['pending'=>'bg-[#ffdea8] text-[#7c5800]','processing'=>'bg-[#6efcb9]/40 text-[#003f28]','shipped'=>'bg-blue-100 text-blue-700','delivered'=>'bg-[#6efcb9]/60 text-[#006c47]','cancelled'=>'bg-[#ffdad6] text-[#ba1a1a]']; @endphp
            <div class="flex flex-col items-end mr-2 border-r border-outline-variant/30 pr-4">
              <span class="text-[10px] uppercase font-bold text-on-surface-variant tracking-wider mb-1">Estado del Envío</span>
              <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm {{ $statusColors[$order->status] ?? 'bg-surface-container text-on-surface' }}">
                {{ ['pending'=>'Pendiente', 'processing'=>'Procesando', 'shipped'=>'Enviado', 'delivered'=>'Entregado', 'cancelled'=>'Cancelado'][$order->status] ?? ucfirst($order->status) }}
              </span>
            </div>
            <div class="flex flex-col items-end">
              @if($order->discount > 0)
                <span class="text-[11px] font-bold text-[#ba1a1a] bg-[#ffdad6] px-2 py-0.5 rounded-md mb-1">- Descuento Puntos: ${{ number_format($order->discount, 0, ',', '.') }}</span>
              @endif
              <span class="font-bold text-primary text-lg ml-1">${{ number_format($order->total,0,',','.') }}</span>
            </div>
          </div>
        </div>
        <div class="mt-5 pt-4 border-t border-outline-variant/30 flex justify-between items-center">
          <div class="flex gap-3 flex-wrap">
            @foreach($order->items as $item)
            <div class="flex items-center gap-2 bg-surface-container-low rounded-xl px-3 py-2">
              <span class="text-sm text-on-surface-variant font-medium">{{ e($item->product->name ?? 'Producto eliminado') }}</span>
              <span class="text-xs bg-surface-container rounded-full px-2 py-0.5 font-bold">x{{ $item->quantity }}</span>
            </div>
            @endforeach
          </div>
          <a href="{{ route('consumer.orders.receipt', $order->id) }}" class="flex items-center gap-2 px-4 py-2 bg-surface-container hover:bg-surface-container-highest text-on-surface-variant rounded-xl text-xs font-bold transition-all shrink-0">
            <span class="material-symbols-outlined text-[18px]">receipt</span>
            Ver Recibo
          </a>
        </div>
      </div>
      @endforeach
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
  @endif
</div>
@endsection
