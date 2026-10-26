@if(!request()->hasCookie('cookie_consent'))
<div id="cookie-banner" class="cookie-banner">
  <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-start sm:items-center gap-4">
    <div class="flex items-start gap-3 flex-1">
      <span class="material-symbols-outlined text-[#feb700] text-2xl shrink-0 mt-0.5">cookie</span>
      <div>
        <p class="font-bold text-white text-sm">Preferencias de cookies</p>
        <p class="text-white/70 text-xs mt-0.5">
          Usamos cookies técnicas necesarias para la sesión, seguridad y preferencias. Actualmente no utilizamos cookies publicitarias ni analítica de terceros.
          <a href="{{ route('cookies') }}" class="text-[#6efcb9] underline hover:no-underline">Política de cookies</a>
        </p>
      </div>
    </div>
    <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto">
      <button onclick="acceptCookies()" class="flex-1 sm:flex-none px-5 py-2 bg-[#00b67a] text-white text-xs font-bold rounded-lg hover:opacity-90 transition-all">
        Entendido
      </button>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const banner = document.getElementById('cookie-banner');
  if (banner) setTimeout(() => banner.classList.add('visible'), 1000);
});
function acceptCookies() {
  fetch('/cookie-consent', {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json'},body:JSON.stringify({consent:true})})
    .then(() => hideCookieBanner(true));
}
function hideCookieBanner(consentValue) {
  if (consentValue !== undefined) {
  }
  const b = document.getElementById('cookie-banner');
  if (b) {
    b.style.transform = 'translateY(100%)';
    setTimeout(() => b.remove(), 500);
  }
}
</script>
@endif
