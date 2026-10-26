@extends('layouts.app')
@section('title', 'Gestión de Reservas & Servicios | FusaShop')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8 space-y-6">

  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20">
    <div>
      <div class="flex items-center gap-2">
        <h1 class="text-2xl md:text-3xl font-['Manrope'] font-extrabold text-slate-900">Gestión de Reservas & Servicios</h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-[#006c47] border border-emerald-200 uppercase tracking-wider flex items-center gap-1">
          <span class="material-symbols-outlined text-xs">event_available</span> Servicios Activos
        </span>
      </div>
      <p class="text-slate-500 text-xs md:text-sm mt-1">
        Atiende y gestiona las solicitudes de tus clientes para hoteles, hospedajes, spas, restaurantes y tours en Fusagasugá y el Sumapaz.
      </p>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('merchant.products.create') }}" class="px-4 py-2 bg-[#006c47] hover:bg-[#10B981] text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm">add_circle</span> Nuevo Servicio
      </a>
      <a href="{{ route('merchant.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm">dashboard</span> Dashboard
      </a>
    </div>
  </div>

  <!-- STATS STRIP -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl p-4 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex items-center justify-between">
      <div>
        <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Total Recibidas</p>
        <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $counts['all'] ?? 0 }}</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
        <span class="material-symbols-outlined text-xl">receipt_long</span>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex items-center justify-between">
      <div>
        <p class="text-[11px] font-extrabold text-amber-800 uppercase tracking-wider">Pendientes</p>
        <p class="text-2xl font-black text-amber-600 mt-0.5">{{ $counts['pending'] ?? 0 }}</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center {{ ($counts['pending'] ?? 0) > 0 ? 'animate-pulse' : '' }}">
        <span class="material-symbols-outlined text-xl">hourglass_top</span>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex items-center justify-between">
      <div>
        <p class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Confirmadas</p>
        <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ $counts['confirmed'] ?? 0 }}</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-emerald-100 text-[#006c47] flex items-center justify-center">
        <span class="material-symbols-outlined text-xl">check_circle</span>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/15 flex items-center justify-between">
      <div>
        <p class="text-[11px] font-extrabold text-blue-800 uppercase tracking-wider">Completadas</p>
        <p class="text-2xl font-black text-blue-700 mt-0.5">{{ $counts['completed'] ?? 0 }}</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
        <span class="material-symbols-outlined text-xl">task_alt</span>
      </div>
    </div>
  </div>

  <!-- FILTROS DE ESTADO -->
  @php
    $filterTabs = [
      '' => ['label' => 'Todas', 'count' => $counts['all'] ?? 0, 'icon' => 'list_alt', 'activeClass' => 'bg-slate-900 text-white border-slate-900'],
      'pending' => ['label' => 'Pendientes', 'count' => $counts['pending'] ?? 0, 'icon' => 'hourglass_top', 'activeClass' => 'bg-amber-600 text-white border-amber-600'],
      'confirmed' => ['label' => 'Confirmadas', 'count' => $counts['confirmed'] ?? 0, 'icon' => 'check_circle', 'activeClass' => 'bg-emerald-700 text-white border-emerald-700'],
      'completed' => ['label' => 'Completadas', 'count' => $counts['completed'] ?? 0, 'icon' => 'task_alt', 'activeClass' => 'bg-blue-700 text-white border-blue-700'],
      'rejected' => ['label' => 'No disponibles', 'count' => $counts['rejected'] ?? 0, 'icon' => 'cancel', 'activeClass' => 'bg-rose-700 text-white border-rose-700'],
      'cancelled' => ['label' => 'Canceladas cliente', 'count' => $counts['cancelled'] ?? 0, 'icon' => 'event_busy', 'activeClass' => 'bg-slate-700 text-white border-slate-700'],
    ];
  @endphp

  <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
    @foreach($filterTabs as $statusKey => $tabInfo)
      @php $isActive = $currentStatus === $statusKey; @endphp
      <a href="{{ route('merchant.reservations', array_filter(['status' => $statusKey])) }}"
         class="px-3.5 py-2 rounded-xl text-xs font-bold border transition-all flex items-center gap-2 shrink-0 {{ $isActive ? $tabInfo['activeClass'] . ' shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:border-emerald-300 hover:bg-slate-50' }}">
        <span class="material-symbols-outlined text-sm">{{ $tabInfo['icon'] }}</span>
        <span>{{ $tabInfo['label'] }}</span>
        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $isActive ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-700' }}">
          {{ $tabInfo['count'] }}
        </span>
      </a>
    @endforeach
  </div>

  @if(session('success'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-900 text-xs font-bold flex items-center gap-2 shadow-xs">
      <span class="material-symbols-outlined text-emerald-700 text-lg">check_circle</span>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-rose-900 text-xs font-bold flex items-center gap-2 shadow-xs">
      <span class="material-symbols-outlined text-rose-700 text-lg">error</span>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <!-- LISTADO DE RESERVAS -->
  @if($reservations->isEmpty())
    <div class="bg-white rounded-2xl p-12 text-center shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20 space-y-3">
      <div class="w-16 h-16 rounded-full bg-emerald-50 text-[#006c47] flex items-center justify-center mx-auto">
        <span class="material-symbols-outlined text-3xl">event_available</span>
      </div>
      <h3 class="text-base font-extrabold text-slate-900">
        @if($currentStatus)
          No hay reservas con estado "{{ $filterTabs[$currentStatus]['label'] ?? $currentStatus }}"
        @else
          Aún no tienes solicitudes de reserva registradas
        @endif
      </h3>
      <p class="text-xs text-slate-500 max-w-md mx-auto">
        Cuando los usuarios soliciten reservas para tus hoteles, spas, mesas de restaurante o tours, se listarán aquí en tiempo real.
      </p>
      @if($currentStatus)
        <a href="{{ route('merchant.reservations') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
          <span class="material-symbols-outlined text-sm">clear_all</span> Ver Todas las Reservas
        </a>
      @else
        <a href="{{ route('merchant.products.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#006c47] hover:bg-[#10B981] text-white font-bold text-xs rounded-xl transition-all shadow-xs">
          <span class="material-symbols-outlined text-sm">add_circle</span> Publicar Servicio o Reserva
        </a>
      @endif
    </div>
  @else
    <div class="space-y-5">
      @foreach($reservations as $reservation)
        @php
          $statusMeta = [
            'pending'   => ['label' => 'Pendiente de confirmación', 'badge' => 'bg-amber-100 text-amber-900 border-amber-300', 'icon' => 'hourglass_top'],
            'confirmed' => ['label' => 'Confirmada', 'badge' => 'bg-emerald-100 text-emerald-900 border-emerald-300', 'icon' => 'check_circle'],
            'rejected'  => ['label' => 'No disponible / Rechazada', 'badge' => 'bg-rose-100 text-rose-900 border-rose-300', 'icon' => 'cancel'],
            'cancelled' => ['label' => 'Cancelada por el cliente', 'badge' => 'bg-slate-100 text-slate-700 border-slate-300', 'icon' => 'event_busy'],
            'completed' => ['label' => 'Completada', 'badge' => 'bg-blue-100 text-blue-900 border-blue-300', 'icon' => 'task_alt'],
          ][$reservation->status] ?? ['label' => ucfirst($reservation->status), 'badge' => 'bg-slate-100 text-slate-700 border-slate-300', 'icon' => 'info'];

          $rawPhone = preg_replace('/[^\d]/', '', $reservation->customer?->phone ?? '');
          $waPhone = str_starts_with($rawPhone, '57') ? $rawPhone : ('57' . $rawPhone);
        @endphp

        <article class="bg-white rounded-2xl shadow-[0_8px_24px_rgba(27,28,28,.04)] border border-slate-200/90 overflow-hidden transition-all hover:shadow-md hover:border-emerald-300">
          
          <!-- 1. ENCABEZADO SUPERIOR DE LA TARJETA -->
          <div class="bg-slate-50/90 px-5 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white border border-slate-200 text-slate-900 font-mono text-xs font-bold tracking-tight shadow-2xs">
                <span class="material-symbols-outlined text-[15px] text-emerald-600">confirmation_number</span>
                <span>{{ $reservation->reference }}</span>
              </span>
              <span class="text-xs text-slate-500 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px] text-slate-400">schedule</span>
                {{ $reservation->created_at->format('d/m/Y H:i') }} · hace {{ $reservation->created_at->diffForHumans() }}
              </span>
            </div>

            <div class="flex items-center gap-3">
              @if($reservation->quoted_price !== null)
                <div class="text-right">
                  <span class="text-[10px] uppercase font-bold text-slate-400 block leading-tight">Tarifa estimada</span>
                  <span class="text-sm font-black text-slate-900">${{ number_format($reservation->quoted_price, 0, ',', '.') }} COP</span>
                </div>
              @endif
              <span class="rounded-full px-3 py-1 text-xs font-extrabold border flex items-center gap-1.5 shadow-2xs {{ $statusMeta['badge'] }}">
                <span class="material-symbols-outlined text-[15px]">{{ $statusMeta['icon'] }}</span>
                <span>{{ $statusMeta['label'] }}</span>
              </span>
            </div>
          </div>

          <!-- 2. CUERPO DE LA TARJETA: SERVICIO Y CLIENTE -->
          <div class="p-5 md:p-6 space-y-5">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pb-4 border-b border-slate-100">
              
              <!-- Detalle del Servicio -->
              <div class="space-y-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm text-emerald-600">room_service</span> Servicio Solicitado
                </span>
                <div>
                  <h2 class="text-lg md:text-xl font-['Manrope'] font-extrabold text-slate-900">
                    <a href="{{ route('consumer.product', $reservation->product_id) }}" target="_blank" class="hover:text-[#006c47] transition-colors inline-flex items-center gap-1.5">
                      <span>{{ $reservation->product->name ?? 'Servicio' }}</span>
                      <span class="material-symbols-outlined text-sm text-slate-400">open_in_new</span>
                    </a>
                  </h2>
                  <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                      {{ $reservation->product->category ?? 'Servicios' }}
                    </span>
                    <span class="text-xs font-semibold text-slate-600">
                      Cantidad: <strong class="text-slate-900">{{ $reservation->quantity }} {{ $reservation->quantity === 1 ? 'unidad/cupo' : 'unidades/cupos' }}</strong>
                    </span>
                  </div>
                </div>

                @if($reservation->selected_options)
                  <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 space-y-0.5">
                    <span class="font-bold text-[10px] uppercase tracking-wider text-slate-400 block">Especificaciones elegidas:</span>
                    <p class="font-medium text-slate-900">
                      {{ collect($reservation->selected_options)->map(fn($v, $k) => "{$k}: {$v}")->implode(' · ') }}
                    </p>
                  </div>
                @endif
              </div>

              <!-- Detalle del Cliente -->
              <div class="space-y-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm text-blue-600">person</span> Datos del Cliente
                </span>
                <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-100 space-y-2">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0 border border-emerald-200">
                      {{ strtoupper(substr($reservation->customer->name ?? 'C', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-extrabold text-slate-900 truncate">{{ $reservation->customer->name ?? 'Cliente' }}</p>
                      <p class="text-xs text-slate-500 truncate flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs text-slate-400">mail</span>
                        {{ $reservation->customer->email ?? 'Sin correo' }}
                      </p>
                    </div>
                  </div>

                  @if($reservation->customer?->phone)
                    <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-slate-200/60">
                      <a href="tel:{{ $reservation->customer->phone }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-bold text-[11px] shadow-2xs transition-colors">
                        <span class="material-symbols-outlined text-xs text-emerald-600">call</span>
                        <span>{{ $reservation->customer->phone }}</span>
                      </a>
                      @if(strlen($rawPhone) >= 7)
                        <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode('Hola ' . ($reservation->customer->name ?? '') . ', te contacto de ' . (auth()->user()->companyProfile->company_name ?? 'FusaShop') . ' sobre tu solicitud de reserva ' . $reservation->reference . ' para ' . ($reservation->product->name ?? 'el servicio') . '.') }}" 
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[11px] shadow-2xs transition-colors">
                          <span class="material-symbols-outlined text-xs">chat</span>
                          <span>Contactar WhatsApp</span>
                        </a>
                      @endif
                    </div>
                  @endif
                </div>
              </div>

            </div>

            <!-- Fila 2: Franja de Fecha, Hora y Personas (Espaciosa y sin solapamiento) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              
              <div class="flex items-center gap-3 p-3.5 bg-emerald-50/70 rounded-xl border border-emerald-100">
                <div class="w-10 h-10 rounded-lg bg-white text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs border border-emerald-100">
                  <span class="material-symbols-outlined text-xl">calendar_month</span>
                </div>
                <div class="min-w-0">
                  <span class="text-[10px] font-black uppercase text-emerald-900 tracking-wider block">Fecha Solicitada</span>
                  <span class="text-sm font-extrabold text-slate-900">
                    {{ $reservation->preferred_date ? $reservation->preferred_date->format('d/m/Y') : 'Por coordinar' }}
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-3 p-3.5 bg-blue-50/70 rounded-xl border border-blue-100">
                <div class="w-10 h-10 rounded-lg bg-white text-blue-700 flex items-center justify-center shrink-0 shadow-2xs border border-blue-100">
                  <span class="material-symbols-outlined text-xl">schedule</span>
                </div>
                <div class="min-w-0">
                  <span class="text-[10px] font-black uppercase text-blue-900 tracking-wider block">Hora Solicitada</span>
                  <span class="text-sm font-extrabold text-slate-900">
                    {{ $reservation->preferred_time ? substr((string) $reservation->preferred_time, 0, 5) : 'Por coordinar' }}
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-3 p-3.5 bg-purple-50/70 rounded-xl border border-purple-100">
                <div class="w-10 h-10 rounded-lg bg-white text-purple-700 flex items-center justify-center shrink-0 shadow-2xs border border-purple-100">
                  <span class="material-symbols-outlined text-xl">group</span>
                </div>
                <div class="min-w-0">
                  <span class="text-[10px] font-black uppercase text-purple-900 tracking-wider block">Personas / Asistentes</span>
                  <span class="text-sm font-extrabold text-slate-900">
                    {{ $reservation->party_size ? $reservation->party_size . ($reservation->party_size === 1 ? ' persona' : ' personas') : 'No especificado' }}
                  </span>
                </div>
              </div>

            </div>

            <!-- Mensaje o indicación del cliente -->
            @if($reservation->customer_notes)
              <div class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-950 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-base text-amber-700 shrink-0 mt-0.5">format_quote</span>
                <div class="min-w-0 flex-1">
                  <span class="font-extrabold text-amber-900 block text-[11px] mb-0.5">Mensaje o preferencia enviada por el cliente:</span>
                  <p class="italic text-slate-800 font-medium leading-relaxed">"{{ $reservation->customer_notes }}"</p>
                </div>
              </div>
            @endif

            <!-- Respuesta del comercio registrada previamente -->
            @if($reservation->merchant_notes && !in_array($reservation->status, ['pending'], true))
              <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-base text-emerald-700 shrink-0 mt-0.5">reply</span>
                <div class="min-w-0 flex-1">
                  <span class="font-extrabold text-emerald-900 block text-[11px] mb-0.5">
                    Tu respuesta enviada al cliente:
                    @if($reservation->responded_at)
                      <span class="font-normal text-emerald-700 ml-1">({{ $reservation->responded_at->format('d/m/Y H:i') }})</span>
                    @endif
                  </span>
                  <p class="font-medium text-slate-900 leading-relaxed">{{ $reservation->merchant_notes }}</p>
                </div>
              </div>
            @endif

          </div>

          <!-- 3. BARRA DE ACCIÓN Y RESPUESTA (FOOTER INTEGRADO) -->
          <div class="bg-slate-50/90 px-5 py-4 border-t border-slate-100">
            @if(in_array($reservation->status, ['pending', 'confirmed'], true))
              <form method="POST" action="{{ route('merchant.reservations.update', $reservation) }}" class="space-y-3">
                @csrf
                @method('PATCH')

                <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                  <div class="flex-1">
                    <label class="block text-[11px] font-extrabold text-slate-700 mb-1">
                      {{ $reservation->status === 'pending' ? 'Instrucciones o mensaje al cliente al confirmar/rechazar (opcional):' : 'Actualizar mensaje al cliente (opcional):' }}
                    </label>
                    <input type="text" name="merchant_notes" maxlength="1500"
                           value="{{ old('merchant_notes', $reservation->merchant_notes) }}"
                           placeholder="Ej: Confirmada para las 3:00 PM. Por favor presentar documento al ingreso en recepción..."
                           class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#10B981] focus:border-[#10B981] outline-none shadow-2xs">
                  </div>

                  <div class="flex items-center gap-2 shrink-0 lg:self-end">
                    @if($reservation->status === 'pending')
                      <button type="submit" name="status" value="confirmed"
                              class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs rounded-xl shadow-xs hover:shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        <span>Confirmar Reserva</span>
                      </button>
                      <button type="submit" name="status" value="rejected"
                              onclick="return confirm('¿Seguro que no tienes disponibilidad para esta reserva? Se notificará al cliente.')"
                              class="px-4 py-2.5 bg-white hover:bg-rose-50 border border-rose-300 text-rose-700 font-extrabold text-xs rounded-xl transition-all flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-base">cancel</span>
                        <span>No disponible / Rechazar</span>
                      </button>
                    @else
                      <button type="submit" name="status" value="completed"
                              class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-extrabold text-xs rounded-xl shadow-xs hover:shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-base">task_alt</span>
                        <span>Marcar como Completada</span>
                      </button>
                      <button type="submit" name="status" value="rejected"
                              onclick="return confirm('¿Cancelar esta reserva ya confirmada? Se notificará al cliente.')"
                              class="px-4 py-2.5 bg-white hover:bg-rose-50 border border-rose-300 text-rose-700 font-extrabold text-xs rounded-xl transition-all flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-base">cancel</span>
                        <span>Cancelar Reserva</span>
                      </button>
                    @endif
                  </div>
                </div>
              </form>
            @else
              <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1.5 font-bold">
                  <span class="material-symbols-outlined text-base {{ $reservation->status === 'completed' ? 'text-blue-600' : 'text-slate-400' }}">
                    {{ $reservation->status === 'completed' ? 'verified' : 'lock' }}
                  </span>
                  {{ $reservation->status === 'completed' ? 'Servicio prestado y completado exitosamente' : 'Esta solicitud se encuentra cerrada (' . $statusMeta['label'] . ')' }}
                </span>
                <span class="font-mono text-[11px] text-slate-400">Ref: {{ $reservation->reference }}</span>
              </div>
            @endif
          </div>

        </article>
      @endforeach
    </div>

    <div class="pt-4">
      {{ $reservations->links() }}
    </div>
  @endif

</div>
@endsection
