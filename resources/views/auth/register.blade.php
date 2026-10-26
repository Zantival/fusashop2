@extends('layouts.auth')
@section('title', 'Crear cuenta')

@section('styles')
<style>
.bg-gradient { background: linear-gradient(135deg, #006c47 0%, #00b67a 50%, #003d28 100%); }
@keyframes cart-float { 0%, 100% { transform: translateY(0) rotate(-3deg); } 50% { transform: translateY(-16px) rotate(3deg); } }
@keyframes float-tag { 0%, 100% { transform: translateY(0) rotate(-5deg); } 50% { transform: translateY(-8px) rotate(-3deg); } }
@keyframes typewriter { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0% 0 0); } }
@keyframes blink-cursor { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

.cart-anim { animation: cart-float 3s ease-in-out infinite; }
.tag-anim   { animation: float-tag 2.5s ease-in-out infinite; }
.typewriter { font-family: 'Manrope', sans-serif; font-weight: 800; white-space: nowrap; overflow: hidden; animation: typewriter 2s steps(10) 0.5s both; }
.cursor::after { content: '|'; animation: blink-cursor 0.8s ease-in-out infinite; color: rgba(255,255,255,0.7); }
.form-enter { animation: fadeInUp 0.6s ease forwards; opacity: 0; }
.form-enter:nth-child(1) { animation-delay: 0.1s; }
.form-enter:nth-child(2) { animation-delay: 0.2s; }
.form-enter:nth-child(3) { animation-delay: 0.3s; }

.role-input:checked + .role-box {
  border-color: #006c47 !important;
  background-color: rgba(0, 108, 71, 0.05) !important;
  box-shadow: 0 0 0 4px rgba(0, 108, 71, 0.1) !important;
}
.role-input:checked + .role-box .role-icon {
  color: #006c47 !important;
}

.input-wrap {
  display: flex; align-items: center;
  background: var(--surface-container); border-radius: 1rem; border: 2px solid transparent;
  transition: all 0.2s ease; overflow: hidden;
}
.input-wrap:focus-within { background: white; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(0,108,71,0.08); }
.input-icon-box {
  display: flex; align-items: center; justify-content: center;
  width: 3rem; min-width: 3rem; height: 100%;
  color: var(--on-surface-variant);
  border-right: 1.5px solid rgba(0,0,0,0.07);
  padding: 0 0.5rem;
  font-size: 20px;
  background: transparent;
  pointer-events: none;
  user-select: none;
}
.input-field {
  flex: 1; padding: 0.875rem 1rem;
  background: transparent; border: none;
  outline: none; font-size: 0.9rem;
  color: var(--on-surface); width: 100%;
}
</style>
@endsection

@section('content')
<div class="min-h-screen flex flex-col lg:flex-row">
  <!-- Left Panel -->
  <div class="bg-gradient lg:w-5/12 xl:w-1/2 flex flex-col items-center justify-center p-8 lg:p-16 py-16 relative overflow-hidden">
    <div class="absolute top-1/4 -left-20 w-64 h-64 bg-white/5 rounded-full"></div>
    <div class="absolute bottom-1/4 -right-20 w-80 h-80 bg-white/5 rounded-full"></div>

    <div class="cart-anim mb-8 relative z-10">
      <div class="tag-anim absolute -top-6 -right-8 bg-[#feb700] text-[#6b4b00] px-3 py-1 rounded-full text-xs font-black shadow-lg rotate-12">¡Nuevo!</div>
      <svg width="120" height="100" viewBox="0 0 140 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 15 L30 15 L50 80 L110 80 L125 30 L40 30" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="55" cy="100" r="8" fill="white"/>
        <circle cx="105" cy="100" r="8" fill="white"/>
      </svg>
    </div>

    <div class="text-center relative z-10">
      <div class="typewriter cursor text-4xl xl:text-5xl text-white mb-4">Únete hoy</div>
      <p class="text-white/80 text-lg font-medium max-w-xs text-center leading-relaxed">Crea tu cuenta y empieza a comprar o vender en Fusagasugá.</p>
    </div>
  </div>

  <!-- Right Panel -->
  <div class="flex-1 flex items-center justify-center p-6 lg:p-12 bg-surface-container-lowest">
    <div class="w-full max-w-lg">
      <div class="mb-6 form-enter">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
            <span class="material-symbols-outlined text-primary">person_add</span>
          </div>
          <a href="{{ route('login') }}" class="text-sm text-on-surface-variant hover:text-primary transition-colors font-medium">← Ya tengo cuenta</a>
        </div>
        <h1 class="text-3xl font-black text-on-surface tracking-tight mb-1">
          {{ session('social_registration') ? 'Completa tu registro' : 'Crea tu cuenta' }}
        </h1>
        <p class="text-on-surface-variant">
          {{ session('social_registration') ? 'Selecciona tu tipo de cuenta y añade tu teléfono para continuar' : 'Regístrate para empezar a usar FusaShop' }}
        </p>
      </div>

      {{-- Notificación si viene redirigido desde Google Login sin cuenta previa --}}
      @if(session('info'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3 form-enter shadow-xs">
          <span class="material-symbols-outlined text-amber-600 text-[22px] shrink-0 mt-0.5">info</span>
          <p class="text-sm text-amber-900 font-medium leading-relaxed">{{ session('info') }}</p>
        </div>
      @endif

      {{-- Banner de cuenta Google conectada --}}
      @if(session('social_registration'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between form-enter shadow-xs">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-xs border border-emerald-100 shrink-0">
              <img loading="lazy" decoding="async" src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
            </div>
            <div>
              <p class="text-xs font-bold text-emerald-950">Conectado con Google</p>
              <p class="text-xs text-emerald-700 font-medium">{{ session('social_registration.email') }}</p>
            </div>
          </div>
          <a href="{{ route('social.cancel') }}" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 py-1.5 px-3 bg-white border border-emerald-200 rounded-xl hover:bg-emerald-100 transition-colors">
            Cancelar
          </a>
        </div>
      @endif

      @php
        $registrationErrors = $errors->getMessages();
        unset($registrationErrors['password'], $registrationErrors['password_confirmation']);
      @endphp
      @if(!empty($registrationErrors))
        <div class="mb-6 p-4 bg-error/10 border border-error/20 rounded-xl flex items-center gap-3 form-enter">
          <span class="material-symbols-outlined text-error text-[20px]">error</span>
          <div class="text-sm text-error font-medium">
            @foreach($registrationErrors as $fieldErrors)
              @foreach($fieldErrors as $error)<p>• {{ $error }}</p>@endforeach
            @endforeach
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('register') }}" class="space-y-4" id="register-form">
        @csrf

        <!-- Role Selection -->
        <div class="form-enter">
          <label class="block text-sm font-bold text-on-surface mb-2">¿Qué tipo de cuenta deseas? <span class="text-error">*</span></label>
          <p class="text-xs text-on-surface-variant mb-3">Elige si vas a comprar productos o vas a vender como comercio aliado</p>
          <div class="grid grid-cols-2 gap-3">
            <!-- Comprar / Cliente -->
            <label class="relative cursor-pointer block h-full">
              <input type="radio" name="role" value="consumer" class="role-input absolute inset-0 w-full h-full opacity-0 z-20 cursor-pointer" {{ old('role', request('role', 'consumer'))==='consumer' ? 'checked' : '' }} onchange="toggleMerchantPayoutRegister()" required/>
              <div class="role-box flex flex-col items-center gap-2 p-4 bg-surface-container rounded-2xl border-2 border-transparent transition-all h-full relative z-10 text-center">
                <span class="material-symbols-outlined role-icon text-on-surface-variant text-3xl">shopping_bag</span>
                <span class="text-sm font-bold text-on-surface">Cliente</span>
                <span class="text-[11px] text-on-surface-variant">Deseo Comprar</span>
              </div>
            </label>
            <!-- Vender / Vendedor -->
            <label class="relative cursor-pointer block h-full">
              <input type="radio" name="role" value="merchant" class="role-input absolute inset-0 w-full h-full opacity-0 z-20 cursor-pointer" {{ old('role', $role ?? 'consumer')==='merchant' ? 'checked' : '' }} onchange="toggleMerchantPayoutRegister()"/>
              <div class="role-box flex flex-col items-center gap-2 p-4 bg-surface-container rounded-2xl border-2 border-transparent transition-all h-full relative z-10 text-center">
                <span class="material-symbols-outlined role-icon text-on-surface-variant text-3xl">storefront</span>
                <span class="text-sm font-bold text-on-surface">Vendedor</span>
                <span class="text-[11px] text-on-surface-variant">Comercio Aliado</span>
              </div>
            </label>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-enter md:col-span-2">
            <label class="block text-sm font-semibold text-on-surface mb-1.5">Nombre completo</label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon-box">person</span>
              <input type="text" id="register-name" name="name" value="{{ old('name', session('social_registration.name')) }}" required
                class="input-field" placeholder="Tu nombre y apellido" autocomplete="name" pattern="\S+(?: \S+)*" title="No uses espacios al inicio, al final ni repetidos" aria-describedby="name-help">
            </div>
            <p id="name-help" class="mt-1 text-xs text-on-surface-variant">Usa letras; se permiten nombres compuestos con un solo espacio entre palabras.</p>
            <p id="name-error" class="hidden mt-1 text-xs font-semibold text-error" role="alert">Revisa el nombre: no uses espacios al inicio, al final ni repetidos, y escribe solo letras.</p>
          </div>

          <div class="form-enter md:col-span-2">
            <label class="block text-sm font-semibold text-on-surface mb-1.5">Correo electrónico</label>
            <div class="input-wrap {{ session('social_registration') ? 'bg-slate-100 border-slate-200' : '' }}">
              <span class="material-symbols-outlined input-icon-box">mail</span>
              <input type="email" name="email" value="{{ old('email', session('social_registration.email')) }}" required
                class="input-field {{ session('social_registration') ? 'text-slate-600 font-semibold cursor-not-allowed' : '' }}"
                placeholder="correo@ejemplo.com" autocomplete="email"
                {{ session('social_registration') ? 'readonly' : '' }}>
            </div>
            <p class="mt-1 text-xs text-on-surface-variant">Escribe un correo real al que tengas acceso. Te enviaremos un código para comprobarlo antes de activar la cuenta.</p>
            @if(session('social_registration'))
              <p class="text-[11px] text-on-surface-variant mt-1">Este correo proviene de tu cuenta de Google verificada.</p>
            @endif
          </div>

          <div class="form-enter md:col-span-2">
            <label class="block text-sm font-semibold text-on-surface mb-1.5">Número de teléfono / WhatsApp <span class="text-error">*</span></label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon-box">call</span>
              <select name="phone_country" aria-label="Indicativo del país" class="border-r border-slate-200 bg-transparent px-2 py-3 text-sm font-semibold text-on-surface outline-none" required>
                @foreach(['57' => '🇨🇴 +57', '1' => '🇺🇸 +1', '52' => '🇲🇽 +52', '34' => '🇪🇸 +34', '51' => '🇵🇪 +51', '56' => '🇨🇱 +56', '54' => '🇦🇷 +54'] as $code => $label)
                  <option value="{{ $code }}" {{ old('phone_country', '57') === $code ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              <input type="tel" name="phone" value="{{ old('phone') }}" required inputmode="numeric" pattern="[0-9]{7,15}" maxlength="15"
                class="input-field" placeholder="3101234567" autocomplete="tel-national" aria-describedby="phone-help">
            </div>
            <p id="phone-help" class="mt-1 text-xs text-on-surface-variant">Colombia: 10 dígitos, debe comenzar por 3. Escribe solo números.</p>
          </div>

          <div class="form-enter {{ session('social_registration') ? 'md:col-span-1' : '' }}">
            <label class="block text-sm font-semibold text-on-surface mb-1.5">
              Contraseña
              @if(session('social_registration'))
                <span class="text-xs font-normal text-on-surface-variant">(Opcional)</span>
              @endif
            </label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon-box">lock</span>
              <input type="password" name="password" id="password" {{ session('social_registration') ? '' : 'required' }}
                class="input-field" placeholder="{{ session('social_registration') ? 'Opcional (12+ caracteres)' : '12+ caracteres' }}" autocomplete="new-password" aria-describedby="password-policy-hint password-error">
              <button type="button" onclick="togglePwd('password', 'eye-pwd')" class="flex items-center justify-center w-10 min-w-10 text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[20px]" id="eye-pwd">visibility_off</span>
              </button>
            </div>
          </div>

          <div class="form-enter {{ session('social_registration') ? 'md:col-span-1' : '' }}">
            <label class="block text-sm font-semibold text-on-surface mb-1.5">
              Confirmar
              @if(session('social_registration'))
                <span class="text-xs font-normal text-on-surface-variant">(Opcional)</span>
              @endif
            </label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon-box">lock_reset</span>
              <input type="password" name="password_confirmation" id="password_confirmation" {{ session('social_registration') ? '' : 'required' }}
                class="input-field" placeholder="Repite contraseña" autocomplete="new-password" aria-describedby="password-confirmation-error">
              <button type="button" onclick="togglePwd('password_confirmation', 'eye-pwd-conf')" class="flex items-center justify-center w-10 min-w-10 text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[20px]" id="eye-pwd-conf">visibility_off</span>
              </button>
            </div>
            <p id="password-confirmation-error" class="{{ $errors->has('password_confirmation') ? 'mt-1 text-center text-xs font-semibold text-error' : 'hidden' }}" role="alert" aria-live="polite">{{ $errors->first('password_confirmation') }}</p>
          </div>

          <div class="form-enter md:col-span-2 w-full max-w-xl mx-auto">
            <div id="password-policy-hint" class="mx-auto w-full max-w-xl rounded-xl border border-emerald-200 bg-emerald-50/70 px-4 py-3 text-sm text-on-surface-variant">
              <p class="mb-2 text-center font-semibold">La contraseña debe cumplir:</p>
              <ul class="mx-auto grid w-fit gap-1 list-disc pl-5 text-left">
                <li>Al menos 12 caracteres</li><li>Una letra mayúscula y una minúscula</li><li>Un número</li><li>Un símbolo (por ejemplo, ! o #)</li>
              </ul>
            </div>
            <p id="password-error" class="{{ $errors->has('password') ? 'mt-1 text-center text-xs font-semibold text-error' : 'hidden' }}" role="alert" aria-live="polite">{{ $errors->first('password') }}</p>
          </div>
        </div>

        {{-- Módulo de Configuración de Método de Pago Preferido para Comerciantes --}}
        @php
          $regMethodType = old('payout_method_type', 'bank');
          $regBanks = \App\Models\CompanyProfile::availableBanks();
        @endphp
        <div id="merchant-payout-register-section" class="p-4 rounded-2xl bg-emerald-50/50 border-2 border-[#006c47]/25 space-y-3.5 transition-all" style="display: {{ old('role', $role ?? 'consumer') === 'merchant' ? 'block' : 'none' }};">
          <div class="flex items-start gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-[#006c47] text-white flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[18px]">payments</span>
            </div>
            <div>
              <h3 class="text-sm font-black text-on-surface">Método de Pago Preferido (Comerciante)</h3>
              <p class="text-[11px] text-on-surface-variant leading-snug">
                Configura dónde deseas recibir el dinero de tus ventas: cuenta bancaria tradicional o billetera digital.
              </p>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-on-surface mb-1">Nombre de tu Tienda o Comercio (Opcional)</label>
            <input type="text" name="company_name" value="{{ old('company_name') }}"
                   class="w-full px-3.5 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-semibold text-on-surface outline-none focus:ring-2 focus:ring-primary/20"
                   placeholder="Ej: Café Especial del Sumapaz">
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <label class="cursor-pointer rounded-xl border-2 p-2.5 flex items-center gap-2 bg-white transition-all" id="reg-label-bank">
              <input type="radio" name="payout_method_type" value="bank" id="reg-radio-bank"
                     {{ $regMethodType === 'bank' ? 'checked' : '' }}
                     onchange="switchRegisterPayoutType('bank')"
                     class="text-[#006c47] focus:ring-[#006c47]">
              <div class="min-w-0">
                <span class="text-xs font-extrabold text-on-surface block truncate">Cuenta Bancaria</span>
                <span class="text-[10px] text-on-surface-variant block truncate">Ahorros / Corriente</span>
              </div>
            </label>

            <label class="cursor-pointer rounded-xl border-2 p-2.5 flex items-center gap-2 bg-white transition-all" id="reg-label-wallet">
              <input type="radio" name="payout_method_type" value="wallet" id="reg-radio-wallet"
                     {{ $regMethodType === 'wallet' ? 'checked' : '' }}
                     onchange="switchRegisterPayoutType('wallet')"
                     class="text-[#006c47] focus:ring-[#006c47]">
              <div class="min-w-0">
                <span class="text-xs font-extrabold text-on-surface block truncate">Billetera Digital</span>
                <span class="text-[10px] text-on-surface-variant block truncate">Nequi o Daviplata</span>
              </div>
            </label>
          </div>

          <!-- Sub-panel Cuenta Bancaria Tradicional -->
          <div id="reg-bank-fields" class="space-y-3 pt-1" style="display: {{ $regMethodType === 'bank' ? 'block' : 'none' }};">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
              <div>
                <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Banco <span class="text-error">*</span></label>
                <select name="payout_bank_name" id="reg-payout-bank"
                        class="w-full px-3 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-semibold text-on-surface outline-none focus:ring-2 focus:ring-primary/20">
                  <option value="">Selecciona banco...</option>
                  @foreach($regBanks as $bName)
                    <option value="{{ $bName }}" {{ old('payout_bank_name', 'Bancolombia') === $bName ? 'selected' : '' }}>{{ $bName }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Tipo de Cuenta <span class="text-error">*</span></label>
                <select name="payout_account_type" id="reg-payout-acc-type"
                        class="w-full px-3 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-semibold text-on-surface outline-none focus:ring-2 focus:ring-primary/20">
                  <option value="Ahorros" {{ old('payout_account_type', 'Ahorros') === 'Ahorros' ? 'selected' : '' }}>Ahorros</option>
                  <option value="Corriente" {{ old('payout_account_type') === 'Corriente' ? 'selected' : '' }}>Corriente</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Número de Cuenta Bancaria <span class="text-error">*</span></label>
              <input type="text" name="payout_account_number" id="reg-payout-acc-num" value="{{ old('payout_account_number') }}"
                     inputmode="numeric"
                     class="w-full px-3.5 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-mono font-bold text-on-surface outline-none focus:ring-2 focus:ring-primary/20"
                     placeholder="Ej: 12345678901">
            </div>
          </div>

          <!-- Sub-panel Billeteras Digitales (Nequi / Daviplata) -->
          <div id="reg-wallet-fields" class="space-y-3 pt-1" style="display: {{ $regMethodType === 'wallet' ? 'block' : 'none' }};">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
              <div>
                <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Billetera Digital <span class="text-error">*</span></label>
                <select name="wallet_provider" id="reg-wallet-provider"
                        class="w-full px-3 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-semibold text-on-surface outline-none focus:ring-2 focus:ring-primary/20">
                  <option value="Nequi" {{ old('wallet_provider', 'Nequi') === 'Nequi' ? 'selected' : '' }}>Nequi</option>
                  <option value="Daviplata" {{ old('wallet_provider') === 'Daviplata' ? 'selected' : '' }}>Daviplata</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-on-surface-variant mb-1">Teléfono Asociado (10 dígitos) <span class="text-error">*</span></label>
                <input type="tel" name="wallet_phone" id="reg-wallet-phone" value="{{ old('wallet_phone') }}"
                       maxlength="10" inputmode="numeric"
                       class="w-full px-3.5 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-xs font-mono font-bold text-on-surface outline-none focus:ring-2 focus:ring-primary/20"
                       placeholder="Ej: 3101234567">
              </div>
            </div>
          </div>
        </div>

        <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 flex items-center gap-2.5 text-xs text-emerald-800 form-enter">
          <span class="material-symbols-outlined text-emerald-600 text-base shrink-0">mark_email_read</span>
          <span>Al crear tu cuenta, te enviaremos un <strong>código de 6 dígitos a tu correo</strong> para confirmar tu identidad.</span>
        </div>

        <fieldset class="form-enter space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <legend class="px-1 text-xs font-extrabold text-slate-700">Consentimientos de la cuenta</legend>
          <label class="flex items-start gap-2.5 text-xs leading-relaxed text-slate-700">
            <input type="checkbox" name="accepted_terms" value="1" required {{ old('accepted_terms') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
            <span>Acepto los <a href="{{ route('terms') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-800 underline">Términos y condiciones</a>.</span>
          </label>
          <label class="flex items-start gap-2.5 text-xs leading-relaxed text-slate-700">
            <input type="checkbox" name="accepted_privacy" value="1" required {{ old('accepted_privacy') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
            <span>Autorizo el tratamiento de mis datos para crear y operar mi cuenta, conforme a la <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-800 underline">Política de privacidad</a>.</span>
          </label>
          <label class="flex items-start gap-2.5 text-xs leading-relaxed text-slate-700">
            <input type="checkbox" name="age_or_guardian_confirmed" value="1" required {{ old('age_or_guardian_confirmed') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
            <span>Confirmo que soy mayor de edad o que mi madre, padre o representante legal conoce y autoriza este registro.</span>
          </label>
          <label class="flex items-start gap-2.5 border-t border-slate-200 pt-3 text-xs leading-relaxed text-slate-600">
            <input type="checkbox" name="marketing_opt_in" value="1" {{ old('marketing_opt_in') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
            <span>Quiero recibir correos opcionales sobre promociones y descuentos. No es necesario para usar FusaShop y puedo darme de baja.</span>
          </label>
        </fieldset>

        <div class="form-enter pt-2">
          <button type="submit" id="reg-btn" class="w-full btn-primary py-4 text-sm font-bold text-white rounded-2xl shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 hover:opacity-95 transition-all" style="background: linear-gradient(135deg, #10B981 0%, #047857 100%) !important;">
            <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
            {{ session('social_registration') ? 'Completar registro y verificar correo' : 'Crear mi cuenta' }}
          </button>

          @unless(session('social_registration'))
            <div class="relative flex items-center justify-center my-6">
              <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-surface-container-highest"></div></div>
              <span class="relative px-4 bg-white text-[10px] font-black text-on-surface-variant uppercase tracking-widest">O regístrate con</span>
            </div>

            <a href="{{ route('social.redirect', 'google') }}" class="w-full flex items-center justify-center gap-3 py-3.5 bg-white border-2 border-surface-container-highest rounded-2xl font-bold text-on-surface hover:bg-surface-container-low hover:border-primary/30 transition-all shadow-sm">
               <img loading="lazy" decoding="async" src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google Logo">
               <span class="text-sm">Google</span>
            </a>
          @endunless
        </div>

      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function togglePwd(inputId, iconId) {
  const i = document.getElementById(inputId);
  const e = document.getElementById(iconId);
  if (!i || !e) return;
  i.type = i.type === 'password' ? 'text' : 'password';
  e.textContent = i.type === 'password' ? 'visibility_off' : 'visibility';
}

function toggleMerchantPayoutRegister() {
  const checkedRole = document.querySelector('input[name="role"]:checked');
  const isMerchant = checkedRole && checkedRole.value === 'merchant';
  const section = document.getElementById('merchant-payout-register-section');
  if (section) {
    section.style.display = isMerchant ? 'block' : 'none';
  }
  const activeMethod = document.querySelector('input[name="payout_method_type"]:checked');
  switchRegisterPayoutType(activeMethod ? activeMethod.value : 'bank', isMerchant);
}

function switchRegisterPayoutType(type, isMerchantOverride) {
  const checkedRole = document.querySelector('input[name="role"]:checked');
  const isMerchant = typeof isMerchantOverride === 'boolean' ? isMerchantOverride : (checkedRole && checkedRole.value === 'merchant');
  const bankFields = document.getElementById('reg-bank-fields');
  const walletFields = document.getElementById('reg-wallet-fields');
  const labelBank = document.getElementById('reg-label-bank');
  const labelWallet = document.getElementById('reg-label-wallet');
  const bankAccNum = document.getElementById('reg-payout-acc-num');
  const walletPhone = document.getElementById('reg-wallet-phone');

  if (type === 'wallet') {
    if (bankFields) bankFields.style.display = 'none';
    if (walletFields) walletFields.style.display = 'block';
    if (labelWallet) labelWallet.classList.add('border-[#006c47]', 'bg-emerald-50/40');
    if (labelBank) labelBank.classList.remove('border-[#006c47]', 'bg-emerald-50/40');
    if (bankAccNum) bankAccNum.required = false;
    if (walletPhone) walletPhone.required = Boolean(isMerchant);
  } else {
    if (bankFields) bankFields.style.display = 'block';
    if (walletFields) walletFields.style.display = 'none';
    if (labelBank) labelBank.classList.add('border-[#006c47]', 'bg-emerald-50/40');
    if (labelWallet) labelWallet.classList.remove('border-[#006c47]', 'bg-emerald-50/40');
    if (walletPhone) walletPhone.required = false;
    if (bankAccNum) bankAccNum.required = Boolean(isMerchant);
  }
}

document.addEventListener('DOMContentLoaded', function() {
  toggleMerchantPayoutRegister();
});

const registerForm = document.getElementById('register-form');
const registerName = document.getElementById('register-name');
const passwordInput = document.getElementById('password');
const passwordConfirmationInput = document.getElementById('password_confirmation');

function validateRegistrationPasswords() {
  if (!passwordInput || !passwordConfirmationInput) return true;

  const password = passwordInput.value;
  const confirmation = passwordConfirmationInput.value;
  const policyError = document.getElementById('password-error');
  const confirmationError = document.getElementById('password-confirmation-error');
  const isSocialRegistration = {{ session('social_registration') ? 'true' : 'false' }};
  let passwordMessage = '';

  if (password || !isSocialRegistration) {
    if (password.length < 12) passwordMessage = 'La contraseña debe tener al menos 12 caracteres.';
    else if (!/[a-z]/.test(password) || !/[A-Z]/.test(password)) passwordMessage = 'La contraseña debe incluir letras mayúsculas y minúsculas.';
    else if (!/[0-9]/.test(password)) passwordMessage = 'La contraseña debe incluir al menos un número.';
    else if (!/[^A-Za-z0-9]/.test(password)) passwordMessage = 'La contraseña debe incluir al menos un símbolo.';
  }

  const confirmationMessage = (password || confirmation) && password !== confirmation
    ? 'La confirmación de la contraseña no coincide.'
    : '';

  if (policyError) {
    policyError.textContent = passwordMessage;
    policyError.classList.toggle('hidden', !passwordMessage);
  }
  if (confirmationError) {
    confirmationError.textContent = confirmationMessage;
    confirmationError.classList.toggle('hidden', !confirmationMessage);
  }

  return !passwordMessage && !confirmationMessage;
}

registerName?.addEventListener('input', function () {
  const valid = /^\p{L}+(?:[ '\u2019-]\p{L}+)*$/u.test(registerName.value);
  document.getElementById('name-error')?.classList.toggle('hidden', !valid || !registerName.value);
  registerName.setCustomValidity(valid || !registerName.value ? '' : 'Escribe un nombre válido, sin espacios sobrantes ni caracteres no permitidos.');
});

passwordInput?.addEventListener('input', validateRegistrationPasswords);
passwordConfirmationInput?.addEventListener('input', validateRegistrationPasswords);

registerForm?.addEventListener('submit', function(event) {
  if (!validateRegistrationPasswords()) {
    event.preventDefault();
    (document.getElementById('password-error')?.textContent
      ? passwordInput
      : passwordConfirmationInput)?.focus();
    return;
  }

  const btn = document.getElementById('reg-btn');
  if (btn) {
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[20px]">sync</span> Creando cuenta...';
    btn.disabled = true;
  }
});
</script>
@endsection
