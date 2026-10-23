@extends('layouts.app')
@section('title', 'Mis Reservas | FusaShop')
@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6 py-4 sm:py-8 space-y-4 sm:space-y-6">
  <header>
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900">Mis solicitudes de reserva</h1>
    <p class="text-xs sm:text-sm text-slate-600 mt-1">Las reservas no generan un cobro en línea. Espera la confirmación del comercio.</p>
  </header>
  @if(session('success'))<div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-900 text-sm font-semibold">{{ session('success') }}</div>@endif
  @if($reservations->isEmpty())
    <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
      <span class="material-symbols-outlined text-5xl text-emerald-600">event_available</span>
      <h2 class="mt-3 font-bold text-xl">Todavía no tienes reservas</h2>
      <a class="inline-block mt-4 rounded-xl bg-emerald-700 px-5 py-3 text-white font-bold" href="{{ route('consumer.catalog') }}">Explorar servicios</a>
    </div>
  @else
    <div class="space-y-4">
      @foreach($reservations as $reservation)
        @php($status = ['pending' => ['Pendiente de confirmación','bg-amber-100 text-amber-800'], 'confirmed' => ['Confirmada','bg-emerald-100 text-emerald-800'], 'rejected' => ['No disponible','bg-rose-100 text-rose-800'], 'cancelled' => ['Cancelada','bg-slate-100 text-slate-700'], 'completed' => ['Completada','bg-blue-100 text-blue-800']][$reservation->status] ?? ['Pendiente','bg-slate-100 text-slate-700'])
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
              <p class="text-xs font-bold text-slate-500">{{ $reservation->reference }} · {{ $reservation->created_at->format('d/m/Y H:i') }}</p>
              <h2 class="mt-1 text-lg font-extrabold text-slate-900">{{ $reservation->product->name }}</h2>
              <p class="text-sm text-slate-600">{{ $reservation->product->merchant->companyProfile->company_name ?? $reservation->product->merchant->name ?? 'Comercio' }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $status[1] }}">{{ $status[0] }}</span>
          </div>
          <div class="mt-4 grid gap-2 sm:grid-cols-3 text-sm text-slate-700">
            <p><strong>Cantidad:</strong> {{ $reservation->quantity }}</p>
            @if($reservation->quoted_price !== null)<p><strong>Precio estimado por opción:</strong> ${{ number_format($reservation->quoted_price, 0, ',', '.') }} COP <span class="text-xs text-slate-500">(no es un cobro; el comercio confirma el valor final)</span></p>@endif
            @if($reservation->preferred_date)<p><strong>Fecha solicitada:</strong> {{ $reservation->preferred_date->format('d/m/Y') }}</p>@endif
            @if($reservation->preferred_time)<p><strong>Hora:</strong> {{ substr((string) $reservation->preferred_time, 0, 5) }}</p>@endif
            @if($reservation->party_size)<p><strong>Personas:</strong> {{ $reservation->party_size }}</p>@endif
          </div>
          @if($reservation->selected_options)<p class="mt-2 text-sm text-slate-600"><strong>Opciones:</strong> {{ collect($reservation->selected_options)->map(fn($v,$k) => $k.': '.$v)->implode(' · ') }}</p>@endif
          @if($reservation->customer_notes)<p class="mt-2 rounded-lg bg-slate-50 p-3 text-sm text-slate-700"><strong>Tu mensaje:</strong> {{ $reservation->customer_notes }}</p>@endif
          @if($reservation->merchant_notes)<p class="mt-2 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-900"><strong>Respuesta del comercio:</strong> {{ $reservation->merchant_notes }}</p>@endif
          @if(in_array($reservation->status, ['pending','confirmed'], true))
            <form method="POST" action="{{ route('consumer.reservations.cancel', $reservation) }}" class="mt-4" onsubmit="return confirm('¿Cancelar esta solicitud de reserva?')">
              @csrf @method('PATCH')
              <button class="rounded-lg border border-rose-200 px-4 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Cancelar solicitud</button>
            </form>
          @endif
        </article>
      @endforeach
    </div>
    <div>{{ $reservations->links() }}</div>
  @endif
</div>
@endsection
