@php
  $cp = $profile ?? (auth()->check() ? auth()->user()->companyProfile : null);
  $initialMethodType = old('payout_method_type', $cp ? $cp->resolvedPayoutMethodType() : 'bank');
  $initialBankName = old('payout_bank_name', ($cp && $cp->resolvedPayoutMethodType() === 'bank') ? $cp->payout_bank_name : 'Bancolombia');
  $initialAccType = old('payout_account_type', ($cp && in_array($cp->payout_account_type, ['Ahorros', 'Corriente'], true)) ? $cp->payout_account_type : 'Ahorros');
  $initialAccNumber = old('payout_account_number', ($cp && $cp->resolvedPayoutMethodType() === 'bank') ? $cp->payout_account_number : '');
  $initialWalletProvider = old('wallet_provider', ($cp && in_array($cp->payout_bank_name, ['Nequi', 'Daviplata'], true)) ? $cp->payout_bank_name : 'Nequi');
  $initialWalletPhone = old('wallet_phone', ($cp && $cp->resolvedPayoutMethodType() === 'wallet') ? $cp->payout_account_number : '');
  $initialHolder = old('payout_account_holder', $cp->payout_account_holder ?? (auth()->check() ? auth()->user()->name : ''));
  $initialIdNum = old('payout_id_number', $cp->payout_id_number ?? '');
  $banksList = \App\Models\CompanyProfile::availableBanks();
@endphp

