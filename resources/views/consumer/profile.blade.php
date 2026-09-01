@extends('layouts.app')
@section('title','Mi Perfil')
@section('content')
<div class="max-w-3xl mx-auto px-6 py-8">
  <h1 class="text-3xl font-['Manrope'] font-bold text-on-background mb-8 flex items-center gap-2">
    <span class="material-symbols-outlined text-primary text-3xl">manage_accounts</span> Mi Perfil Personal
  </h1>
  
  <div class="bg-surface-container-lowest rounded-2xl p-8 shadow-card border border-outline-variant/20">
    <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
      @csrf
      @method('PUT')
      
      @if($errors->any())
        <div class="bg-error-container text-error p-4 rounded-xl text-sm font-semibold mb-6">
          <ul class="list-disc list-inside">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="flex items-center gap-6 mb-8">
        <div class="w-24 h-24 rounded-full overflow-hidden bg-surface-container border-2 border-outline-variant/50 shadow-inner shrink-0 flex items-center justify-center">
          @if($user->avatar)
            <img src="{{ Storage::url($user->avatar) }}" class="w-full h-full object-cover">
          @else
            <span class="material-symbols-outlined text-4xl text-on-surface-variant">person</span>
          @endif
        </div>
        <div class="flex-1">
          <label class="block text-sm font-semibold text-on-surface-variant mb-2">Foto de Perfil (Avatar)</label>
          <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90 transition-all">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-on-surface-variant mb-2">Nombre completo</label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-surface-container rounded-xl border-0 p-3 outline-none text-on-surface focus:ring-2 focus:ring-primary transition-all shadow-inner" required>
        </div>
        <div>
          <label class="block text-sm font-semibold text-on-surface-variant mb-2">Correo electrónico</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-surface-container rounded-xl border-0 p-3 outline-none text-on-surface focus:ring-2 focus:ring-primary transition-all shadow-inner" required>
        </div>
        <div>
          <label class="block text-sm font-semibold text-on-surface-variant mb-2">Teléfono de contacto</label>
          <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full bg-surface-container rounded-xl border-0 p-3 outline-none text-on-surface focus:ring-2 focus:ring-primary transition-all shadow-inner" placeholder="Ej. 3001234567">
        </div>
        <div>
          <label class="block text-sm font-semibold text-on-surface-variant mb-2">Dirección de envío primaria</label>
          <input type="text" name="address" value="{{ old('address', $user->address) }}" class="w-full bg-surface-container rounded-xl border-0 p-3 outline-none text-on-surface focus:ring-2 focus:ring-primary transition-all shadow-inner" placeholder="Ej. Calle 123, Fusagasugá">
        </div>
      </div>

      <div class="pt-6 mt-6 border-t border-outline-variant/20 flex justify-end">
        <button type="submit" class="px-8 py-3 bg-primary-gradient text-white font-bold rounded-xl hover:opacity-90 active:scale-95 transition-all shadow-md flex items-center gap-2">
          <span class="material-symbols-outlined text-sm">save</span> Guardar Datos
        </button>
      </div>
    </form>
  </div>

  <div id="loyalty-section">
    @if(isset($loyaltyPoints) && $loyaltyPoints->count() > 0)
      <div class="mt-8 bg-white rounded-2xl p-8 shadow-sm border border-outline-variant/20 overflow-hidden relative">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
        <h2 class="text-xl font-bold text-on-surface mb-6 flex items-center gap-2 relative z-10">
          <span class="material-symbols-outlined text-[#feb700] text-3xl" style="font-variation-settings: 'FILL' 1">stars</span> 
          Mis Puntos de Fidelidad
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 relative z-10">
          @foreach($loyaltyPoints as $lp)
            <div class="flex items-center justify-between p-4 bg-surface-container-low rounded-xl border border-surface-container hover:border-primary/30 transition-all">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                  <span class="material-symbols-outlined text-primary text-xl">storefront</span>
                </div>
                <div>
                  <p class="font-bold text-on-surface text-sm leading-tight">{{ $lp->merchant->companyProfile->company_name ?? 'Tienda Local' }}</p>
                  <p class="text-[10px] text-on-surface-variant font-medium">Cliente Frecuente</p>
                </div>
              </div>
              <div class="flex flex-col items-end">
                <span class="text-xl font-black text-primary">{{ number_format($lp->points, 0, ',', '.') }}</span>
                <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider">Puntos</span>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="mt-8 bg-surface-container rounded-2xl p-8 text-center border-2 border-dashed border-outline-variant/30">
        <span class="material-symbols-outlined text-4xl text-on-surface-variant/30 mb-2">stars</span>
        <p class="font-bold text-on-surface">Aún no tienes puntos de fidelidad</p>
        <p class="text-xs text-on-surface-variant mt-1">¡Compra en tus tiendas favoritas para empezar a acumular!</p>
      </div>
    @endif
  </div>
</div>
@endsection
