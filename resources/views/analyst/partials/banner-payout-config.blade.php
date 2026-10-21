@php
  $bannerPayoutCfg = \App\Models\Setting::bannerPayoutConfig();
  $activeSnapshot = \App\Models\Setting::activeBannerPayoutSnapshot();
  $destMode = old('destination_mode', $bannerPayoutCfg['destination_mode'] ?? 'corporate');
  $customMethod = old('custom_method_type', $bannerPayoutCfg['custom_method_type'] ?? 'bank');
  $banksList = \App\Models\CompanyProfile::availableBanks();
@endphp

<div class="bg-white rounded-3xl p-6 shadow-sm border-2 border-[#006c47]/25 space-y-5 relative overflow-hidden">
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
    <div class="flex items-start gap-3.5">
      <div class="w-12 h-12 rounded-2xl bg-[#006c47] text-white flex items-center justify-center shrink-0 shadow-md">
        <span class="material-symbols-outlined text-2xl">account_balance</span>
      </div>
      <div>
        <div class="flex items-center gap-2 flex-wrap">
          <h2 class="text-base md:text-lg font-extrabold font-['Manrope'] text-slate-900">
            Destino de Ingresos por Banners y Publicidad
          </h2>
          <span class="px-3 py-0.5 rounded-full text-[11px] font-black {{ $activeSnapshot['payout_destination_mode'] === 'corporate' ? 'bg-emerald-100 text-[#006c47] border border-emerald-300' : 'bg-blue-100 text-blue-800 border border-blue-300' }}">
            Activo: {{ $activeSnapshot['mode_badge'] }}
          </span>
        </div>
        <p class="text-xs text-slate-600 mt-1">
          Configura hacia qué cuenta bancaria o billetera digital se enrutan los pagos realizados por los comerciantes al contratar o alquilar banners publicitarios y tarjetas promocionales.
        </p>
        <p class="text-[11px] font-bold text-[#006c47] mt-1 flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">verified</span>
          <span>Destino financiero actual en reportes: {{ $activeSnapshot['summary_label'] }} ({{ $activeSnapshot['payout_account_holder'] }})</span>
        </p>
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('analyst.banners.payout-config.update') }}" class="space-y-5">
    @csrf

    <!-- Selector Principal: Cuenta Corporativa Central vs Cuenta Específica del Analista -->
    <div>
      <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2.5">
        Selecciona el destino del recaudo publicitario *
      </label>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
        <!-- Opción 1: Cuenta Corporativa Central FusaShop -->
        <label class="cursor-pointer rounded-2xl border-2 p-4 flex items-start gap-3 transition-all"
               id="analyst-label-dest-corporate"
               onclick="switchBannerDestinationMode('corporate')">
          <input type="radio" name="destination_mode" value="corporate" id="analyst-radio-dest-corporate"
                 class="mt-1 text-[#006c47] focus:ring-[#006c47]"
                 {{ $destMode === 'corporate' ? 'checked' : '' }}
                 onchange="switchBannerDestinationMode('corporate')">
          <div class="min-w-0 flex-1">
            <span class="text-sm font-extrabold text-slate-900 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[#006c47] text-[18px]">domain</span>
              Cuenta Principal / Corporativa Central de FusaShop
            </span>
            <span class="text-xs text-slate-500 block mt-0.5">
              Enrutar todos los pagos de pauta publicitaria y alquiler de banners directamente a la cuenta central corporativa de la plataforma.
            </span>
          </div>
        </label>

        <!-- Opción 2: Cuenta o Método Específico Configurado por el Analista -->
        <label class="cursor-pointer rounded-2xl border-2 p-4 flex items-start gap-3 transition-all"
               id="analyst-label-dest-custom"
               onclick="switchBannerDestinationMode('custom')">
          <input type="radio" name="destination_mode" value="custom" id="analyst-radio-dest-custom"
                 class="mt-1 text-[#006c47] focus:ring-[#006c47]"
                 {{ $destMode === 'custom' ? 'checked' : '' }}
                 onchange="switchBannerDestinationMode('custom')">
          <div class="min-w-0 flex-1">
            <span class="text-sm font-extrabold text-slate-900 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-blue-600 text-[18px]">account_balance_wallet</span>
              Cuenta o Método Específico del Analista / Admin
            </span>
            <span class="text-xs text-slate-500 block mt-0.5">
              Asignar el recaudo publicitario a una cuenta bancaria (Ahorros/Corriente) o billetera digital (Nequi/Daviplata) configurada por el analista.
            </span>
          </div>
        </label>
      </div>
    </div>

    <!-- PANEL 1: CUENTA PRINCIPAL / CORPORATIVA CENTRAL DE FUSASHOP -->
    <div id="analyst-dest-corporate-panel" class="p-5 rounded-2xl bg-emerald-50/40 border border-emerald-200 space-y-4" style="display: {{ $destMode === 'corporate' ? 'block' : 'none' }};">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2 text-xs font-extrabold text-[#006c47]">
          <span class="material-symbols-outlined text-base">verified_user</span>
          <span>Parámetros de la Cuenta Corporativa Central de FusaShop</span>
        </div>
        <span class="text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full">
          Recaudo Institucional Directo
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-3.5">
        <div class="md:col-span-2">
          <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Entidad Bancaria Corporativa</label>
          <input type="text" name="corporate_bank_name"
                 value="{{ old('corporate_bank_name', $bannerPayoutCfg['corporate_bank_name'] ?? 'Bancolombia (Cuenta Corporativa Central FusaShop)') }}"
                 class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tipo de Cuenta</label>
          <select name="corporate_account_type"
                  class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]">
            <option value="Corriente" {{ old('corporate_account_type', $bannerPayoutCfg['corporate_account_type'] ?? 'Corriente') === 'Corriente' ? 'selected' : '' }}>Cuenta Corriente</option>
            <option value="Ahorros" {{ old('corporate_account_type', $bannerPayoutCfg['corporate_account_type'] ?? '') === 'Ahorros' ? 'selected' : '' }}>Cuenta de Ahorros</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Número de Cuenta Central</label>
          <input type="text" name="corporate_account_number"
                 value="{{ old('corporate_account_number', $bannerPayoutCfg['corporate_account_number'] ?? '031-998877-01') }}"
                 class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
        <div>
          <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Razón Social Titular</label>
          <input type="text" name="corporate_account_holder"
                 value="{{ old('corporate_account_holder', $bannerPayoutCfg['corporate_account_holder'] ?? 'FusaShop S.A.S. — Recaudo Central') }}"
                 class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">NIT Corporativo</label>
          <input type="text" name="corporate_nit"
                 value="{{ old('corporate_nit', $bannerPayoutCfg['corporate_nit'] ?? '901.456.789-1') }}"
                 class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]">
        </div>
      </div>
    </div>

    <!-- PANEL 2: CUENTA O MÉTODO ESPECÍFICO CONFIGURADO POR EL ANALISTA -->
    <div id="analyst-dest-custom-panel" class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4" style="display: {{ $destMode === 'custom' ? 'block' : 'none' }};">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200/80">
        <div class="flex items-center gap-2 text-xs font-extrabold text-slate-800">
          <span class="material-symbols-outlined text-base text-[#006c47]">tune</span>
          <span>Configurar Método de Pago Específico para Recaudo de Publicidad</span>
        </div>

        <div class="flex items-center gap-2">
          <label class="cursor-pointer px-3 py-1.5 rounded-xl border text-xs font-extrabold flex items-center gap-1.5 transition-all"
                 id="analyst-submethod-bank-label"
                 onclick="switchAnalystCustomMethod('bank')">
            <input type="radio" name="custom_method_type" value="bank" id="analyst-submethod-bank-radio"
                   {{ $customMethod === 'bank' ? 'checked' : '' }}
                   onchange="switchAnalystCustomMethod('bank')"
                   class="text-[#006c47]">
            <span class="material-symbols-outlined text-sm">account_balance</span>
            <span>Cuenta Bancaria</span>
          </label>

          <label class="cursor-pointer px-3 py-1.5 rounded-xl border text-xs font-extrabold flex items-center gap-1.5 transition-all"
                 id="analyst-submethod-wallet-label"
                 onclick="switchAnalystCustomMethod('wallet')">
            <input type="radio" name="custom_method_type" value="wallet" id="analyst-submethod-wallet-radio"
                   {{ $customMethod === 'wallet' ? 'checked' : '' }}
                   onchange="switchAnalystCustomMethod('wallet')"
                   class="text-[#006c47]">
            <span class="material-symbols-outlined text-sm">smartphone</span>
            <span>Nequi / Daviplata</span>
          </label>
        </div>
      </div>

      <!-- Sub-opción 2A: Cuenta Bancaria Tradicional del Analista -->
      <div id="analyst-custom-bank-fields" class="grid grid-cols-1 md:grid-cols-3 gap-3.5" style="display: {{ $customMethod === 'bank' ? 'grid' : 'none' }};">
        <div>
          <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Selecciona el Banco *</label>
          <select name="custom_bank_name"
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]">
            @foreach($banksList as $bOption)
              <option value="{{ $bOption }}" {{ old('custom_bank_name', $bannerPayoutCfg['custom_bank_name'] ?? 'Bancolombia') === $bOption ? 'selected' : '' }}>{{ $bOption }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tipo de Cuenta *</label>
          <select name="custom_account_type"
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]">
            <option value="Ahorros" {{ old('custom_account_type', $bannerPayoutCfg['custom_account_type'] ?? 'Ahorros') === 'Ahorros' ? 'selected' : '' }}>Cuenta de Ahorros</option>
            <option value="Corriente" {{ old('custom_account_type', $bannerPayoutCfg['custom_account_type'] ?? '') === 'Corriente' ? 'selected' : '' }}>Cuenta Corriente</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Número de Cuenta *</label>
          <input type="text" name="custom_account_number"
                 value="{{ old('custom_account_number', $bannerPayoutCfg['custom_account_number'] ?? '') }}"
                 inputmode="numeric"
                 class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]"
                 placeholder="Ej: 45678901234">
        </div>
      </div>

      <!-- Sub-opción 2B: Billetera Digital (Nequi / Daviplata) del Analista -->
      <div id="analyst-custom-wallet-fields" class="grid grid-cols-1 md:grid-cols-2 gap-3.5" style="display: {{ $customMethod === 'wallet' ? 'grid' : 'none' }};">
        <div>
          <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Billetera Digital *</label>
          <select name="custom_wallet_provider"
                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]">
            <option value="Nequi" {{ old('custom_wallet_provider', $bannerPayoutCfg['custom_wallet_provider'] ?? 'Nequi') === 'Nequi' ? 'selected' : '' }}>Nequi</option>
            <option value="Daviplata" {{ old('custom_wallet_provider', $bannerPayoutCfg['custom_wallet_provider'] ?? '') === 'Daviplata' ? 'selected' : '' }}>Daviplata</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Número de Teléfono Asociado (10 dígitos) *</label>
          <input type="tel" name="custom_wallet_phone"
                 value="{{ old('custom_wallet_phone', $bannerPayoutCfg['custom_wallet_phone'] ?? '') }}"
                 maxlength="10" inputmode="numeric"
                 class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]"
                 placeholder="Ej: 3109876543">
        </div>
      </div>

      <!-- Datos del Titular y Llaves Wompi Opcionales -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 pt-1">
        <div>
          <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nombre del Titular / Responsable *</label>
          <input type="text" name="custom_account_holder"
                 value="{{ old('custom_account_holder', $bannerPayoutCfg['custom_account_holder'] ?? auth()->user()->name) }}"
                 class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]"
                 placeholder="Nombre completo o área encargada">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Cédula / NIT del Titular</label>
          <input type="text" name="custom_id_number"
                 value="{{ old('custom_id_number', $bannerPayoutCfg['custom_id_number'] ?? '') }}"
                 class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-800 outline-none focus:ring-2 focus:ring-[#10B981]"
                 placeholder="Ej: 1003618196">
        </div>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <div class="text-[11px] text-slate-500">
        @if(!empty($bannerPayoutCfg['updated_at']))
          Última actualización: <strong>{{ $bannerPayoutCfg['updated_at'] }}</strong>
          @if(!empty($bannerPayoutCfg['updated_by'])) por <strong>{{ $bannerPayoutCfg['updated_by'] }}</strong> @endif
        @else
          Todos los pagos de banners quedarán asociados a este destino en los reportes financieros de publicidad.
        @endif
      </div>

      <button type="submit" class="px-6 py-2.5 bg-[#006c47] hover:bg-[#10B981] text-white font-extrabold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 text-xs cursor-pointer shrink-0">
        <span class="material-symbols-outlined text-base">save</span>
        <span>Guardar Configuración de Recaudo de Banners</span>
      </button>
    </div>
  </form>
</div>

<script>
function switchBannerDestinationMode(mode) {
  const corpPanel = document.getElementById('analyst-dest-corporate-panel');
  const customPanel = document.getElementById('analyst-dest-custom-panel');
  const labelCorp = document.getElementById('analyst-label-dest-corporate');
  const labelCustom = document.getElementById('analyst-label-dest-custom');
  const radioCorp = document.getElementById('analyst-radio-dest-corporate');
  const radioCustom = document.getElementById('analyst-radio-dest-custom');

  if (mode === 'custom') {
    if (radioCustom) radioCustom.checked = true;
    if (corpPanel) corpPanel.style.display = 'none';
    if (customPanel) customPanel.style.display = 'block';
    if (labelCustom) {
      labelCustom.classList.add('border-[#006c47]', 'bg-emerald-50/30');
      labelCustom.classList.remove('border-slate-200');
    }
    if (labelCorp) {
      labelCorp.classList.remove('border-[#006c47]', 'bg-emerald-50/30');
      labelCorp.classList.add('border-slate-200');
    }
  } else {
    if (radioCorp) radioCorp.checked = true;
    if (corpPanel) corpPanel.style.display = 'block';
    if (customPanel) customPanel.style.display = 'none';
    if (labelCorp) {
      labelCorp.classList.add('border-[#006c47]', 'bg-emerald-50/30');
      labelCorp.classList.remove('border-slate-200');
    }
    if (labelCustom) {
      labelCustom.classList.remove('border-[#006c47]', 'bg-emerald-50/30');
      labelCustom.classList.add('border-slate-200');
    }
  }
}

function switchAnalystCustomMethod(subMethod) {
  const bankFields = document.getElementById('analyst-custom-bank-fields');
  const walletFields = document.getElementById('analyst-custom-wallet-fields');
  const bankLabel = document.getElementById('analyst-submethod-bank-label');
  const walletLabel = document.getElementById('analyst-submethod-wallet-label');
  const bankRadio = document.getElementById('analyst-submethod-bank-radio');
  const walletRadio = document.getElementById('analyst-submethod-wallet-radio');

  if (subMethod === 'wallet') {
    if (walletRadio) walletRadio.checked = true;
    if (bankFields) bankFields.style.display = 'none';
    if (walletFields) walletFields.style.display = 'grid';
    if (walletLabel) {
      walletLabel.classList.add('border-[#006c47]', 'bg-emerald-50', 'text-[#006c47]');
      walletLabel.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
    }
    if (bankLabel) {
      bankLabel.classList.remove('border-[#006c47]', 'bg-emerald-50', 'text-[#006c47]');
      bankLabel.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
    }
  } else {
    if (bankRadio) bankRadio.checked = true;
    if (bankFields) bankFields.style.display = 'grid';
    if (walletFields) walletFields.style.display = 'none';
    if (bankLabel) {
      bankLabel.classList.add('border-[#006c47]', 'bg-emerald-50', 'text-[#006c47]');
      bankLabel.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
    }
    if (walletLabel) {
      walletLabel.classList.remove('border-[#006c47]', 'bg-emerald-50', 'text-[#006c47]');
      walletLabel.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
    }
  }
}

document.addEventListener('DOMContentLoaded', function() {
  switchBannerDestinationMode('{{ $destMode }}');
  switchAnalystCustomMethod('{{ $customMethod }}');
});
</script>
