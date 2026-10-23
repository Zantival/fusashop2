<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Nueva contraseña | FusaShop</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@400,0&display=swap" rel="stylesheet"/>
<style>
.mso{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
.grad{background:linear-gradient(135deg,#006c47,#00b67a)}
body{font-family:'Inter',sans-serif;background:#f7fcf8;color:#1b1c1c;min-height:100vh;display:flex;flex-direction:column}
h1{font-family:'Manrope',sans-serif}
</style>
</head>
<body>
<header class="fixed top-0 w-full z-50 shadow-sm h-16 flex items-center justify-between px-6" style="background:rgba(252,249,248,.9);backdrop-filter:blur(20px)">
  <x-logo size="default" :href="route('consumer.home')" />
  <a href="{{ route('login') }}" class="text-sm text-[#006c47] font-semibold hover:underline">Volver a inicio</a>
</header>
<main class="flex-1 flex items-center justify-center pt-20 pb-10 px-4">
  <div class="w-full max-w-md bg-white rounded-2xl p-8 shadow-[0_12px_40px_rgba(0,0,0,0.08)]">
    <div class="mb-7">
      <div class="w-12 h-12 grad rounded-xl flex items-center justify-center mb-4"><span class="mso material-symbols-outlined text-white">lock_reset</span></div>
      <h1 class="text-2xl font-black">Crear contraseña nueva</h1>
      <p class="text-[#3c4a41] text-sm mt-1">Elige una contraseña segura para recuperar el acceso a tu cuenta.</p>
    </div>

    @if($errors->any())
      <div class="bg-red-50 text-red-700 px-4 py-3 rounded-xl mb-5 text-sm" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div>
        <label for="email" class="block text-sm font-semibold mb-1.5">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email" autofocus
          class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="correo@ejemplo.com">
      </div>
      <div>
        <label for="password" class="block text-sm font-semibold mb-1.5">Nueva contraseña</label>
        <input id="password" type="password" name="password" required minlength="12" autocomplete="new-password"
          class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="12 caracteres o más">
        <p class="mt-2 text-xs text-[#526258]">Usa al menos 12 caracteres, con mayúsculas, minúsculas, un número y un símbolo.</p>
      </div>
      <div>
        <label for="password_confirmation" class="block text-sm font-semibold mb-1.5">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"
          class="w-full px-4 py-3 bg-[#f0eded] rounded-xl border-0 outline-none focus:ring-2 focus:ring-[#006c47]" placeholder="Escribe la contraseña otra vez">
      </div>
      <button type="submit" class="w-full py-3 grad text-white font-semibold rounded-xl hover:opacity-90 transition-all shadow-md flex items-center justify-center gap-2">
        <span class="mso material-symbols-outlined text-sm">lock_reset</span> Guardar contraseña nueva
      </button>
    </form>
  </div>
</main>
</body>
</html>
