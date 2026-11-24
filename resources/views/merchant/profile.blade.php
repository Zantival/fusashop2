@extends('layouts.app')
@section('title', 'Perfil Corporativo')
@section('content')
<div class="max-w-4xl mx-auto px-4 md:px-6 py-8">

  <div class="mb-8">
    <h1 class="text-3xl font-black text-on-surface tracking-tight flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-3xl">business</span>
      Identidad Corporativa (KYC)
    </h1>
    <p class="text-on-surface-variant mt-1">Completa tu perfil para ser verificado por el equipo analista.</p>
  </div>

  @if($profile?->kyc_status === 'pending' && !empty($profile?->rut_path))
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3">
      <span class="material-symbols-outlined text-amber-600">hourglass_empty</span>
      <p class="text-sm font-bold text-amber-800">Perfil en revisión — Aún no puedes publicar nuevos productos.</p>
    </div>
  @elseif($profile?->kyc_status === 'approved')
    <div class="mb-6 p-4 bg-[#6efcb9]/20 border border-primary/20 rounded-xl flex items-center gap-3">
      <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1">verified</span>
      <p class="text-sm font-bold text-primary">Perfil Aprobado — Puedes vender y actualizar tu información libremente.</p>
    </div>
  @elseif($profile?->kyc_status === 'rejected')
    <div class="mb-6 p-4 bg-[#ffdad6] border border-[#ba1a1a]/20 rounded-xl flex items-center gap-3">
      <span class="material-symbols-outlined text-[#ba1a1a]">error</span>
      <p class="text-sm font-bold text-[#ba1a1a]">Verificación Rechazada — Vuelve a enviar tu documentación.</p>
    </div>
  @endif

  @if($errors->any())
    <div class="mb-6 p-4 bg-[#ffdad6] border border-[#ba1a1a]/10 rounded-xl">
      <ul class="space-y-1">
        @foreach($errors->all() as $err)
          <li class="text-sm text-[#ba1a1a] font-medium flex items-center gap-2"><span class="material-symbols-outlined text-[14px]">error</span>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('merchant.profile.store') }}" enctype="multipart/form-data" class="space-y-8">
    @csrf

    <!-- Información Básica -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-surface-container">
      <h2 class="font-bold text-on-surface mb-6 flex items-center gap-2 pb-4 border-b border-surface-container">
        <span class="material-symbols-outlined text-primary text-[20px]">info</span> Información de la Empresa
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Nombre del negocio / empresa *</label>
          <input type="text" id="merchant-business-name" name="company_name" value="{{ old('company_name', $profile->company_name ?? '') }}" placeholder="Ej. Café Sumapaz" required
            class="w-full bg-surface-container-low rounded-xl border-2 {{ $errors->has('company_name') ? 'border-[#ba1a1a]' : 'border-transparent focus:border-primary' }} p-4 outline-none text-on-surface text-sm transition-all">
          @error('company_name') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
          <p class="mt-1 text-xs text-on-surface-variant">Este nombre comercial aparecerá públicamente en tu tienda y productos.</p>
        </div>
        <div>
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Nombre del representante legal *</label>
          <input type="text" name="owner_name" value="{{ old('owner_name', Auth::user()->name) }}" placeholder="Nombre y apellido del representante" required
            class="w-full bg-surface-container-low rounded-xl border-2 {{ $errors->has('owner_name') ? 'border-[#ba1a1a]' : 'border-transparent focus:border-primary' }} p-4 outline-none text-on-surface text-sm transition-all">
          @error('owner_name') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
          <p class="mt-1 text-xs text-on-surface-variant">Titular responsable del comercio (no se muestra como nombre de la tienda).</p>
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Razón Social (Automática con S.A.S.)</label>
          <input type="text" id="merchant-legal-name-input" name="legal_name" value="{{ \App\Models\CompanyProfile::suggestedLegalName(old('company_name', $profile->company_name ?? 'Nombre del negocio')) }}" readonly
            class="w-full bg-emerald-50/60 rounded-xl border-2 border-emerald-200/80 p-4 outline-none text-[#006c47] font-bold text-sm cursor-not-allowed">
          <p class="mt-1 text-xs text-on-surface-variant">Se asigna automáticamente a partir del nombre del negocio junto con <strong>S.A.S.</strong> (<span id="merchant-legal-name-preview">{{ \App\Models\CompanyProfile::suggestedLegalName(old('company_name', $profile->company_name ?? 'Nombre del negocio')) }}</span>).</p>
        </div>
        <div>
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Tipo de Productos que vende *</label>
          <input type="text" name="business_type" value="{{ old('business_type', $profile->business_type ?? '') }}" placeholder="Ej. Ropa, Electrónica, Alimentos" required
            class="w-full bg-surface-container-low rounded-xl border-2 {{ $errors->has('business_type') ? 'border-[#ba1a1a]' : 'border-transparent focus:border-primary' }} p-4 outline-none text-on-surface text-sm transition-all">
          @error('business_type') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Teléfono *</label>
          <input type="text" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" required
            class="w-full bg-surface-container-low rounded-xl border-2 {{ $errors->has('phone') ? 'border-[#ba1a1a]' : 'border-transparent focus:border-primary' }} p-4 outline-none text-on-surface text-sm transition-all">
          @error('phone') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Número de Empleados</label>
          <input type="number" name="employee_count" value="{{ old('employee_count', $profile->employee_count ?? '') }}" min="1" placeholder="Ej. 5"
            class="w-full bg-surface-container-low rounded-xl border-2 {{ $errors->has('employee_count') ? 'border-[#ba1a1a]' : 'border-transparent focus:border-primary' }} p-4 outline-none text-on-surface text-sm transition-all">
          @error('employee_count') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">WhatsApp (sin +57)</label>
          <input type="text" name="whatsapp" value="{{ old('whatsapp', $profile->whatsapp ?? '') }}" placeholder="3001234567"
            class="w-full bg-surface-container-low rounded-xl border-2 {{ $errors->has('whatsapp') ? 'border-[#ba1a1a]' : 'border-transparent focus:border-primary' }} p-4 outline-none text-on-surface text-sm transition-all">
          @error('whatsapp') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Dirección física del negocio o despacho * <span class="text-primary text-[11px] font-semibold lowercase">(requerida para vender)</span></label>
          <input type="text" name="address" value="{{ old('address', $profile->address ?? '') }}" required placeholder="Cra 10 # 3-45, Fusagasugá"
            class="w-full bg-surface-container-low rounded-xl border-2 {{ $errors->has('address') ? 'border-[#ba1a1a]' : 'border-transparent focus:border-primary' }} p-4 outline-none text-on-surface text-sm transition-all">
          @error('address') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Descripción Pública</label>
          <textarea name="description" rows="3"
            class="w-full bg-surface-container-low rounded-xl border-2 {{ $errors->has('description') ? 'border-[#ba1a1a]' : 'border-transparent focus:border-primary' }} p-4 outline-none text-on-surface text-sm transition-all resize-none">{{ old('description', $profile->description ?? '') }}</textarea>
          @error('description') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    <!-- KYC / Documentos de Verificación -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-surface-container">
      <div class="flex items-center justify-between pb-4 border-b border-surface-container mb-6 flex-wrap gap-2">
        <h2 class="font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">badge</span> Documentación & Verificación del Negocio
        </h2>
        <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
          💡 Aprobación MiPyme con Cédula y Dirección
        </span>
      </div>
      <p class="text-xs text-on-surface-variant mb-6 leading-relaxed">
        <strong>Puedes vender como MiPyme</strong> cuando cargues tu <strong>cédula</strong>, registres la <strong>dirección del negocio</strong> y el administrador apruebe tu perfil. El <strong>RUT y la Cámara de Comercio son opcionales</strong>: si no los tienes, igual puedes vender; solo no podrás obtener el sello de <strong>Comercio Verificado (✓)</strong>. El administrador revisará los documentos opcionales antes de otorgar ese sello.
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Documento de Identidad (Obligatorio) -->
        <div class="p-4 bg-emerald-50/50 rounded-2xl border-2 border-emerald-200">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-black text-emerald-900 uppercase tracking-wider">Cédula de Identidad *</label>
            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-[#006c47] text-white">Obligatorio</span>
          </div>
          <p class="text-[11px] text-slate-600 mb-3">Foto legible de tu Cédula o documento de identidad (PDF o imagen).</p>
          @if($profile?->id_document_path)
            <input type="hidden" name="id_document_exists" value="1">
          @endif
          <input type="file" name="id_document_file" accept=".pdf,image/*" {{ $profile?->id_document_path ? '' : 'required' }}
            class="w-full text-xs text-slate-700 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:font-bold file:bg-[#006c47] file:text-white hover:file:opacity-90 file:cursor-pointer">
          @error('id_document_file') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
          @if($profile?->id_document_path)
            <p class="text-xs mt-3 text-emerald-800 font-bold flex items-center gap-1">
              <span class="material-symbols-outlined text-[15px] text-[#006c47]">check_circle</span> Cédula cargada correctamente
            </p>
          @endif
        </div>

        <!-- RUT DIAN (Opcional / Recomendado) -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">RUT DIAN</label>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">Recomendado</span>
          </div>
          <p class="text-[11px] text-slate-500 mb-3">Opcional para MiPymes. Si tienes RUT, cárgalo en formato PDF.</p>
          @if($profile?->rut_path)
            <input type="hidden" name="rut_exists" value="1">
          @endif
          <input type="file" name="rut_file" accept=".pdf"
            class="w-full text-xs text-slate-700 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:font-bold file:bg-slate-700 file:text-white hover:file:opacity-90 file:cursor-pointer">
          @error('rut_file') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
          @if($profile?->rut_path)
            <p class="text-xs mt-3 text-slate-700 font-bold flex items-center gap-1">
              <span class="material-symbols-outlined text-[15px] text-[#006c47]">check_circle</span> RUT cargado
            </p>
          @endif
        </div>
        
        <!-- Cámara de Comercio (Opcional para Chulito Oficial) -->
        <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-200">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider">Cámara de Comercio</label>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Para Chulito ✓</span>
          </div>
          <p class="text-[11px] text-slate-500 mb-3">Opcional. Súbelo para obtener el Sello Oficial de Comercio Verificado.</p>
          @if($profile?->camara_comercio_path)
            <input type="hidden" name="camara_comercio_exists" value="1">
          @endif
          <input type="file" name="camara_comercio_file" accept=".pdf,image/*"
            class="w-full text-xs text-slate-700 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:font-bold file:bg-blue-700 file:text-white hover:file:opacity-90 file:cursor-pointer">
          @error('camara_comercio_file') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
          @if($profile?->camara_comercio_path)
            <p class="text-xs mt-3 text-blue-800 font-bold flex items-center gap-1">
              <span class="material-symbols-outlined text-[15px] text-blue-600">verified</span> Cámara de Comercio cargada
            </p>
          @endif
        </div>
      </div>
    </div>

    <!-- Branding -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-surface-container">
      <h2 class="font-bold text-on-surface mb-6 flex items-center gap-2 pb-4 border-b border-surface-container">
        <span class="material-symbols-outlined text-primary text-[20px]">palette</span> Imagen de Marca
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-3">Logo de la Empresa</label>
          @if($profile?->logo_path)
            <div class="w-20 h-20 rounded-2xl overflow-hidden border border-surface-container mb-3">
              <img loading="lazy" decoding="async" src="{{ $profile->logoUrl() }}" class="w-full h-full object-cover">
            </div>
          @endif
          <input type="file" name="logo" accept="image/*"
            class="w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:font-semibold file:bg-surface-container-low file:text-on-surface hover:file:opacity-90">
          @error('logo') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-3">Banners (múltiples)</label>
          @if($profile?->banners_path && is_array($profile->banners_path))
            <div class="flex gap-2 flex-wrap mb-3">
              @foreach($profile->banners_path as $bPath)
                @php $path = is_array($bPath) ? ($bPath['path'] ?? '') : $bPath; @endphp
                @if($path)
                  <img loading="lazy" decoding="async" src="{{ Storage::url($path) }}" class="w-14 h-10 rounded-lg object-cover border border-surface-container">
                @endif
              @endforeach
            </div>
          @endif
          <input type="file" name="banners[]" accept="image/*" multiple
            class="w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:font-semibold file:bg-surface-container-low file:text-on-surface hover:file:opacity-90">
          @error('banners') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
          @error('banners.*') <p class="text-xs text-[#ba1a1a] mt-1">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    <!-- Configuración de Método de Pago Preferido (Banco o Nequi/Daviplata) -->
    @include('merchant.partials.payout-method-fields', ['profile' => $profile])

    <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4">
      @if(!$profile || $profile->kyc_status !== 'approved')
        <a href="#" onclick="event.preventDefault(); fetch('{{ route('logout') }}', { method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} }).then(() => window.location.href='{{ route('register') }}');" class="btn-secondary w-full sm:w-auto">
          <span class="material-symbols-outlined text-sm">arrow_back</span> Volver al registro
        </a>
      @else
        <a href="{{ route('merchant.dashboard') }}" class="btn-secondary w-full sm:w-auto">
          <span class="material-symbols-outlined text-sm">arrow_back</span> Volver al panel
        </a>
      @endif
      <button type="submit" class="px-8 py-4 bg-greenhouse-gradient text-white font-bold rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 active:scale-97 transition-all flex items-center gap-2">
        <span class="material-symbols-outlined text-[20px]">save</span>
        {{ $profile ? 'Actualizar Perfil' : 'Enviar para Aprobación' }}
      </button>
    </div>
  </form>
</div>

<script>
const merchantBusinessName = document.getElementById('merchant-business-name');
merchantBusinessName?.addEventListener('input', function() {
  const name = this.value.trim();
  const suggested = name
    ? (/\sS\.?A\.?S\.?$/i.test(name) ? name : `${name} S.A.S.`)
    : 'Nombre del negocio S.A.S.';
  const preview = document.getElementById('merchant-legal-name-preview');
  const input = document.getElementById('merchant-legal-name-input');
  if (preview) preview.textContent = suggested;
  if (input) input.value = suggested;
});

document.querySelector('form').onsubmit = function() {
  const btn = this.querySelector('button[type="submit"]');
  btn.disabled = true;
  btn.classList.add('opacity-70', 'cursor-not-allowed');
  btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span> Guardando...';
};
</script>
@endsection