<div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-emerald-500/30 space-y-5 relative overflow-hidden" id="merchant-payout-config-card">
  <div class="flex items-start gap-3">
    <div class="w-11 h-11 rounded-2xl bg-[#006c47]/10 text-[#006c47] flex items-center justify-center shrink-0">
      <span class="material-symbols-outlined">account_balance_wallet</span>
    </div>
    <div class="flex-1">
      <div class="flex items-center gap-2 flex-wrap">
        <h2 class="text-lg font-bold text-on-surface">Método de Pago Preferido (Recepción de Ventas)</h2>
        @if($cp && $cp->hasPayoutInfo())
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6efcb9]/50 text-[#003f28] inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-[12px]">verified</span> {{ $cp->formattedPayoutSummary() }}
          </span>
        @else
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
            Configuración recomendada
          </span>
        @endif
      </div>
      <p class="text-xs text-on-surface-variant mt-1">
        Selecciona dónde deseas recibir el dinero de los productos que compren tus clientes: <strong>Cuenta Bancaria Tradicional</strong> (Ahorros o Corriente) o <strong>Billetera Digital</strong> (Nequi o Daviplata).
      </p>
    </div>
  </div>

  <!-- Selector de Tipo de Método de Pago -->
  <div>
    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">
      Selecciona tu método de recaudo preferido *
    </label>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <label class="cursor-pointer rounded-2xl border-2 p-4 flex items-start gap-3 transition-all"
             id="label-method-bank"
             onclick="switchMerchantPayoutMethod('bank')">
        <input type="radio" name="payout_method_type" value="bank" id="radio-method-bank"
               class="mt-1 text-[#006c47] focus:ring-[#006c47]"
               {{ $initialMethodType === 'bank' ? 'checked' : '' }}
               onchange="switchMerchantPayoutMethod('bank')">
        <div>
          <span class="text-sm font-extrabold text-on-surface flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[#006c47] text-[18px]">account_balance</span>
            Cuenta Bancaria Tradicional
          </span>
          <span class="text-[11px] text-on-surface-variant block mt-0.5">
            Bancolombia, Davivienda, Banco de Bogotá, BBVA, Agrario (Ahorros o Corriente)
          </span>
        </div>
      </label>

      <label class="cursor-pointer rounded-2xl border-2 p-4 flex items-start gap-3 transition-all"
             id="label-method-wallet"
             onclick="switchMerchantPayoutMethod('wallet')">
        <input type="radio" name="payout_method_type" value="wallet" id="radio-method-wallet"
               class="mt-1 text-[#006c47] focus:ring-[#006c47]"
               {{ $initialMethodType === 'wallet' ? 'checked' : '' }}
               onchange="switchMerchantPayoutMethod('wallet')">
        <div>
          <span class="text-sm font-extrabold text-on-surface flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[#006c47] text-[18px]">smartphone</span>
            Billetera Digital (Nequi / Daviplata)
          </span>
          <span class="text-[11px] text-on-surface-variant block mt-0.5">
            Recibe pagos directamente con tu número de teléfono celular asociado
          </span>
        </div>
      </label>
    </div>
  </div>

  <!-- OPCIÓN A: CUENTA BANCARIA TRADICIONAL -->
  <div id="merchant-payout-bank-panel" class="p-4 rounded-2xl bg-surface-container-low/60 border border-outline-variant/25 space-y-4" style="display: {{ $initialMethodType === 'bank' ? 'block' : 'none' }};">
    <div class="flex items-center gap-2 text-xs font-extrabold text-[#006c47]">
      <span class="material-symbols-outlined text-base">account_balance</span>
      <span>Datos de Cuenta Bancaria Tradicional</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
          Entidad Bancaria *
        </label>
        <select name="payout_bank_name" id="input-payout-bank-name"
                class="w-full px-3.5 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 outline-none">
          <option value="">Selecciona tu banco...</option>
          @foreach($banksList as $bankOption)
            <option value="{{ $bankOption }}" {{ $initialBankName === $bankOption ? 'selected' : '' }}>{{ $bankOption }}</option>
          @endforeach
        </select>
        @error('payout_bank_name') <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
          Tipo de Cuenta *
        </label>
        <select name="payout_account_type" id="input-payout-account-type"
                class="w-full px-3.5 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 outline-none">
          <option value="Ahorros" {{ $initialAccType === 'Ahorros' ? 'selected' : '' }}>Cuenta de Ahorros</option>
          <option value="Corriente" {{ $initialAccType === 'Corriente' ? 'selected' : '' }}>Cuenta Corriente</option>
        </select>
        @error('payout_account_type') <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
          Número de Cuenta Bancaria *
        </label>
        <input type="text" name="payout_account_number" id="input-payout-account-number"
               value="{{ $initialAccNumber }}"
               inputmode="numeric"
               class="w-full px-3.5 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-mono font-bold text-on-surface focus:ring-2 focus:ring-primary/20 outline-none"
               placeholder="Ej: 12345678901">
        @error('payout_account_number') <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
      </div>
    </div>
  </div>

  <!-- OPCIÓN B: BILLETERA DIGITAL (NEQUI / DAVIPLATA) -->
  <div id="merchant-payout-wallet-panel" class="p-4 rounded-2xl bg-surface-container-low/60 border border-outline-variant/25 space-y-4" style="display: {{ $initialMethodType === 'wallet' ? 'block' : 'none' }};">
    <div class="flex items-center gap-2 text-xs font-extrabold text-[#006c47]">
      <span class="material-symbols-outlined text-base">smartphone</span>
      <span>Datos de Billetera Digital (Nequi o Daviplata)</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
          Billetera Digital *
        </label>
        <select name="wallet_provider" id="input-wallet-provider"
                class="w-full px-3.5 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 outline-none">
          <option value="Nequi" {{ $initialWalletProvider === 'Nequi' ? 'selected' : '' }}>Nequi</option>
          <option value="Daviplata" {{ $initialWalletProvider === 'Daviplata' ? 'selected' : '' }}>Daviplata</option>
        </select>
        @error('wallet_provider') <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
          Número de Teléfono Celular Asociado (10 dígitos) *
        </label>
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#006c47] text-sm">phone_iphone</span>
          <input type="tel" name="wallet_phone" id="input-wallet-phone"
                 value="{{ $initialWalletPhone }}"
                 maxlength="10"
                 inputmode="numeric"
                 class="w-full pl-9 pr-3.5 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-mono font-bold text-on-surface focus:ring-2 focus:ring-primary/20 outline-none"
                 placeholder="Ej: 3101234567">
        </div>
        @error('wallet_phone') <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
      </div>
    </div>
  </div>

  <!-- Datos del Titular de la Cuenta / Billetera -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
    <div>
      <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
        Nombre Completo del Titular
      </label>
      <input type="text" name="payout_account_holder" value="{{ $initialHolder }}"
             class="w-full px-3.5 py-2.5 bg-surface-container-low border border-outline-variant/25 rounded-xl text-xs font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 outline-none"
             placeholder="Nombre y apellidos del titular">
    </div>
    <div>
      <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">
        Cédula o NIT del Titular
      </label>
      <input type="text" name="payout_id_number" value="{{ $initialIdNum }}"
             class="w-full px-3.5 py-2.5 bg-surface-container-low border border-outline-variant/25 rounded-xl text-xs font-mono text-on-surface focus:ring-2 focus:ring-primary/20 outline-none"
             placeholder="Ej: 1003618196 o 901234567-1">
    </div>
  </div>

  <p class="pt-3 border-t border-outline-variant/20 text-xs text-on-surface-variant">
    Los pagos de los pedidos se procesan con la cuenta central de Wompi de FusaShop. Esta cuenta bancaria o billetera se usa para liquidarte las ventas aprobadas.
  </p>
</div>

<script>
function switchMerchantPayoutMethod(method) {
  const bankPanel = document.getElementById('merchant-payout-bank-panel');
  const walletPanel = document.getElementById('merchant-payout-wallet-panel');
  const labelBank = document.getElementById('label-method-bank');
  const labelWallet = document.getElementById('label-method-wallet');
  const radioBank = document.getElementById('radio-method-bank');
  const radioWallet = document.getElementById('radio-method-wallet');

  if (method === 'wallet') {
    if (radioWallet) radioWallet.checked = true;
    if (bankPanel) bankPanel.style.display = 'none';
    if (walletPanel) walletPanel.style.display = 'block';
    if (labelWallet) {
      labelWallet.classList.add('border-[#006c47]', 'bg-emerald-50/40');
      labelWallet.classList.remove('border-outline-variant/30');
    }
    if (labelBank) {
      labelBank.classList.remove('border-[#006c47]', 'bg-emerald-50/40');
      labelBank.classList.add('border-outline-variant/30');
    }
  } else {
    if (radioBank) radioBank.checked = true;
    if (bankPanel) bankPanel.style.display = 'block';
    if (walletPanel) walletPanel.style.display = 'none';
    if (labelBank) {
      labelBank.classList.add('border-[#006c47]', 'bg-emerald-50/40');
      labelBank.classList.remove('border-outline-variant/30');
    }
    if (labelWallet) {
      labelWallet.classList.remove('border-[#006c47]', 'bg-emerald-50/40');
      labelWallet.classList.add('border-outline-variant/30');
    }
  }
}
document.addEventListener('DOMContentLoaded', function() {
  switchMerchantPayoutMethod('{{ $initialMethodType }}');
});
</script>
