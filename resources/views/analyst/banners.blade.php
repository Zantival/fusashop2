@extends('layouts.app')
@section('title','Gestión de Banners Globales')
@section('content')
<div class="max-w-5xl mx-auto px-6 py-8">
  <h1 class="text-3xl font-['Manrope'] font-bold text-on-background mb-2 flex items-center gap-2">
    <span class="material-symbols-outlined text-primary">featured_play_list</span> Banners Globales
  </h1>
  <p class="text-on-surface-variant mb-8">Los banners activos se muestran en el catálogo principal para todos los compradores.</p>

  <!-- Upload form para Logo Global -->
  <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-card mb-8">
    <h2 class="font-bold text-on-background font-['Manrope'] text-lg mb-4">Logo Global del Sistema</h2>
    <form method="POST" action="{{ route('analyst.global.logo.store') }}" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 items-end">
      @csrf
      <div class="flex-1 w-full">
        <label class="block text-sm font-semibold text-on-surface-variant mb-1">Subir Logo (se reemplazará el actual en toda la tienda)</label>
        <input type="file" name="logo" accept="image/*" required
               class="w-full text-sm text-on-surface-variant file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
      </div>
      <button type="submit" class="px-6 py-2.5 bg-secondary-gradient text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-md flex items-center gap-2 whitespace-nowrap">
        <span class="material-symbols-outlined text-sm">upload</span> Actualizar Logo
      </button>
    </form>
    @if(file_exists(storage_path('app/public/system/global_logo.png')))
    <div class="mt-4 p-4 bg-surface-container rounded-xl flex items-center gap-4">
      <p class="text-sm font-semibold text-on-surface-variant">Logo actual:</p>
      <img src="{{ asset('storage/system/global_logo.png') }}?v={{ time() }}" class="h-10 object-contain bg-white p-1 rounded">
    </div>
    @endif
  </div>

  <!-- Upload form -->
  <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-card mb-8">
    <h2 class="font-bold text-on-background font-['Manrope'] text-lg mb-4">Subir nuevo banner</h2>
    <form method="POST" action="{{ route('analyst.banners.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf
      <div>
        <label class="block text-sm font-semibold text-on-surface-variant mb-1">Título (opcional)</label>
        <input type="text" name="title" class="w-full rounded-xl border border-outline-variant px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/40 focus:outline-none" placeholder="Ej: Promoción Mayo">
      </div>
      <div>
        <label class="block text-sm font-semibold text-on-surface-variant mb-1">URL de enlace (opcional)</label>
        <input type="url" name="link_url" class="w-full rounded-xl border border-outline-variant px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/40 focus:outline-none" placeholder="https://...">
      </div>
      <div>
        <label class="block text-sm font-semibold text-on-surface-variant mb-1">Imagen o Video * (JPG/PNG/WEBP/MP4, máx 10MB)</label>
        <input type="file" name="image" accept="image/*,video/*" required
               class="w-full text-sm text-on-surface-variant file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
      </div>
      <div>
        <label class="block text-sm font-semibold text-on-surface-variant mb-1">Orden de aparición</label>
        <input type="number" name="sort_order" value="0" min="0"
               class="w-full rounded-xl border border-outline-variant px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/40 focus:outline-none">
      </div>
      @if($errors->any())
        <div class="md:col-span-2 bg-error-container text-error rounded-xl p-3 text-sm">{{ $errors->first() }}</div>
      @endif
      <div class="md:col-span-2">
        <button type="submit" class="px-6 py-2.5 bg-primary-gradient text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-md flex items-center gap-2">
          <span class="material-symbols-outlined text-sm">upload</span> Publicar Banner
        </button>
      </div>
    </form>
  </div>

  <!-- Banners list -->
  @if($banners->isEmpty())
    <div class="text-center py-16 bg-surface-container-lowest rounded-2xl shadow-card">
      <span class="material-symbols-outlined text-6xl text-on-surface-variant/40 mb-3 block">image</span>
      <p class="text-on-surface-variant font-semibold">No hay banners globales aún.</p>
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      @foreach($banners as $banner)
      <div class="bg-surface-container-lowest rounded-2xl shadow-card overflow-hidden border {{ $banner->is_active ? 'border-primary/30' : 'border-outline-variant/30 opacity-60' }}">
        <div class="w-full h-36 bg-surface-container-low flex items-center justify-center overflow-hidden">
          @php $isVideo = in_array(pathinfo($banner->image_path, PATHINFO_EXTENSION), ['mp4', 'webm', 'mov']); @endphp
          @if($isVideo)
            <video src="{{ asset('storage/'.$banner->image_path) }}" class="max-w-full max-h-full object-contain" autoplay loop muted playsinline></video>
          @else
            <img src="{{ asset('storage/'.$banner->image_path) }}" class="max-w-full max-h-full object-contain">
          @endif
        </div>
        <div class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-semibold text-on-background text-sm">{{ $banner->title ?: 'Sin título' }}</p>
              @if($banner->link_url)
                <a href="{{ $banner->link_url }}" target="_blank" class="text-xs text-primary hover:underline truncate block max-w-[200px]">{{ $banner->link_url }}</a>
              @endif
            </div>
            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $banner->is_active ? 'bg-primary/10 text-primary' : 'bg-surface-container text-on-surface-variant' }}">
              {{ $banner->is_active ? 'Activo' : 'Inactivo' }}
            </span>
          </div>
          <div class="flex gap-2 mt-3">
            <form method="POST" action="{{ route('analyst.banners.toggle', $banner) }}">
              @csrf @method('PATCH')
              <button class="px-3 py-1.5 text-xs font-bold rounded-lg {{ $banner->is_active ? 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' : 'bg-primary/10 text-primary hover:bg-primary/20' }} transition-all">
                {{ $banner->is_active ? 'Desactivar' : 'Activar' }}
              </button>
            </form>
            <form method="POST" action="{{ route('analyst.banners.delete', $banner) }}" onsubmit="return confirm('¿Eliminar este banner?')">
              @csrf @method('DELETE')
              <button class="px-3 py-1.5 text-xs font-bold rounded-lg bg-error-container text-error hover:bg-error hover:text-white transition-all">
                Eliminar
              </button>
            </form>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
