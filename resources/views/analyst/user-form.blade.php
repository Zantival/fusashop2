@extends('layouts.app')
@section('title', $user ? 'Editar Usuario' : 'Nuevo Usuario')
@section('content')
<div class="max-w-3xl mx-auto px-6 py-8 space-y-6">
  <div class="flex items-center gap-4">
    <a href="{{ route('analyst.dashboard') }}" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm hover:bg-[#f0eded] transition-colors">
      <span class="material-symbols-outlined text-[#3c4a41]">arrow_back</span>
    </a>
    <div>
      <h1 class="text-2xl md:text-3xl font-['Manrope'] font-extrabold text-[#1b1c1c]">
        {{ $user && $user->id === auth()->id() ? 'Mi Perfil de Administrador' : ($user ? 'Editar Usuario' : 'Nuevo Usuario') }}
      </h1>
      <p class="text-xs text-slate-500">Personaliza tu foto de perfil, datos de acceso y credenciales seguras</p>
    </div>
  </div>

  @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-[#006c47] px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
      <span class="material-symbols-outlined text-lg">check_circle</span>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
      <span class="material-symbols-outlined text-lg">error</span>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="bg-red-50 text-red-700 px-4 py-3 rounded-xl text-sm">
      @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
  @endif

  <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-200">
    <form method="POST" action="{{ $user ? route('analyst.users.update', $user->id) : route('analyst.users.store') }}" enctype="multipart/form-data" class="space-y-6">
      @csrf
      @if($user) @method('PUT') @endif

      <!-- Selector de Imagen / Avatar con Previsualización Instantánea -->
      <div class="flex flex-col sm:flex-row items-center gap-5 p-4 bg-emerald-50/50 border border-emerald-200/70 rounded-2xl">
        <div class="relative shrink-0">
          @if($user && $user->avatarUrl())
            <img loading="lazy" decoding="async" id="admin-avatar-preview" src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full object-cover border-2 border-[#006c47] shadow-md">
          @else
            <div id="admin-avatar-placeholder" class="w-20 h-20 rounded-full bg-gradient-to-tr from-[#006c47] to-[#10B981] text-white flex items-center justify-center font-black text-2xl shadow-md">
              {{ strtoupper(substr($user?->name ?? 'A', 0, 1)) }}
            </div>
            <img loading="lazy" decoding="async" id="admin-avatar-preview" src="" alt="Preview" style="display: none;" class="w-20 h-20 rounded-full object-cover border-2 border-[#006c47] shadow-md">
          @endif
        </div>
        <div class="flex-1 text-center sm:text-left">
          <label class="block text-sm font-extrabold text-slate-900 mb-1">Foto de Perfil (Se mostrará en el círculo del panel superior)</label>
          <p class="text-xs text-slate-500 mb-2.5">Formatos permitidos: JPG, PNG o WEBP (Máx. 4 MB)</p>
          <input type="file" name="avatar" accept="image/*"
                 onchange="if(this.files && this.files[0]){ const r = new FileReader(); r.onload = function(e){ const img = document.getElementById('admin-avatar-preview'); const ph = document.getElementById('admin-avatar-placeholder'); if(img){ img.src = e.target.result; img.style.display = 'block'; } if(ph){ ph.style.display = 'none'; } }; r.readAsDataURL(this.files[0]); }"
                 class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-[#006c47] file:text-white hover:file:bg-[#10B981] cursor-pointer"/>
        </div>
      </div>

      @php
        $selectedAdminRole = old('role', $user?->role ?? 'consumer');
      @endphp
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div id="analyst-company-name-wrap" class="sm:col-span-2 {{ $selectedAdminRole === 'merchant' ? '' : 'hidden' }}">
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Nombre del negocio / empresa *</label>
          <input type="text" id="analyst-company-name" name="company_name" value="{{ old('company_name', $user?->companyProfile?->company_name) }}"
            oninput="const v=this.value.trim();const el=document.getElementById('analyst-legal-name-preview');if(el)el.textContent=v?(/\sS\.?A\.?S\.?$/i.test(v)?v:v+' S.A.S.'):'Nombre del negocio S.A.S.';"
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="Ej. Café Sumapaz"/>
          <p class="mt-1 text-xs text-slate-500">Nombre público de la tienda. Razón social automática: <strong id="analyst-legal-name-preview" class="text-[#006c47]">{{ \App\Models\CompanyProfile::suggestedLegalName(old('company_name', $user?->companyProfile?->company_name ?: 'Nombre del negocio')) }}</strong></p>
        </div>
        <div class="sm:col-span-2">
          <label id="analyst-user-name-label" class="block text-sm font-semibold text-[#1b1c1c] mb-2">{{ $selectedAdminRole === 'merchant' ? 'Nombre del representante legal *' : 'Nombre completo *' }}</label>
          <input type="text" name="name" value="{{ old('name', $user?->name) }}" required
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="Nombre y apellido"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Correo electrónico *</label>
          <input type="email" name="email" value="{{ old('email', $user?->email) }}" required
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="correo@ejemplo.com"/>
        </div>

        <!-- §2.2: Si edita su propio perfil, no puede cambiar su rol aquí -->
        @if($user && $user->id === auth()->id())
          <div>
            <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Rol Asignado (Protegido)</label>
            <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-extrabold text-[#006c47] flex items-center gap-2">
              <span class="material-symbols-outlined text-base">verified_user</span>
              {{ $user->adminSubroleLabel() }} (Rol bloqueado en perfil propio)
            </div>
          </div>
        @else
          <div>
            <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Rol Principal *</label>
            <select id="analyst-role-select" name="role" required onchange="const m=this.value==='merchant';document.getElementById('analyst-company-name-wrap')?.classList.toggle('hidden',!m);const lbl=document.getElementById('analyst-user-name-label');if(lbl)lbl.textContent=m?'Nombre del representante legal *':'Nombre completo *';" class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]">
              @foreach(['consumer'=>'Consumidor','merchant'=>'Comerciante','analyst'=>'Analista / Admin'] as $val=>$label)
                <option value="{{ $val }}" {{ old('role',$user?->role)===$val?'selected':'' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-[#1b1c1c] mb-2">Sub-rol Granular (Solo si es Admin)</label>
            <select name="admin_subrole" class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]">
              @foreach(['super_admin'=>'Super Admin (Control Total)','analyst_readonly'=>'Analista (Solo Lectura y Reportes)','support'=>'Soporte (PQRS + KYC)'] as $sVal=>$sLbl)
                <option value="{{ $sVal }}" {{ old('admin_subrole', $user?->admin_subrole ?? 'super_admin')===$sVal?'selected':'' }}>{{ $sLbl }}</option>
              @endforeach
            </select>
          </div>
        @endif

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
            class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="12+ caracteres, mayúscula, número y símbolo" minlength="12"/>
          <p class="mt-1 text-xs text-slate-500">Debe incluir mayúsculas, minúsculas, un número y un símbolo.</p>
        </div>
      </div>

      <div class="flex gap-4 pt-2">
        <button type="submit" class="flex-1 py-3.5 bg-gradient-to-r from-[#006c47] to-[#00b67a] text-white font-semibold rounded-xl hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-md cursor-pointer">
          <span class="material-symbols-outlined text-sm">{{ $user ? 'save' : 'person_add' }}</span>
          {{ $user ? 'Guardar cambios y foto de perfil' : 'Crear usuario' }}
        </button>
        <a href="{{ route('analyst.dashboard') }}" class="px-6 py-3.5 bg-[#f0eded] text-[#1b1c1c] font-semibold rounded-xl hover:bg-[#e5e2e1] transition-colors text-center">
          Volver al Panel
        </a>
      </div>
    </form>

    {{-- APARTADO EXCLUSIVO PARA VENDEDORES: AUDITORÍA DOCUMENTAL Y CONTROL DEL CHULITO DE VERIFICACIÓN --}}
    @if($user && $user->role === 'merchant')
      @php
        $merchantProfile = $user->companyProfile;
        $currentKycStatus = $merchantProfile?->kyc_status ?? 'pending';
        $isVerifiedMerchant = $currentKycStatus === 'approved';
        $companyName = $merchantProfile?->company_name ?: $user->name;
        $legalName = $merchantProfile?->legal_name ?? \App\Models\CompanyProfile::suggestedLegalName($companyName);
      @endphp

      <div class="mt-10 pt-8 border-t-2 border-slate-200 space-y-6">

        {{-- Banner Informativo del Apartado de Vendedores --}}
        <div class="p-5 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50/40 rounded-2xl border border-emerald-200 flex items-start gap-4 shadow-xs">
          <div class="w-12 h-12 rounded-2xl bg-[#006c47] text-white flex items-center justify-center shrink-0 shadow-xs">
            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1">verified_user</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="text-base font-extrabold text-slate-900">Apartado Especial: Auditoría y Verificación de Comerciante (KYC)</h2>
              @if($isVerifiedMerchant)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-[#006c47] border border-emerald-300">
                  <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1">verified</span>
                  Chulito Activo
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                  Sin chulito de verificación
                </span>
              @endif
            </div>
            <p class="text-xs text-slate-600 mt-1">
              Antes de otorgar el chulito oficial de verificación, inspecciona cuidadosamente el Formulario RUT DIAN y el Certificado de Cámara de Comercio. Verifica la coincidencia con el Representante Legal.
            </p>
          </div>
        </div>

        {{-- Datos Corporativos y Estado Actual --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Datos del Negocio / Empresa</p>
            <p class="text-base font-extrabold text-slate-900">{{ $companyName }}</p>
            <p class="text-xs text-slate-700">Razón social legal: <strong class="text-slate-900">{{ $legalName }}</strong></p>
            <p class="text-xs text-slate-700">Representante legal: <strong class="text-[#006c47]">{{ $user->name }}</strong></p>
            <p class="text-xs text-slate-600">Actividad declarada: {{ $merchantProfile?->business_type ?? 'Comercio Local' }}</p>
            <p class="text-xs text-slate-600">Tel: {{ $merchantProfile?->phone ?? $user->phone ?? 'Sin teléfono' }} · Dir: {{ $merchantProfile?->address ?? 'Fusagasugá' }}</p>
          </div>

          <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado de Verificación Actual</p>
            @php
              $statusMap = [
                'pending'  => ['bg-amber-100 text-amber-900 border-amber-300', '⏳ Sin verificar / Pendiente de revisión'],
                'approved' => ['bg-emerald-100 text-[#006c47] border-emerald-300', '✓ Aprobado y con Chulito Oficial'],
                'rejected' => ['bg-rose-100 text-rose-800 border-rose-300', '❌ Verificación Rechazada']
              ];
              $s = $statusMap[$currentKycStatus] ?? $statusMap['pending'];
            @endphp
            <div class="inline-block px-3 py-1 rounded-full text-xs font-extrabold border {{ $s[0] }}">
              {{ $s[1] }}
            </div>
            @if($merchantProfile?->kyc_notes)
              <p class="text-xs text-slate-600 italic bg-white p-2 rounded-lg border border-slate-200">
                Observación registrada: {{ $merchantProfile->kyc_notes }}
              </p>
            @endif
            <p class="text-[11px] text-slate-500">
              {{ $isVerifiedMerchant
                  ? 'El comercio cuenta con la insignia de confianza en su tienda pública y catálogo.'
                  : 'El comercio no tiene la insignia activa hasta que completes la auditoría documental.' }}
            </p>
          </div>
        </div>

        {{-- Inspección de Documentos (RUT DIAN y Cámara de Comercio) --}}
        <div class="space-y-2">
          <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Documentos de Identificación y Formalidad Mercantil</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- RUT DIAN --}}
            @if($merchantProfile && $merchantProfile->rut_path)
              <a href="{{ route('analyst.users.kyc-document', [$user->id, 'rut']) }}" target="_blank" rel="noopener"
                 class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-xl hover:border-red-400 hover:shadow-xs transition-all group">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-2xl">picture_as_pdf</span>
                  </div>
                  <div>
                    <span class="text-xs font-extrabold text-slate-900 block group-hover:text-red-700">Ver RUT Privado (DIAN)</span>
                    <span class="text-[10px] text-slate-400">Formulario 001 · Acceso auditado Ley 1581</span>
                  </div>
                </div>
                <span class="material-symbols-outlined text-slate-400 group-hover:text-red-600 text-lg">open_in_new</span>
              </a>
            @else
              <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-2xl">pending</span>
                </div>
                <div>
                  <span class="text-xs font-extrabold text-amber-950 block">RUT no adjuntado</span>
                  <span class="text-[10px] text-amber-700">El comerciante aún no ha cargado su Formulario 001</span>
                </div>
              </div>
            @endif

            {{-- Cámara de Comercio --}}
            @if($merchantProfile && $merchantProfile->camara_comercio_path)
              <a href="{{ route('analyst.users.kyc-document', [$user->id, 'camara_comercio']) }}" target="_blank" rel="noopener"
                 class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-400 hover:shadow-xs transition-all group">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-2xl">description</span>
                  </div>
                  <div>
                    <span class="text-xs font-extrabold text-slate-900 block group-hover:text-blue-700">Ver Cámara de Comercio</span>
                    <span class="text-[10px] text-slate-400">Certificado de Existencia y Representación</span>
                  </div>
                </div>
                <span class="material-symbols-outlined text-slate-400 group-hover:text-blue-600 text-lg">open_in_new</span>
              </a>
            @else
              <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
                <div>
                  <span class="text-xs font-extrabold text-amber-950 block">Cámara de Comercio no adjuntada</span>
                  <span class="text-[10px] text-amber-700">El comercio no ha subido su certificado mercantil</span>
                </div>
              </div>
            @endif

            {{-- Cotejo RUES Nacional --}}
            <a href="https://www.rues.org.co/" target="_blank" rel="noopener noreferrer"
               class="md:col-span-2 flex items-center justify-between p-3.5 bg-blue-50/60 border border-blue-200 rounded-xl hover:border-blue-500 transition-all group">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-blue-600 text-xl">search</span>
                <div>
                  <span class="text-xs font-extrabold text-blue-950 block">Consultar RUES Nacional Oficial</span>
                  <span class="text-[10px] text-blue-700">Validar matrícula activa en la red de Cámaras de Comercio de Colombia</span>
                </div>
              </div>
              <span class="material-symbols-outlined text-blue-500 text-lg">open_in_new</span>
            </a>
          </div>
        </div>

        {{-- Formulario de Decisión KYC (Aprobar Chulito o Rechazar con Motivo) --}}
        <form method="POST" action="{{ route('analyst.users.kyc', $user->id) }}" class="p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
          @csrf
          <div class="p-3.5 bg-white border border-slate-200 rounded-xl text-xs space-y-2">
            <span class="font-extrabold text-slate-800 block">🛡️ Protocolo de Certificación Segura de Comerciante:</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] text-slate-600">
              <span class="flex items-center gap-1.5 font-medium">✓ RUT DIAN cotejado con NIT y CIIU</span>
              <span class="flex items-center gap-1.5 font-medium">✓ Matrícula mercantil activa en RUES</span>
              <span class="flex items-center gap-1.5 font-medium">✓ Cédula del Representante Legal cotejada</span>
              <span class="flex items-center gap-1.5 font-medium">✓ Titularidad de cuenta bancaria coincidente</span>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-extrabold text-slate-700 mb-1">Causal Estandarizada (Si rechaza)</label>
              <select name="kyc_rejection_code" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                <option value="">— Seleccionar motivo predefinido —</option>
                <option value="rut_ilegible">RUT ilegible o incompleto</option>
                <option value="nit_no_coincide">NIT o razón social no coincide</option>
                <option value="documento_vencido">Documento desactualizado (>30 días)</option>
                <option value="actividad_ciiu_invalida">Código CIIU no corresponde al rubro</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-extrabold text-slate-700 mb-1">Observaciones para el Comerciante</label>
              <input type="text" name="kyc_notes" placeholder="Detalle aclaratorio para el comercio..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs">
            </div>
          </div>

          <div class="flex flex-wrap gap-3 pt-1">
            <button type="submit" name="kyc_status" value="approved"
                    class="flex-1 min-w-[200px] py-3 bg-[#006c47] hover:bg-[#10B981] text-white font-extrabold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm">
              <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">verified</span>
              <span>Dar Chulito de Verificación (Aprobar KYC)</span>
            </button>

            @if($isVerifiedMerchant)
              <button type="submit" name="kyc_status" value="pending"
                      class="flex-1 min-w-[160px] py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-base">verified_off</span>
                <span>Quitar Verificación</span>
              </button>
            @endif

            <button type="submit" name="kyc_status" value="rejected"
                    class="flex-1 min-w-[160px] py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
              <span class="material-symbols-outlined text-base">cancel</span>
              <span>Rechazar con Motivo</span>
            </button>
          </div>
        </form>

        {{-- Corrección Manual del Rubro Sectorial --}}
        @if($merchantProfile)
          <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
              <span class="material-symbols-outlined text-[#006c47]">category</span>
              Clasificación Sectorial y Subsector (Corrección Administrativa)
            </h3>
            <form method="POST" action="{{ route('analyst.users.sector', $user->id) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
              @csrf
              <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Sector Principal</label>
                <select name="sector_override" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold">
                  @foreach(\App\Services\Admin\SectorCensusService::definitions() as $sKey => $def)
                    <option value="{{ $sKey }}" {{ ($merchantProfile->sector_override ?? '') === $sKey ? 'selected' : '' }}>{{ $def['title'] }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Subsector / Especialidad</label>
                <input type="text" name="subsector_override" value="{{ $merchantProfile->subsector_override }}" placeholder="Ej: Belleza, Agrícola, Glamping..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
              </div>
              <button type="submit" class="px-4 py-2 bg-[#006c47] text-white font-bold rounded-xl text-xs hover:bg-[#10B981] cursor-pointer">
                Guardar Rubro
              </button>
            </form>
          </div>
        @endif

      </div>
    @endif
  </div>
</div>
@endsection
