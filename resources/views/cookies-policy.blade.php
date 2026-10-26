@extends('layouts.app')

@section('title', 'Política de Cookies')

@section('content')
<article class="mx-auto max-w-4xl px-4 py-10 md:py-14 text-on-surface">
  <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm md:p-10">
    <div class="flex items-center gap-2 mb-2">
      <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
      <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-800">FusaShop · Fusagasugá, Colombia</p>
    </div>
    <h1 class="mb-3 text-3xl font-black text-emerald-950 md:text-4xl">Política de Uso de Cookies</h1>
    <p class="mb-8 text-sm text-slate-500">Versión 2026-10-25 · Actualizada el 25 de octubre de 2026</p>

    <div class="space-y-7 text-sm leading-7 text-slate-700">
      
      <section class="rounded-2xl bg-emerald-50/60 p-5 border border-emerald-100 text-emerald-950">
        <p class="font-medium">
          En <strong>FusaShop</strong> creemos en la privacidad y la navegación honesta. Solo utilizamos cookies técnicas y esenciales estrictamente necesarias para el correcto funcionamiento de la plataforma, la seguridad de tu cuenta y la gestión de tu carrito de compras. No empleamos cookies invasivas de seguimiento entre sitios ni comercializamos tu historial de navegación con anunciantes externos.
        </p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">1. ¿Qué es una cookie?</h2>
        <p>
          Una cookie es un pequeño archivo de texto que un sitio web almacena en tu navegador web o dispositivo móvil al visitarlo. Las cookies permiten que el sitio recuerde tus acciones y preferencias durante un período de tiempo, para que no tengas que volver a iniciar sesión o reconfigurar la plataforma cada vez que cambias de página.
        </p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">2. Cookies esenciales que utilizamos</h2>
        <div class="overflow-x-auto mt-3">
          <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
            <thead class="bg-slate-100 text-slate-800 font-extrabold">
              <tr>
                <th class="p-3 border-b border-slate-200">Nombre de Cookie</th>
                <th class="p-3 border-b border-slate-200">Propósito</th>
                <th class="p-3 border-b border-slate-200">Duración</th>
                <th class="p-3 border-b border-slate-200">Tipo</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr>
                <td class="p-3 font-mono font-bold text-emerald-800">fusashop_session</td>
                <td class="p-3">Mantiene la sesión de usuario autenticado de forma segura.</td>
                <td class="p-3">2 horas / Sesión</td>
                <td class="p-3">Esencial</td>
              </tr>
              <tr>
                <td class="p-3 font-mono font-bold text-emerald-800">XSRF-TOKEN</td>
                <td class="p-3">Protege contra ataques de falsificación de peticiones en sitios cruzados (CSRF).</td>
                <td class="p-3">2 horas</td>
                <td class="p-3">Seguridad / Esencial</td>
              </tr>
              <tr>
                <td class="p-3 font-mono font-bold text-emerald-800">cookie_consent</td>
                <td class="p-3">Registra que viste y aceptaste el banner informativo de cookies.</td>
                <td class="p-3">1 año</td>
                <td class="p-3">Preferencia</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">3. Cookies de terceros y contenidos integrados</h2>
        <p>
          Cuando interactúas con componentes externos como el checkout seguro de <strong>Wompi</strong>, el cotizador de envíos de <strong>Envia.com</strong>, mapas de ubicación o autenticación delegada con <strong>Google OAuth</strong>, estos proveedores pueden utilizar identificadores técnicos estrictamente orientados a procesar la transacción o verificar la autenticidad del pago conforme a sus propios estándares de seguridad PCI-DSS.
        </p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">4. Cómo gestionar y deshabilitar cookies</h2>
        <p>
          Puedes configurar tu navegador para bloquear o alertarte sobre la presencia de cookies:
        </p>
        <ul class="list-disc pl-6 space-y-1 mt-2">
          <li><strong>Google Chrome:</strong> Configuración &gt; Privacidad y seguridad &gt; Cookies de terceros.</li>
          <li><strong>Mozilla Firefox:</strong> Opciones &gt; Privacidad y seguridad &gt; Cookies y datos del sitio.</li>
          <li><strong>Apple Safari:</strong> Preferencias &gt; Privacidad &gt; Bloquear todas las cookies.</li>
          <li><strong>Microsoft Edge:</strong> Configuración &gt; Permisos del sitio &gt; Cookies y datos almacenados.</li>
        </ul>
        <p class="mt-3 text-xs text-slate-500">
          Nota: Si decides desactivar las cookies esenciales, no será posible iniciar sesión ni procesar compras en el carrito de FusaShop.
        </p>
      </section>

      <section class="border-t border-slate-200 pt-6">
        <h2 class="mb-2 text-lg font-bold text-slate-900">Contacto &amp; Soporte</h2>
        <p class="text-xs text-slate-600">
          Para dudas sobre nuestra política de cookies o el tratamiento de tus datos personales, puedes escribirnos mediante nuestro canal oficial de <a class="font-bold text-emerald-800 underline" href="{{ route('pqrs.create') }}">PQRS</a> o llamarnos al <a class="font-bold text-emerald-800 underline" href="tel:+573209106326">320 910 6326</a>.
        </p>
      </section>

    </div>

    <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-100 pt-6">
      <a class="rounded-xl bg-emerald-800 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-900 transition-colors" href="{{ route('privacy') }}">
        Política de Privacidad
      </a>
      <a class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors" href="{{ route('terms') }}">
        Términos y Condiciones
      </a>
      <a class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors" href="{{ route('returns') }}">
        Devoluciones y Reembolsos
      </a>
    </div>

  </div>
</article>
@endsection
