@extends('layouts.app')
@section('title', 'Solicitudes de reserva | FusaShop')
@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-6 py-4 sm:py-8 space-y-4 sm:space-y-6">
  <header>
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900">Solicitudes de reserva</h1>
    <p class="text-xs sm:text-sm text-slate-600 mt-1">Las solicitudes no están pagadas. Confirma disponibilidad y coordina el servicio con el cliente.</p>
  </header>
  @if(session('success'))<div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-900 text-sm font-semibold">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-900 text-sm">{{ $errors->first() }}</div>@endif
  @if($reservations->isEmpty())
    <div class="rounded-2xl bg-white p-10 text-center shadow-sm text-slate-600">Aún no recibes solicitudes.</div>
  @else
    <div class="space-y-4">
      @foreach($reservations as $reservation)
        @php($status = ['pending' => ['Pendiente','bg-amber-100 text-amber-800'], 'confirmed' => ['Confirmada','bg-emerald-100 text-emerald-800'], 'rejected' => ['Rechazada','bg-rose-100 text-rose-800'], 'cancelled' => ['Cancelada por el cliente','bg-slate-100 text-slate-700'], 'completed' => ['Completada','bg-blue-100 text-blue-800']][$reservation->status] ?? ['Pendiente','bg-slate-100 text-slate-700'])
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="space-y-2">
              <div class="flex flex-wrap items-center gap-2"><span class="font-mono text-xs font-bold text-slate-500">{{ $reservation->reference }}</span><span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $status[1] }}">{{ $status[0] }}</span></div>
              <h2 class="text-lg font-extrabold text-slate-900">{{ $reservation->product->name }} · {{ $reservation->quantity }} {{ $reservation->quantity === 1 ? 'unidad' : 'unidades' }}</h2>
              <p class="text-sm text-slate-700"><strong>Cliente:</strong> {{ $reservation->customer->name }} · {{ $reservation->customer->email }} @if($reservation->customer->phone)· {{ $reservation->customer->phone }}@endif</p>
              @if($reservation->quoted_price !== null)<p class="text-sm text-slate-700"><strong>Precio estimado al solicitar:</strong> ${{ number_format($reservation->quoted_price, 0, ',', '.') }} COP</p>@endif
              <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-600">
                @if($reservation->preferred_date)<span>Fecha: {{ $reservation->preferred_date->format('d/m/Y') }}</span>@endif
                @if($reservation->preferred_time)<span>Hora: {{ substr((string) $reservation->preferred_time, 0, 5) }}</span>@endif
                @if($reservation->party_size)<span>Personas: {{ $reservation->party_size }}</span>@endif
              </div>
              @if($reservation->selected_options)<p class="text-sm text-slate-600"><strong>Opciones:</strong> {{ collect($reservation->selected_options)->map(fn($v,$k) => $k.': '.$v)->implode(' · ') }}</p>@endif
              @if($reservation->customer_notes)<p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-700"><strong>Mensaje del cliente:</strong> {{ $reservation->customer_notes }}</p>@endif
            </div>
            @if(in_array($reservation->status, ['pending','confirmed'], true))
              <form method="POST" action="{{ route('merchant.reservations.update', $reservation) }}" class="w-full md:max-w-xs space-y-2">
                @csrf @method('PATCH')
                <textarea name="merchant_notes" maxlength="1500" rows="2" placeholder="Respuesta o instrucciones para el cliente" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">{{ old('merchant_notes', $reservation->merchant_notes) }}</textarea>
                <div class="flex flex-wrap gap-2">
                  @if($reservation->status === 'pending')
                    <button name="status" value="confirmed" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white">Confirmar</button>
                    <button name="status" value="rejected" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">No disponible</button>
                  @else
                    <button name="status" value="completed" class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-bold text-white">Marcar completada</button>
                    <button name="status" value="rejected" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">Cancelar reserva</button>
                  @endif
                </div>
              </form>
            @elseif($reservation->merchant_notes)
              <p class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-900"><strong>Tu respuesta:</strong> {{ $reservation->merchant_notes }}</p>
            @endif
          </div>
        </article>
      @endforeach
    </div>
    <div>{{ $reservations->links() }}</div>
  @endif
</div>
@endsection
