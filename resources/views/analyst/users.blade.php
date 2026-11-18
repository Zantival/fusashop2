@extends('layouts.app')
@section('title','Gestión de Usuarios & Verificación KYC')
@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8 space-y-6">

  @php
    try {
      $pendingKycCount = \App\Models\CompanyProfile::where('kyc_status', 'pending')->count();
      $verifiedMerchantsCount = \App\Models\CompanyProfile::where('kyc_status', 'approved')->count();
    } catch (\Throwable $e) {
      $pendingKycCount = 0;
      $verifiedMerchantsCount = 0;
    }
    $currentRole = request('role');
    $currentKyc = request('kyc_status');
  @endphp

  {{-- Encabezado con estadísticas rápidas --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl md:text-3xl font-['Manrope'] font-extrabold text-[#1b1c1c] flex items-center gap-2">
        <span class="material-symbols-outlined text-[#006c47] text-3xl">manage_accounts</span>
        Gestión de Usuarios & Verificación KYC
      </h1>
      <p class="text-xs text-slate-500 mt-1">
        Supervisión de cuentas, auditoría de RUT y Cámara de Comercio para otorgar el chulito oficial de verificación.
      </p>
    </div>
    <div class="flex items-center gap-2.5">
      <a href="{{ route('analyst.users.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-[#006c47] to-[#00b67a] text-white font-bold rounded-xl hover:opacity-90 transition-all flex items-center gap-2 shadow-md text-xs cursor-pointer">
        <span class="material-symbols-outlined text-base">person_add</span>
        <span>Nuevo Usuario</span>
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-[#006c47] px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs">
      <span class="material-symbols-outlined text-base">check_circle</span>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs">
      <span class="material-symbols-outlined text-base">error</span>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  {{-- Pestañas de Navegación Rápida por Rol / Estado KYC --}}
  <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
    <a href="{{ route('analyst.users') }}"
       class="px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 flex items-center gap-1.5 {{ empty($currentRole) && empty($currentKyc) ? 'bg-[#006c47] text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
      <span class="material-symbols-outlined text-base">group</span>
      <span>Todos los Usuarios</span>
    </a>
    <a href="{{ route('analyst.users', ['role' => 'merchant', 'kyc_status' => 'pending']) }}"
       class="px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $currentRole === 'merchant' && $currentKyc === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100' }}">
      <span class="material-symbols-outlined text-base">pending_actions</span>
      <span>⏳ KYC Pendientes por Verificar</span>
      @if($pendingKycCount > 0)
        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $currentRole === 'merchant' && $currentKyc === 'pending' ? 'bg-white text-amber-700' : 'bg-amber-200 text-amber-900' }}">
          {{ $pendingKycCount }}
        </span>
      @endif
    </a>
    <a href="{{ route('analyst.users', ['role' => 'merchant']) }}"
       class="px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $currentRole === 'merchant' && empty($currentKyc) ? 'bg-[#006c47] text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
      <span class="material-symbols-outlined text-base">storefront</span>
      <span>🏪 Todos los Vendedores</span>
    </a>
    <a href="{{ route('analyst.users', ['role' => 'merchant', 'kyc_status' => 'approved']) }}"
       class="px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $currentRole === 'merchant' && $currentKyc === 'approved' ? 'bg-[#006c47] text-white shadow-xs' : 'bg-emerald-50 text-[#006c47] border border-emerald-200 hover:bg-emerald-100' }}">
      <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">verified</span>
      <span>✓ Vendedores Verificados ({{ $verifiedMerchantsCount }})</span>
    </a>
    <a href="{{ route('analyst.users', ['role' => 'consumer']) }}"
       class="px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $currentRole === 'consumer' ? 'bg-[#006c47] text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
      <span class="material-symbols-outlined text-base">person</span>
      <span>👤 Consumidores</span>
    </a>
    <a href="{{ route('analyst.users', ['role' => 'analyst']) }}"
       class="px-3.5 py-2 rounded-xl font-bold transition-all shrink-0 flex items-center gap-1.5 {{ $currentRole === 'analyst' ? 'bg-[#006c47] text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
      <span class="material-symbols-outlined text-base">security</span>
      <span>🛡️ Administradores</span>
    </a>
  </div>

  {{-- APARTADO DEDICADO DE AUDITORÍA PREVIA KYC (Para Comerciantes Pendientes) --}}
  @if(isset($kycQueue) && $kycQueue->isNotEmpty() && (empty($currentRole) || $currentRole === 'merchant'))
    <div class="bg-gradient-to-br from-amber-50/90 via-orange-50/50 to-white rounded-2xl border-2 border-amber-300 p-5 shadow-sm space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-amber-200 pb-3">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-xs shrink-0">
            <span class="material-symbols-outlined text-xl">verified_user</span>
          </div>
          <div>
            <h2 class="text-base font-extrabold text-amber-950">
              Apartado Especial: Comerciantes Pendientes de Verificación ({{ $kycQueue->count() }})
            </h2>
            <p class="text-xs text-amber-800">
              Antes de otorgar el chulito de verificación, inspecciona el RUT DIAN y el Certificado de Cámara de Comercio.
            </p>
          </div>
        </div>
        <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-200 text-amber-900 rounded-full text-xs font-black self-start sm:self-auto">
          ⚠️ Requiere Revisión Previa
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($kycQueue as $profile)
          @php
            $qUser = $profile->user;
          @endphp
          @if($qUser)
            <div class="bg-white rounded-xl border border-amber-200/90 p-4 shadow-xs space-y-3">
              {{-- Cabecera del Comercio --}}
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <span class="text-[10px] font-black uppercase text-amber-700 tracking-wider">Tienda / Empresa</span>
                  <h3 class="font-extrabold text-slate-900 text-base truncate">{{ $profile->company_name ?: $qUser->name }}</h3>
                  <p class="text-xs text-slate-500">
                    Razón Social: <strong class="text-slate-800">{{ $profile->legal_name ?? \App\Models\CompanyProfile::suggestedLegalName($profile->company_name ?: $qUser->name) }}</strong>
                  </p>
                  <p class="text-xs text-slate-600">
                    Representante Legal: <strong class="text-[#006c47]">{{ $qUser->name }}</strong>
                  </p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300 shrink-0">
                  ⏳ Pendiente
                </span>
              </div>

              {{-- Contacto y Ubicación --}}
              <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                <div class="truncate">
                  <span class="text-slate-400 block text-[10px]">Correo:</span>
                  <span class="font-semibold text-slate-800">{{ $qUser->email }}</span>
                </div>
                <div class="truncate">
                  <span class="text-slate-400 block text-[10px]">Teléfono:</span>
                  <span class="font-semibold text-slate-800">{{ $profile->phone ?? $qUser->phone ?? 'Sin teléfono' }}</span>
                </div>
                <div class="col-span-2 truncate">
                  <span class="text-slate-400 block text-[10px]">Dirección & Rubro:</span>
                  <span class="font-semibold text-slate-800">{{ $profile->address ?? 'Fusagasugá' }} · {{ $profile->business_type ?? 'Comercio Local' }}</span>
                </div>
              </div>

              {{-- Botones de Inspección Documental (RUT y Cámara de Comercio) --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @if($profile->rut_path)
                  <a href="{{ route('analyst.users.kyc-document', [$qUser->id, 'rut']) }}" target="_blank" rel="noopener"
                     class="flex items-center justify-between px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl text-xs font-bold transition-all">
                    <span class="flex items-center gap-1.5 truncate">
                      <span class="material-symbols-outlined text-base text-red-600">picture_as_pdf</span>
                      <span class="truncate">Ver RUT DIAN</span>
                    </span>
                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                  </a>
                @else
                  <div class="flex items-center gap-1.5 px-3 py-2 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl text-xs font-bold">
                    <span class="material-symbols-outlined text-base text-rose-600">error</span>
                    <span>Sin RUT cargado</span>
                  </div>
                @endif

                @if($profile->camara_comercio_path)
                  <a href="{{ route('analyst.users.kyc-document', [$qUser->id, 'camara_comercio']) }}" target="_blank" rel="noopener"
                     class="flex items-center justify-between px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition-all">
                    <span class="flex items-center gap-1.5 truncate">
                      <span class="material-symbols-outlined text-base text-blue-600">description</span>
                      <span class="truncate">Ver Cámara de Comercio</span>
                    </span>
                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                  </a>
                @else
                  <div class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold">
                    <span class="material-symbols-outlined text-base text-amber-600">warning</span>
                    <span>Sin Cámara Comercio</span>
                  </div>
                @endif
              </div>

              {{-- Botón Consulta RUES y Formulario de Decisión --}}
              <div class="pt-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <a href="https://www.rues.org.co/" target="_blank" rel="noopener noreferrer"
                   class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold flex items-center justify-center gap-1 transition-all"
                   title="Consultar registro mercantil nacional">
                  <span class="material-symbols-outlined text-sm">search</span> RUES Oficial
                </a>

                <form method="POST" action="{{ route('analyst.users.kyc', $qUser->id) }}" class="flex-1 flex gap-2">
                  @csrf
                  <button type="submit" name="kyc_status" value="approved"
                          class="flex-1 py-2 px-3 bg-[#006c47] hover:bg-[#10B981] text-white font-extrabold rounded-xl text-xs transition-all flex items-center justify-center gap-1 cursor-pointer shadow-xs">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1">verified</span>
                    <span>Dar Chulito ✓</span>
                  </button>
                  <a href="{{ route('analyst.users.edit', $qUser->id) }}"
                     class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-sm">edit</span> Auditar / Editar
                  </a>
                </form>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  @endif

  {{-- Buscador y Filtros Avanzados --}}
  <form method="GET" class="flex gap-2 flex-wrap bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, tienda, representante legal o email..."
      class="flex-1 min-w-48 px-4 py-2.5 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-[#006c47] text-xs font-medium"/>
    <select name="role" class="px-3 py-2.5 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-[#006c47] text-xs font-semibold">
      <option value="">Todos los roles</option>
      <option value="consumer" {{ request('role')==='consumer'?'selected':'' }}>Consumidor</option>
      <option value="merchant" {{ request('role')==='merchant'?'selected':'' }}>Comerciante / Vendedor</option>
      <option value="analyst" {{ request('role')==='analyst'?'selected':'' }}>Analista / Admin</option>
    </select>
    <select name="kyc_status" class="px-3 py-2.5 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-[#006c47] text-xs font-semibold">
      <option value="">Estado Verificación (Todos)</option>
      <option value="approved" {{ request('kyc_status')==='approved'?'selected':'' }}>✓ Con Chulito de Verificación</option>
      <option value="pending" {{ request('kyc_status')==='pending'?'selected':'' }}>⏳ Pendientes de Auditoría</option>
      <option value="rejected" {{ request('kyc_status')==='rejected'?'selected':'' }}>❌ Rechazados</option>
    </select>
    <button type="submit" class="px-5 py-2.5 bg-[#006c47] hover:bg-[#10B981] text-white font-bold rounded-xl text-xs transition-all cursor-pointer">
      Buscar
    </button>
    @if(request()->anyFilled(['search','role','kyc_status']))
      <a href="{{ route('analyst.users') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center justify-center">
        Limpiar Filtros
      </a>
    @endif
  </form>

  {{-- Lista de Usuarios General --}}
  <div class="space-y-4">
    @forelse($users as $u)
      @php
        $rCls = [
          'consumer' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
          'merchant' => 'bg-emerald-100 text-[#006c47] border-emerald-300 font-extrabold',
          'analyst'  => 'bg-amber-100 text-amber-900 border-amber-300 font-extrabold',
        ][$u->role] ?? 'bg-slate-100 text-slate-700 border-slate-200';

        $isMerchantUser = $u->role === 'merchant';
        $merchantProfile = $u->companyProfile;
        $kycStatus = $merchantProfile?->kyc_status ?? 'pending';
        $isMerchantVerified = $isMerchantUser && $kycStatus === 'approved';
        $companyName = $merchantProfile?->company_name;
        $legalName = $merchantProfile?->legal_name ?? \App\Models\CompanyProfile::suggestedLegalName($companyName ?: $u->name);
        $personAvatarUrl = $u->avatarUrl();
        $companyLogoUrl = $isMerchantUser ? $merchantProfile?->logoUrl() : null;
        $primaryAvatarUrl = $personAvatarUrl ?: $companyLogoUrl;
        $hasSeparateStoreLogo = $personAvatarUrl && $companyLogoUrl && $personAvatarUrl !== $companyLogoUrl;
      @endphp

      <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden {{ $u->is_blocked ? 'opacity-70 bg-slate-50' : '' }}">

        {{-- Cabecera de la tarjeta --}}
        <div class="p-4 md:p-5 space-y-3">
          <div class="flex items-start gap-4">
            {{-- Avatar --}}
            <div class="relative w-12 h-12 shrink-0">
              @if($primaryAvatarUrl)
                <img loading="lazy" decoding="async" src="{{ $primaryAvatarUrl }}" data-fallback="{{ $personAvatarUrl && $companyLogoUrl ? $companyLogoUrl : '' }}" alt="{{ $isMerchantUser && !$personAvatarUrl ? $companyName : $u->name }}" class="w-12 h-12 rounded-full object-cover border-2 border-[#10B981] bg-white shadow-xs" onerror="if(this.dataset.fallback){this.src=this.dataset.fallback;this.dataset.fallback='';}else{this.remove();this.nextElementSibling?.classList.remove('hidden');}">
                <div class="hidden w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg {{ $u->is_blocked ? 'bg-slate-400' : 'bg-[#006c47]' }}">
                  {{ strtoupper(substr($u->name,0,1)) }}
                </div>
              @else
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg {{ $u->is_blocked ? 'bg-slate-400' : 'bg-[#006c47]' }}">
                  {{ strtoupper(substr($u->name,0,1)) }}
                </div>
              @endif
              @if($hasSeparateStoreLogo)
                <img loading="lazy" decoding="async" src="{{ $companyLogoUrl }}" alt="Logo de {{ $companyName }}" title="{{ $companyName }}" class="absolute -right-1 -bottom-1 w-6 h-6 rounded-full object-cover border-2 border-white bg-white shadow-xs" onerror="this.remove()">
              @endif
            </div>

            {{-- Información Primaria --}}
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-extrabold text-slate-900 text-base truncate flex items-center gap-1.5">
                  <span>{{ $isMerchantUser && $companyName ? $companyName : $u->name }}</span>
                  @if($isMerchantVerified)
                    <span class="material-symbols-outlined text-[#006c47] text-[18px] shrink-0" style="font-variation-settings: 'FILL' 1" title="Comercio Verificado con Chulito Oficial">verified</span>
                  @endif
                </span>

                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $rCls }}">
                  {{ $u->role === 'merchant' ? 'Vendedor / Comercio' : ucfirst($u->role) }}
                </span>

                @if($isMerchantUser)
                  @if($isMerchantVerified)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-[#006c47] border border-emerald-300">
                      <span class="material-symbols-outlined text-[13px]" style="font-variation-settings: 'FILL' 1">verified</span>
                      <span>Chulito Activo</span>
                    </span>
                  @elseif($kycStatus === 'rejected')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                      <span class="material-symbols-outlined text-[13px]">cancel</span>
                      <span>Verificación Rechazada</span>
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                      <span class="material-symbols-outlined text-[13px]">pending</span>
                      <span>Pendiente de Verificación</span>
                    </span>
                  @endif
                @endif

                @if($u->is_blocked)
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">🔒 Bloqueado</span>
                @else
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-[#006c47]">✓ Activo</span>
                @endif
              </div>

              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600">
                <span>{{ $u->email }}</span>
                <span>·</span>
                <span>{{ $u->phone ?: 'Sin teléfono' }}</span>
                <span>·</span>
                <span>Registrado: {{ $u->created_at?->format('d/m/Y') ?? 'Reciente' }}</span>
              </div>
            </div>
          </div>

          {{-- SECCIÓN DEDICADA DE DATOS Y DOCUMENTOS PARA VENDEDORES (KYC) --}}
          @if($isMerchantUser)
            <div class="mt-3 p-4 bg-slate-50/90 rounded-xl border border-slate-200 space-y-3">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/80 pb-2">
                <div>
                  <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block">Auditoría Previa al Chulito de Verificación</span>
                  <div class="text-xs text-slate-700">
                    Razón Social: <strong class="text-slate-900">{{ $legalName }}</strong> ·
                    Representante Legal: <strong class="text-[#006c47]">{{ $u->name }}</strong>
                  </div>
                </div>
                <div class="text-xs text-slate-600">
                  <span class="font-semibold text-slate-800">{{ $merchantProfile?->business_type ?? 'Comercio Local' }}</span> ·
                  <span>{{ $merchantProfile?->address ?? 'Fusagasugá' }}</span>
                </div>
              </div>

              {{-- Botones de Inspección de Documentos (RUT y Cámara de Comercio) --}}
              <div class="flex flex-wrap items-center gap-2 pt-0.5">
                <span class="text-xs font-bold text-slate-600 shrink-0">Documentos:</span>

                {{-- RUT DIAN --}}
                @if($merchantProfile && $merchantProfile->rut_path)
                  <a href="{{ route('analyst.users.kyc-document', [$u->id, 'rut']) }}" target="_blank" rel="noopener"
                     class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl text-xs font-bold transition-all"
                     title="Ver Formulario RUT DIAN (Abre en nueva pestaña)">
                    <span class="material-symbols-outlined text-[16px] text-red-600">picture_as_pdf</span>
                    <span>Ver RUT DIAN</span>
                    <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                  </a>
                @else
                  <span class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl text-xs font-semibold">
                    <span class="material-symbols-outlined text-[15px] text-rose-600">error</span>
                    <span>Sin RUT cargado</span>
                  </span>
                @endif

                {{-- Cámara de Comercio --}}
                @if($merchantProfile && $merchantProfile->camara_comercio_path)
                  <a href="{{ route('analyst.users.kyc-document', [$u->id, 'camara_comercio']) }}" target="_blank" rel="noopener"
                     class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition-all"
                     title="Ver Certificado de Cámara de Comercio (Abre en nueva pestaña)">
                    <span class="material-symbols-outlined text-[16px] text-blue-600">description</span>
                    <span>Ver Cámara de Comercio</span>
                    <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                  </a>
                @else
                  <span class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-xs font-semibold">
                    <span class="material-symbols-outlined text-[15px] text-amber-600">warning</span>
                    <span>Sin Cámara de Comercio</span>
                  </span>
                @endif

                {{-- RUES Oficial --}}
                <a href="https://www.rues.org.co/" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-xs font-medium transition-all"
                   title="Consultar registro mercantil en RUES Oficial Nacional">
                  <span class="material-symbols-outlined text-[15px] text-blue-600">search</span>
                  <span>RUES Nacional</span>
                  <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                </a>
              </div>
            </div>
          @endif
        </div>

        {{-- Barra de Acciones del Usuario --}}
        <div class="border-t border-slate-200 grid {{ $isMerchantUser ? 'grid-cols-2 sm:grid-cols-4' : 'grid-cols-3' }} divide-x divide-slate-200 bg-white">

          {{-- Editar --}}
          <a href="{{ route('analyst.users.edit', $u->id) }}"
             class="flex items-center justify-center gap-2 py-3 text-slate-700 hover:bg-[#006c47] hover:text-white transition-all font-bold text-xs">
            <span class="material-symbols-outlined text-[18px]">edit</span>
            <span>Editar Perfil</span>
          </a>

          {{-- Control del Chulito de Verificación (Comerciantes) --}}
          @if($isMerchantUser)
            <form method="POST" action="{{ route('analyst.users.toggle-verification', $u->id) }}" class="h-full">
              @csrf @method('PATCH')
              <button type="submit"
                class="w-full h-full flex items-center justify-center gap-1.5 py-3 font-extrabold text-xs transition-all cursor-pointer {{ $isMerchantVerified ? 'text-amber-700 hover:bg-amber-600 hover:text-white' : 'text-[#006c47] bg-emerald-50/70 hover:bg-[#006c47] hover:text-white' }}"
                title="{{ $isMerchantVerified ? 'Quitar el chulito de verificación a este comercio' : 'Otorgar el chulito de verificación a este comercio' }}">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' {{ $isMerchantVerified ? '0' : '1' }}">
                  {{ $isMerchantVerified ? 'verified_off' : 'verified' }}
                </span>
                <span>{{ $isMerchantVerified ? 'Quitar Chulito' : 'Dar Chulito ✓' }}</span>
              </button>
            </form>
          @endif

          @if($u->id !== auth()->id())
            {{-- Bloquear / Desbloquear --}}
            <form method="POST" action="{{ route('analyst.users.toggle-block', $u->id) }}"
                  onsubmit="return confirm('{{ $u->is_blocked ? '¿Desbloquear a '.$u->name.'?' : '¿Bloquear a '.$u->name.'?' }}')">
              @csrf @method('PATCH')
              <button type="submit"
                class="w-full h-full flex items-center justify-center gap-2 py-3 font-semibold text-xs transition-all cursor-pointer {{ $u->is_blocked ? 'text-amber-600 hover:bg-amber-500 hover:text-white' : 'text-orange-600 hover:bg-orange-500 hover:text-white' }}">
                <span class="material-symbols-outlined text-[18px]">{{ $u->is_blocked ? 'lock_open' : 'block' }}</span>
                <span>{{ $u->is_blocked ? 'Desbloquear' : 'Bloquear' }}</span>
              </button>
            </form>

            {{-- Eliminar --}}
            <form method="POST" action="{{ route('analyst.users.delete', $u->id) }}"
                  onsubmit="return confirm('¿Eliminar a {{ e($u->name) }}? Esta acción enviará al usuario a la papelera.')">
              @csrf @method('DELETE')
              <button type="submit"
                class="w-full h-full flex items-center justify-center gap-2 py-3 text-rose-600 hover:bg-rose-600 hover:text-white transition-all font-semibold text-xs cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                <span>Eliminar</span>
              </button>
            </form>
          @else
            <div class="flex items-center justify-center py-3 text-slate-400 text-xs col-span-2">
              — Cuenta Propia —
            </div>
          @endif

        </div>
      </div>
    @empty
      <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
        <span class="material-symbols-outlined text-5xl text-slate-300 block mb-3">group_off</span>
        <p class="text-slate-600 font-bold text-base">No se encontraron usuarios con los filtros indicados.</p>
      </div>
    @endforelse
  </div>

  {{-- Paginación --}}
  <div class="mt-6">{{ $users->links() }}</div>

</div>
@endsection
