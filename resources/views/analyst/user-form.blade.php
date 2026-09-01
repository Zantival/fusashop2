@extends('layouts.app')
@section('title', $user ? 'Editar Usuario' : 'Nuevo Usuario')
@section('content')
<div class="max-w-2xl mx-auto px-6 py-8">
  <div class="flex items-center gap-4 mb-8">
    <a href="{{ route('analyst.users') }}" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm hover:bg-[#f0eded] transition-colors">
      <span class="material-symbols-outlined text-[#3c4a41]">arrow_back</span>
    </a>
    <h1 class="text-3xl font-['Manrope'] font-bold text-[#1b1c1c]">{{ $user ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
  </div>

  @if($errors->any())
    <div class="bg-red-50 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
      @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
  @endif

  <div class="bg-white rounded-2xl p-8 shadow-sm">
    <form method="POST" action="{{ $user ? route('analyst.users.update', $user->id) : route('analyst.users.store') }}" class="space-y-5">
      @csrf
      @if($user) @method('PUT') @endif

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Nombre completo *</label>
          <input type="text" name="name" value="{{ old('name', $user?->name) }}" required
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="Nombre completo"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Correo electrónico *</label>
          <input type="email" name="email" value="{{ old('email', $user?->email) }}" required
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="correo@ejemplo.com"/>
        </div>
        <div>
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Rol *</label>
          <select name="role" required class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]">
            @foreach(['consumer'=>'Consumidor','merchant'=>'Comerciante','analyst'=>'Analista/Admin'] as $val=>$label)
              <option value="{{ $val }}" {{ old('role',$user?->role)===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Teléfono</label>
          <input type="tel" name="phone" value="{{ old('phone', $user?->phone) }}"
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="+57 300 000 0000"/>
        </div>
        <div>
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">
            Contraseña {{ $user ? '(dejar vacío para no cambiar)' : '*' }}
          </label>
          <input type="password" name="password" {{ !$user ? 'required' : '' }}
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="Mínimo 8 caracteres"/>
        </div>
      </div>

      <div class="flex gap-4 pt-2">
        <button type="submit" class="flex-1 py-3.5 bg-gradient-to-r from-[#006c47] to-[#00b67a] text-white font-semibold rounded-xl hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-md">
          <span class="material-symbols-outlined text-sm">{{ $user ? 'save' : 'person_add' }}</span>
          {{ $user ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
        <a href="{{ route('analyst.users') }}" class="px-6 py-3.5 bg-[#f0eded] text-[#1b1c1c] font-semibold rounded-xl hover:bg-[#e5e2e1] transition-colors text-center">
          Cancelar
        </a>
      </div>
    </form>

    @if($user && $user->role === 'merchant' && $user->companyProfile)
      <div class="mt-12 pt-8 border-t border-outline-variant/30">
        <h2 class="text-xl font-bold text-on-background mb-6 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">verified_user</span>
          Verificación de Identidad (KYC)
        </h2>

        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant/20">
              <p class="text-xs font-bold text-on-surface-variant uppercase mb-2">Información Corporativa</p>
              <p class="text-sm font-bold">{{ $user->companyProfile->company_name }}</p>
              <p class="text-xs text-on-surface-variant">Tipo: {{ $user->companyProfile->business_type }}</p>
              <p class="text-xs text-on-surface-variant">Tel: {{ $user->companyProfile->phone }}</p>
              <p class="text-xs text-on-surface-variant">Dirección: {{ $user->companyProfile->address }}</p>
            </div>
            <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant/20">
              <p class="text-xs font-bold text-on-surface-variant uppercase mb-2">Estado Actual</p>
              @php
                $statusMap = [
                  'pending' => ['bg-amber-100 text-amber-700', 'Pendiente'],
                  'approved' => ['bg-green-100 text-green-700', 'Aprobado'],
                  'rejected' => ['bg-red-100 text-red-700', 'Rechazado']
                ];
                $s = $statusMap[$user->companyProfile->kyc_status] ?? $statusMap['pending'];
              @endphp
              <span class="px-3 py-1 rounded-full text-xs font-bold {{ $s[0] }}">{{ $s[1] }}</span>
              @if($user->companyProfile->kyc_notes)
                <p class="mt-3 text-xs text-on-surface-variant italic">Notas: {{ $user->companyProfile->kyc_notes }}</p>
              @endif
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('analyst.users.rut', $user->id) }}" target="_blank" class="flex items-center justify-between p-4 bg-white border border-outline-variant/30 rounded-xl hover:bg-surface-container-low transition-all">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-600">picture_as_pdf</span>
                <span class="text-sm font-semibold">Registro Único Tributario (RUT)</span>
              </div>
              <span class="material-symbols-outlined text-on-surface-variant">open_in_new</span>
            </a>
            @if($user->companyProfile->camara_comercio_path)
              <a href="{{ Storage::url($user->companyProfile->camara_comercio_path) }}" target="_blank" class="flex items-center justify-between p-4 bg-white border border-outline-variant/30 rounded-xl hover:bg-surface-container-low transition-all">
                <div class="flex items-center gap-3">
                  <span class="material-symbols-outlined text-blue-600">description</span>
                  <span class="text-sm font-semibold">Cámara de Comercio</span>
                </div>
                <span class="material-symbols-outlined text-on-surface-variant">open_in_new</span>
              </a>
            @endif
          </div>

          <form method="POST" action="{{ route('analyst.users.kyc', $user->id) }}" class="p-6 bg-surface-container-highest rounded-2xl space-y-4">
            @csrf
            <div>
              <label class="block text-sm font-bold mb-2">Decisión de Verificación</label>
              <textarea name="kyc_notes" placeholder="Escribe aquí los motivos si decides rechazar la solicitud..." class="w-full bg-white rounded-xl border border-outline-variant/30 p-4 text-sm outline-none focus:ring-2 focus:ring-primary/20 transition-all resize-none" rows="3"></textarea>
            </div>
            
            <div class="flex gap-3">
              <button type="submit" name="kyc_status" value="approved" class="flex-1 py-3 bg-[#00b67a] text-white font-bold rounded-xl hover:opacity-90 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">check_circle</span> Aprobar
              </button>
              <button type="submit" name="kyc_status" value="rejected" class="flex-1 py-3 bg-error text-white font-bold rounded-xl hover:opacity-90 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">cancel</span> Rechazar
              </button>
            </div>
          </form>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
