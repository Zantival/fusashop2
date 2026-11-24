@extends('layouts.app')
@section('title', ($notification->data['title'] ?? 'Detalle de notificación') . ' | FusaShop')
@section('content')
@php
  $data = is_array($notification->data) ? $notification->data : [];
  $details = collect($data)->except(['type', 'title', 'message', 'icon', 'url']);
  $destinationUrl = route('notifications.open', $notification->id);
  $merchantId = $data['merchant_id'] ?? null;
@endphp

<div class="max-w-3xl mx-auto px-4 md:px-6 py-8">
  <a href="{{ route('notifications.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-[#006c47] mb-5">
    <span class="material-symbols-outlined text-lg">arrow_back</span> Volver a notificaciones
  </a>

  <article class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 md:p-8 border-b border-slate-100 bg-emerald-50/40">
      <div class="flex items-start gap-4">
        <span class="w-12 h-12 rounded-2xl bg-emerald-100 text-[#006c47] flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">{{ $data['icon'] ?? 'notifications' }}</span>
        </span>
        <div class="min-w-0">
          <p class="text-xs font-bold text-[#006c47] uppercase tracking-wide">Detalle de notificación</p>
          <h1 class="mt-1 text-xl md:text-2xl font-extrabold text-slate-900">{{ $data['title'] ?? 'Notificación del Sistema' }}</h1>
          <p class="mt-2 text-xs text-slate-500">{{ $notification->created_at->format('d/m/Y H:i') }} · {{ $notification->created_at->diffForHumans() }} · Leída</p>
        </div>
      </div>
    </div>

    <div class="p-6 md:p-8 space-y-6">
      <section>
        <h2 class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Mensaje</h2>
        <p class="mt-2 text-sm leading-7 text-slate-800">{{ $data['message'] ?? 'No hay un mensaje adicional para esta notificación.' }}</p>
      </section>

      @if($details->isNotEmpty())
        <section class="pt-5 border-t border-slate-100">
          <h2 class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Información relacionada</h2>
          <dl class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach($details as $key => $value)
              @if(is_scalar($value) && (string) $value !== '')
                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                  <dt class="text-[11px] font-bold text-slate-500">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $key)) }}</dt>
                  <dd class="mt-1 text-sm text-slate-800 break-words">{{ $value }}</dd>
                </div>
              @endif
            @endforeach
          </dl>
        </section>
      @endif

      <div class="pt-5 border-t border-slate-100 flex flex-wrap gap-3">
        @if(auth()->user()->isAnalyst() && $merchantId)
          <a href="{{ route('analyst.users.edit', $merchantId) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#006c47] hover:bg-[#10B981] text-white text-sm font-bold">
            <span class="material-symbols-outlined text-lg">fact_check</span> Revisar cuenta y documentos
          </a>
        @endif
        <a href="{{ $destinationUrl }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-bold">
          <span class="material-symbols-outlined text-lg">open_in_new</span> Ir a la sección relacionada
        </a>
      </div>
      <p class="text-xs text-slate-500">Esta notificación se conserva en tu historial, aunque ya figure como leída.</p>
    </div>
  </article>
</div>
@endsection
