@extends('layouts.app')
@section('title', 'Gestión de Reservas & Servicios | FusaShop')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8 space-y-6">

  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20">
    <div>
      <div class="flex items-center gap-2">
        <h1 class="text-2xl md:text-3xl font-['Manrope'] font-extrabold text-on-background">Gestión de Reservas & Servicios</h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#006c47] border border-emerald-200 uppercase tracking-wider flex items-center gap-1">
          <span class="material-symbols-outlined text-xs">event_available</span> Servicios Activos
        </span>
      </div>
      <p class="text-on-surface-variant text-xs md:text-sm mt-1">
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
    <div class="space-y-4">
      @foreach($reservations as $reservation)
        @php
          $statusMeta = [
            'pending'   => ['label' => 'Pendiente de confirmación', 'badge' => 'bg-amber-100 text-amber-800 border-amber-200', 'icon' => 'hourglass_top'],
            'confirmed' => ['label' => 'Confirmada', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'icon' => 'check_circle'],
            'rejected'  => ['label' => 'No disponible / Rechazada', 'badge' => 'bg-rose-100 text-rose-800 border-rose-200', 'icon' => 'cancel'],
            'cancelled' => ['label' => 'Cancelada por el cliente', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'icon' => 'event_busy'],
            'completed' => ['label' => 'Completada', 'badge' => 'bg-blue-100 text-blue-800 border-blue-200', 'icon' => 'task_alt'],
          ][$reservation->status] ?? ['label' => ucfirst($reservation->status), 'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'icon' => 'info'];

          $rawPhone = preg_replace('/[^\d]/', '', $reservation->customer?->phone ?? '');
          $waPhone = str_starts_with($rawPhone, '57') ? $rawPhone : ('57' . $rawPhone);
        @endphp

        <article class="bg-white rounded-2xl p-5 md:p-6 shadow-[0_12px_32px_rgba(27,28,28,.06)] border border-outline-variant/20 transition-all hover:border-emerald-300">
          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            
            <!-- INFORMACIÓN PRINCIPAL DE LA RESERVA -->
            <div class="flex-1 space-y-4">
              
              <!-- Fila Superior: Referencia + Estado + Fecha Creación -->
              <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-xs font-black px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 border border-slate-200">
                  {{ $reservation->reference }}
                </span>
                <span class="rounded-full px-3 py-1 text-xs font-extrabold border flex items-center gap-1 {{ $statusMeta['badge'] }}">
                  <span class="material-symbols-outlined text-[14px]">{{ $statusMeta['icon'] }}</span>
                  <span>{{ $statusMeta['label'] }}</span>
                </span>
                <span class="text-xs text-slate-400">
                  Solicitada hace {{ $reservation->created_at->diffForHumans() }} ({{ $reservation->created_at->format('d/m/Y H:i') }})
                </span>
              </div>

              <!-- Título del Servicio & Precio -->
              <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                  <h2 class="text-lg md:text-xl font-['Manrope'] font-extrabold text-slate-900">
                    <a href="{{ route('consumer.product', $reservation->product_id) }}" target="_blank" class="hover:text-[#006c47] transition-colors inline-flex items-center gap-1.5">
                      <span>{{ $reservation->product->name ?? 'Servicio' }}</span>
                      <span class="material-symbols-outlined text-xs text-slate-400">open_in_new</span>
                    </a>
                  </h2>
                  <p class="text-xs text-slate-500 mt-0.5">
                    Categoría: <span class="font-bold text-slate-700">{{ $reservation->product->category ?? 'Servicios' }}</span> · Cantidad: <span class="font-bold text-slate-700">{{ $reservation->quantity }} {{ $reservation->quantity === 1 ? 'unidad/cupo' : 'unidades/cupos' }}</span>
                  </p>
                </div>
                @if($reservation->quoted_price !== null)
                  <div class="text-right">
                    <p class="text-xs text-slate-500">Precio estimado / base:</p>
                    <p class="text-base font-extrabold text-[#006c47]">${{ number_format($reservation->quoted_price, 0, ',', '.') }} COP</p>
                  </div>
                @endif
              </div>

              <!-- DATOS CLAVE: AGENDA, PERSONAS, OPCIONES -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50/80 rounded-xl p-3.5 border border-slate-200/80 text-xs">
                <div>
                  <span class="text-slate-500 font-bold block text-[10px] uppercase">Fecha de Reserva</span>
                  <span class="font-extrabold text-slate-900 flex items-center gap-1 mt-0.5">
                    <span class="material-symbols-outlined text-emerald-700 text-sm">calendar_month</span>
                    {{ $reservation->preferred_date ? $reservation->preferred_date->format('d/m/Y') : 'Por coordinar' }}
                  </span>
                </div>

                <div>
                  <span class="text-slate-500 font-bold block text-[10px] uppercase">Hora de Reserva</span>
                  <span class="font-extrabold text-slate-900 flex items-center gap-1 mt-0.5">
                    <span class="material-symbols-outlined text-emerald-700 text-sm">schedule</span>
                    {{ $reservation->preferred_time ? substr((string) $reservation->preferred_time, 0, 5) : 'Por coordinar' }}
                  </span>
                </div>

                <div>
                  <span class="text-slate-500 font-bold block text-[10px] uppercase">Personas / Asistentes</span>
                  <span class="font-extrabold text-slate-900 flex items-center gap-1 mt-0.5">
                    <span class="material-symbols-outlined text-emerald-700 text-sm">group</span>
                    {{ $reservation->party_size ? $reservation->party_size . ($reservation->party_size === 1 ? ' persona' : ' personas') : 'No especificado' }}
                  </span>
                </div>
              </div>

              <!-- OPCIONES Y ESPECIFICACIONES SELECCIONADAS -->
              @if($reservation->selected_options)
                <div class="text-xs bg-emerald-50/50 rounded-xl p-3 border border-emerald-100 space-y-1">
                  <span class="font-bold text-emerald-950 uppercase text-[10px] tracking-wider block">Opciones de la reserva / Especificaciones</span>
                  <p class="text-slate-700 font-medium">
                    {{ collect($reservation->selected_options)->map(fn($v, $k) => "{$k}: {$v}")->implode(' · ') }}
                  </p>
                </div>
              @endif

              <!-- DATOS DEL CLIENTE Y CONTACTO -->
              <div class="flex flex-wrap items-center gap-4 text-xs pt-1">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-emerald-100 text-[#006c47] font-bold text-xs flex items-center justify-center">
                    {{ strtoupper(substr($reservation->customer->name ?? 'C', 0, 1)) }}
                  </div>
                  <div>
                    <span class="font-extrabold text-slate-900">{{ $reservation->customer->name ?? 'Cliente' }}</span>
                    <span class="text-slate-500 ml-1">({{ $reservation->customer->email ?? '' }})</span>
                  </div>
                </div>

                @if($reservation->customer?->phone)
                  <div class="flex items-center gap-2">
                    <a href="tel:{{ $reservation->customer->phone }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition-colors">
                      <span class="material-symbols-outlined text-xs text-emerald-600">call</span>
                      <span>{{ $reservation->customer->phone }}</span>
                    </a>
                    @if(strlen($rawPhone) >= 7)
                      <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode('Hola ' . ($reservation->customer->name ?? '') . ', te contacto de ' . (auth()->user()->companyProfile->company_name ?? 'FusaShop') . ' sobre tu solicitud de reserva ' . $reservation->reference . ' para ' . ($reservation->product->name ?? 'el servicio') . '.') }}" 
                         target="_blank" rel="noopener"
                         class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-[#006c47] font-extrabold text-[11px] transition-colors">
                        <span class="material-symbols-outlined text-xs">chat</span>
                        <span>WhatsApp</span>
                      </a>
                    @endif
                  </div>
                @endif
              </div>

              <!-- NOTA O MENSAJE DEL CLIENTE -->
              @if($reservation->customer_notes)
                <div class="rounded-xl bg-amber-50/60 border border-amber-200/80 p-3 text-xs text-amber-950 space-y-1">
                  <span class="font-extrabold text-amber-900 uppercase text-[10px] tracking-wider block flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs text-amber-700">chat_bubble</span> Mensaje o indicación del cliente:
                  </span>
                  <p class="italic text-slate-800">"{{ $reservation->customer_notes }}"</p>
                </div>
              @endif

              <!-- RESPUESTA PREVIA DEL COMERCIANTE -->
              @if($reservation->merchant_notes && !in_array($reservation->status, ['pending'], true))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-950 space-y-1">
                  <span class="font-extrabold text-[#006c47] uppercase text-[10px] tracking-wider block flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">reply</span> Tu respuesta registrada al cliente:
                  </span>
                  <p class="text-emerald-900 font-medium">{{ $reservation->merchant_notes }}</p>
                  @if($reservation->responded_at)
                    <span class="text-[10px] text-emerald-700/80 block mt-0.5">Respondida el {{ $reservation->responded_at->format('d/m/Y H:i') }}</span>
                  @endif
                </div>
              @endif

            </div>

            <!-- FORMULARIO DE GESTIÓN Y ACCIONES DEL COMERCIANTE -->
            <div class="w-full lg:w-80 shrink-0 bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-3">
              <h3 class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base text-[#006c47]">admin_panel_settings</span> Gestión de la Reserva
              </h3>

              @if(in_array($reservation->status, ['pending', 'confirmed'], true))
                <form method="POST" action="{{ route('merchant.reservations.update', $reservation) }}" class="space-y-3">
                  @csrf
                  @method('PATCH')

                  <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                      {{ $reservation->status === 'pending' ? 'Instrucciones o mensaje al confirmar/rechazar:' : 'Actualizar mensaje o instrucciones:' }}
                    </label>
                    <textarea name="merchant_notes" maxlength="1500" rows="3"
                              placeholder="Ej: Confirmada para las 3:00 PM. Por favor presentar documento al ingreso en recepción..."
                              class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:ring-2 focus:ring-[#10B981] focus:border-[#10B981] outline-none">{{ old('merchant_notes', $reservation->merchant_notes) }}</textarea>
                  </div>

                  @if($reservation->status === 'pending')
                    <div class="grid grid-cols-2 gap-2">
                      <button type="submit" name="status" value="confirmed"
                              class="w-full py-2.5 px-3 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span>Confirmar</span>
                      </button>
                      <button type="submit" name="status" value="rejected"
                              onclick="return confirm('¿Seguro que no tienes disponibilidad para esta reserva? Se notificará al cliente.')"
                              class="w-full py-2.5 px-3 bg-white hover:bg-rose-50 border border-rose-300 text-rose-700 font-extrabold text-xs rounded-xl transition-all flex items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">cancel</span>
                        <span>Rechazar</span>
                      </button>
                    </div>
                  @else
                    <div class="grid grid-cols-2 gap-2">
                      <button type="submit" name="status" value="completed"
                              class="w-full py-2.5 px-3 bg-blue-700 hover:bg-blue-800 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">task_alt</span>
                        <span>Completada</span>
                      </button>
                      <button type="submit" name="status" value="rejected"
                              onclick="return confirm('¿Cancelar esta reserva ya confirmada? Se notificará al cliente.')"
                              class="w-full py-2.5 px-3 bg-white hover:bg-rose-50 border border-rose-300 text-rose-700 font-extrabold text-xs rounded-xl transition-all flex items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">cancel</span>
                        <span>Cancelar</span>
                      </button>
                    </div>
                  @endif
                </form>
              @else
                <div class="p-3 bg-white rounded-xl border border-slate-200 text-center space-y-1">
                  <span class="material-symbols-outlined text-2xl {{ $reservation->status === 'completed' ? 'text-blue-600' : 'text-slate-400' }}">
                    {{ $reservation->status === 'completed' ? 'verified' : 'lock' }}
                  </span>
                  <p class="text-xs font-bold text-slate-800">
                    {{ $reservation->status === 'completed' ? 'Servicio prestado y completado' : 'Esta solicitud se encuentra cerrada' }}
                  </p>
                  <p class="text-[10px] text-slate-400">Estado final: {{ $statusMeta['label'] }}</p>
                </div>
              @endif

              <div class="pt-2 border-t border-slate-200/80 flex items-center justify-between text-[11px] text-slate-500">
                <span>Ref: {{ $reservation->reference }}</span>
                <a href="{{ route('merchant.messages') }}" class="text-[#006c47] font-bold hover:underline flex items-center gap-0.5">
                  <span class="material-symbols-outlined text-xs">chat</span> Mensajería
                </a>
              </div>

            </div>

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
