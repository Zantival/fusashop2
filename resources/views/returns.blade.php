@extends('layouts.app')

@section('title', 'Política de Devoluciones, Retracto y Reversión de Pagos')

@section('content')
<article class="mx-auto max-w-4xl px-4 py-10 md:py-14 text-on-surface">
  <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm md:p-10">
    <div class="flex items-center gap-2 mb-2">
      <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
      <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-800">FusaShop · Protección y Derechos del Consumidor</p>
    </div>
    <h1 class="mb-3 text-3xl font-black text-emerald-950 md:text-4xl">Política de Devoluciones, Retracto y Reversión de Pagos</h1>
    <p class="mb-8 text-sm text-slate-500">Versión 2026-10-25 · En estricto cumplimiento de la Ley 1480 de 2011 y el Decreto 587 de 2016 de la República de Colombia</p>

    <div class="space-y-8 text-sm leading-7 text-slate-700">
      
      <!-- Introducción -->
      <section class="rounded-2xl bg-emerald-50/60 p-5 border border-emerald-100 text-emerald-950">
        <p class="font-medium">
          En <strong>FusaShop</strong> nos comprometemos con la total transparencia en las compras de comercio electrónico. Si presentas algún inconveniente con un pedido realizado a través de nuestra plataforma, puedes radicar tu solicitud de forma gratuita y directa a través del módulo de <a class="font-bold text-emerald-800 underline" href="{{ route('pqrs.create') }}">PQRS</a>, indicando el número de referencia del pedido y los soportes correspondientes.
        </p>
      </section>

      <!-- 1. Derecho de Retracto -->
      <section>
        <h2 class="mb-3 text-xl font-extrabold text-slate-900 flex items-center gap-2">
          <span>1. Derecho de Retracto (Artículo 47, Ley 1480 de 2011)</span>
        </h2>
        <p>
          En todas las ventas de bienes y servicios a distancia realizadas mediante FusaShop, se entenderá pactado el <strong>derecho de retracto</strong> por parte del consumidor.
        </p>
        <ul class="list-disc pl-6 space-y-2 mt-2">
          <li><strong>Término para ejercerlo:</strong> El término máximo para solicitar el retracto es de <strong>cinco (5) días hábiles</strong> contados a partir de la entrega física del producto o de la celebración del contrato en caso de servicios.</li>
          <li><strong>Condición del bien:</strong> El comprador deberá devolver el producto al comercio emisor por los mismos medios y en las mismas condiciones en que lo recibió (sin uso, con empaques y etiquetas originales). Los costos de transporte y los demás que conlleve la devolución serán cubiertos conforme a la ley.</li>
          <li><strong>Plazo de reintegro:</strong> Una vez ejercido válidamente el derecho de retracto y verificado el retorno del producto, se reintegrará la totalidad del dinero pagado por el consumidor en un término no mayor a <strong>treinta (30) días calendario</strong> desde el momento en que se ejerció el derecho.</li>
        </ul>
        <div class="mt-3 p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
          <strong>Excepciones legales al retracto (Art. 47, num. 1 al 7):</strong> El derecho de retracto no aplicará para: productos perecederos (alimentos frescos o preparados de corta duración), bienes de uso personal o íntimo, bienes confeccionados o personalizados según especificaciones del consumidor, y servicios cuya prestación haya comenzado con acuerdo del cliente.
        </div>
      </section>

      <!-- 2. Reversión del Pago Electrónico -->
      <section>
        <h2 class="mb-3 text-xl font-extrabold text-slate-900 flex items-center gap-2">
          <span>2. Reversión de Pagos Electrónicos (Artículo 51, Ley 1480 y Decreto 587 de 2016)</span>
        </h2>
        <p>
          Cuando las ventas se procesen mediante mecanismos de comercio electrónico como tarjetas de crédito, débito, PSE, Nequi o pasarela Wompi, los participantes del proceso deberán reversar los pagos solicitados por el consumidor en cualquiera de los siguientes casos:
        </p>
        <ol class="list-decimal pl-6 space-y-2 mt-2">
          <li>Cuando el consumidor sea objeto de <strong>fraude</strong> o de una operación no solicitada.</li>
          <li>Cuando el producto adquirido <strong>no haya sido entregado</strong> dentro del plazo acordado.</li>
          <li>Cuando el producto entregado <strong>no corresponda a lo solicitado</strong> o resulte defectuoso.</li>
        </ol>
        <p class="mt-3">
          <strong>Procedimiento:</strong> El titular del medio de pago deberá presentar la queja formal ante FusaShop mediante <a class="font-bold text-emerald-800 underline" href="{{ route('pqrs.create') }}">PQRS</a> dentro de los <strong>cinco (5) días hábiles</strong> siguientes a la fecha en que tuvo conocimiento de la operación fraudulenta o en que debió recibir el producto, y notificar paralelamente al emisor del instrumento financiero utilizado.
        </p>
      </section>

      <!-- 3. Garantía Legal de Calidad e Idoneidad -->
      <section>
        <h2 class="mb-3 text-xl font-extrabold text-slate-900">3. Garantía Legal de Productos y Servicios</h2>
        <p>
          Todo producto comercializado por nuestros comercios aliados cuenta con la <strong>garantía legal</strong> de conformidad con el Estatuto del Consumidor. Si un producto llega averiado por transporte o presenta fallas de funcionamiento, el comerciante responderá por la reparación, cambio o reposición del bien. Para hacer efectiva una garantía, radica tu solicitud aportando fotografías y descripción del daño dentro de los términos indicados en la ficha del producto.
        </p>
      </section>

      <!-- 4. Cancelaciones de Órdenes -->
      <section>
        <h2 class="mb-3 text-xl font-extrabold text-slate-900">4. Cancelación de Pedidos</h2>
        <p>
          Puedes solicitar la cancelación voluntaria de tu pedido desde el panel de cliente siempre que la orden se encuentre en estado <strong>Pendiente de Pago</strong> o <strong>Pagado (sin despachar)</strong>. Una vez que el comercio genera la guía logística en Envia.com y el paquete es recolectado por la transportadora, aplicará el procedimiento de devolución o retracto posterior a la recepción.
        </p>
      </section>

      <!-- Canales y Normativa de Referencia -->
      <section class="border-t border-slate-200 pt-6">
        <h2 class="mb-2 text-lg font-bold text-slate-900">Marco Legal &amp; Canales Oficiales</h2>
        <p class="text-xs text-slate-500 mb-4">
          Normativa colombiana aplicable: Ley 1480 de 2011 (Estatuto del Consumidor), Decreto 587 de 2016 y circulares de la Superintendencia de Industria y Comercio (SIC).
        </p>
        <div class="flex flex-col sm:flex-row gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs">
          <div>
            <strong class="text-slate-900 block mb-1">📞 Líneas de Atención al Consumidor:</strong>
            <a href="tel:+573209106326" class="text-emerald-800 font-bold hover:underline">320 910 6326</a> · 
            <a href="tel:+573223674364" class="text-emerald-800 font-bold hover:underline">322 367 4364</a>
          </div>
          <div>
            <strong class="text-slate-900 block mb-1">📍 Sede de la Operación:</strong>
            Fusagasugá, Cundinamarca, Colombia
          </div>
        </div>
      </section>

    </div>

    <!-- Botones de Acción -->
    <div class="mt-9 flex flex-wrap gap-3 border-t border-slate-100 pt-6">
      <a class="rounded-xl bg-emerald-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-900 transition-colors" href="{{ route('pqrs.create') }}">
        📝 Radicar Solicitud de Devolución o Reversión (PQRS)
      </a>
      <a class="rounded-xl border border-slate-300 px-5 py-3.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors" href="{{ route('terms') }}">
        Términos y Condiciones
      </a>
      <a class="rounded-xl border border-slate-300 px-5 py-3.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors" href="{{ route('privacy') }}">
        Política de Privacidad
      </a>
    </div>

  </div>
</article>
@endsection
