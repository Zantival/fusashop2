@extends('layouts.app')
@section('title', 'Notificaciones')
@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-3xl font-['Manrope'] font-bold text-on-background flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-3xl">notifications</span> Panel de Notificaciones
    </h1>
    @if(auth()->user()->unreadNotifications->count() > 0)
    <form action="{{ route('notifications.readAll') }}" method="POST">
      @csrf
      <button type="submit" class="text-sm px-4 py-2 bg-secondary-fixed text-on-secondary-fixed rounded-xl font-bold hover:brightness-95">Marcar todo como leído</button>
    </form>
    @endif
  </div>

  <div class="space-y-4">
    @forelse(auth()->user()->notifications as $notification)
      <div class="p-4 rounded-xl border {{ $notification->read_at ? 'bg-surface-container-lowest border-outline-variant/30 opacity-70' : 'bg-surface-container border-primary/40 shadow-sm' }} flex gap-4 transition-all hover:shadow-md">
        <div class="shrink-0 mt-1">
          <span class="material-symbols-outlined {{ $notification->read_at ? 'text-on-surface-variant' : 'text-primary' }} text-2xl">
            {{ $notification->data['icon'] ?? 'info' }}
          </span>
        </div>
        <div class="flex-1">
          @php
              $url = $notification->data['url'] ?? null;
              if(!$url && isset($notification->data['type']) && $notification->data['type'] === 'banner_request' && isset($notification->data['request_id'])) {
                  $url = route('analyst.banner-requests.show', $notification->data['request_id']);
              }
          @endphp
          @if($url)
            <a href="{{ $url }}" class="block group">
          @endif
          <h4 class="font-bold text-on-background group-hover:text-primary transition-colors">{{ $notification->data['title'] ?? 'Notificación' }}</h4>
          <p class="text-sm text-on-surface-variant mt-1 group-hover:text-on-surface transition-colors">{{ $notification->data['message'] ?? '' }}</p>
          @if($url)
            <span class="text-xs text-primary font-bold mt-2 flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity"><span class="material-symbols-outlined text-sm">visibility</span> Ver Solicitud</span>
            </a>
          @endif
          <span class="text-xs text-on-surface-variant/70 mt-2 block">{{ $notification->created_at->diffForHumans() }}</span>
        </div>
        @if(!$notification->read_at)
        <div>
           <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
             @csrf
             <button type="submit" class="text-primary hover:bg-primary/10 p-2 rounded-full h-10 w-10 flex items-center justify-center">
               <span class="material-symbols-outlined text-[18px]">done</span>
             </button>
           </form>
        </div>
        @endif
      </div>
    @empty
      <div class="text-center py-12 bg-surface-container-lowest rounded-3xl border border-outline-variant/20">
        <span class="material-symbols-outlined text-5xl text-on-surface-variant/50 mb-3">notifications_paused</span>
        <h3 class="text-xl font-bold text-on-surface-variant">Sin notificaciones</h3>
        <p class="text-sm mt-1 text-on-surface-variant/80">Te avisaremos cuando haya novedades importantes.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
