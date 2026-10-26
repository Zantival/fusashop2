@extends('layouts.app')

@section('title', 'Política de privacidad')

@section('content')
<article class="mx-auto max-w-4xl px-4 py-10 md:py-14 text-on-surface">
  <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm md:p-10">
    <p class="mb-2 text-xs font-extrabold uppercase tracking-wider text-emerald-800">FusaShop · Fusagasugá, Colombia</p>
    <h1 class="mb-3 text-3xl font-black text-emerald-950 md:text-4xl">Política de privacidad y tratamiento de datos</h1>
    <p class="mb-8 text-sm text-slate-500">Versión 2026-10-25 · Última actualización: 25 de octubre de 2026</p>

    <div class="space-y-7 text-sm leading-7 text-slate-700">
      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">1. Responsable y contacto</h2>
        <p>FusaShop es una plataforma de comercio electrónico operada desde Fusagasugá, Cundinamarca, Colombia. El NIT de la operación aún no ha sido asignado. No se presenta a FusaShop como una sociedad constituida ni como titular de una marca registrada. Para consultas sobre privacidad, tratamiento, actualización, corrección, revocatoria o supresión de datos, radica una solicitud en <a class="font-bold text-emerald-800 underline" href="{{ route('pqrs.create') }}">PQRS</a> o comunícate a los teléfonos <a class="font-bold text-emerald-800 underline" href="tel:+573209106326">320 910 6326</a> y <a class="font-bold text-emerald-800 underline" href="tel:+573223674364">322 367 4364</a>.</p>
        <p>La identidad legal completa del responsable deberá actualizarse cuando se formalice la entidad operadora y se asigne su NIT.</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">2. Datos que tratamos y por qué</h2>
        <ul class="list-disc space-y-2 pl-6">
          <li><strong>Cuenta:</strong> nombre, correo, contraseña protegida, rol y teléfono que aportas para crear, verificar y proteger la cuenta.</li>
          <li><strong>Compras:</strong> productos, cantidades, dirección y datos de contacto de entrega, cotización de envío, pagos, pedidos y comprobantes para completar la compra y atender soporte.</li>
          <li><strong>Comerciantes:</strong> datos de tienda, contacto, documentos KYC/RUT y datos de pago que se requieren para revisión y operación comercial.</li>
          <li><strong>Uso de la plataforma:</strong> mensajes, reseñas, PQRS, notificaciones y registros técnicos necesarios para prestar el servicio, prevenir abuso y resolver incidentes.</li>
          <li><strong>Promociones:</strong> solo enviaremos campañas promocionales por correo si marcas voluntariamente la casilla opcional de autorización. No condicionamos el uso del servicio a ese consentimiento y puedes cancelarlo desde el enlace de baja de cada campaña.</li>
        </ul>
        <p>FusaShop no solicita datos que no sean necesarios para las funciones descritas. No envíes contraseñas, códigos de verificación ni datos completos de tarjetas por chat o PQRS.</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">3. Finalidades, autorización y datos de comerciantes</h2>
        <p>Usamos los datos para crear y administrar cuentas; verificar correo y comerciantes; mostrar tiendas y productos; procesar pedidos, pagos, envíos y facturación; comunicar estados; atender reservas, reseñas, mensajes y PQRS; cumplir obligaciones legales; prevenir fraude y proteger la plataforma.</p>
        <p>Los datos de ventas identificables de cada comerciante pueden consultarse para gestionar sus pedidos y preparar sus propios reportes de desempeño. No se venden ni se publican para que otros comerciantes los usen en sus propios fines comerciales. Los análisis generales se agregan o desidentifican cuando es posible.</p>
        <p>La autorización para tratamiento necesario se registra al crear la cuenta; la autorización promocional es opcional y separada. El tratamiento se realiza de acuerdo con la Ley 1581 de 2012, el Decreto 1377 de 2013 y las demás normas aplicables.</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">4. Proveedores y transferencias necesarias</h2>
        <p>Según la operación que elijas, FusaShop puede compartir solo los datos necesarios con proveedores que prestan servicios para la plataforma: Wompi para pagos, Envia.com para cotización, guía y rastreo, Factus para facturación, Resend para correos y Google si eliges autenticación social. Contenido de mapas o videos puede ser cargado desde OpenStreetMap, Google Maps, YouTube o Vimeo cuando uses esas funciones. Esos proveedores pueden tratar datos técnicos de conexión conforme a sus políticas.</p>
        <p>Los datos de pago completos se gestionan en la pasarela correspondiente; FusaShop no debe solicitar ni almacenar el número completo de la tarjeta o su código de seguridad. La disponibilidad real de cada proveedor depende de su configuración y del ambiente (pruebas o producción).</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">5. Cookies y tecnologías similares</h2>
        <p>La aplicación usa cookies necesarias para sesión, seguridad y para recordar que viste el aviso. No se activan cookies publicitarias o de analítica propias. Los contenidos externos —por ejemplo, un video o mapa— pueden implicar solicitudes al proveedor cuando los cargas.</p>
        <p>Consulta detalles y opciones en la <a class="font-bold text-emerald-800 underline" href="{{ route('cookies') }}">Política de cookies</a>. Puedes borrar cookies desde el navegador; algunas funciones podrían requerir una nueva sesión.</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">6. Conservación y eliminación</h2>
        <p>Conservamos los datos mientras sean necesarios para prestar el servicio, administrar la cuenta, resolver reclamaciones y cumplir obligaciones legales, contables o de seguridad. Los registros de transacciones que deban conservarse por ley pueden mantenerse aun cuando se solicite cerrar la cuenta; los datos que ya no sean necesarios se eliminan o se desidentifican cuando corresponda.</p>
        <p>Puedes solicitar acceso, actualización, corrección, revocatoria o supresión a través de PQRS o los teléfonos indicados arriba. La solicitud se verifica antes de actuar y la supresión puede tener límites cuando exista un deber legal o contractual de conservación.</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">7. Tus derechos y atención de solicitudes</h2>
        <p>Puedes conocer, actualizar y rectificar tus datos; solicitar prueba de autorización e información sobre su uso; presentar consultas y reclamos; solicitar revocatoria o supresión cuando proceda; y acudir a la Superintendencia de Industria y Comercio (SIC) después de agotar el trámite ante FusaShop.</p>
        <p>Incluye en tu solicitud tu nombre, correo de la cuenta, el derecho que deseas ejercer y la información necesaria para verificar tu identidad. Las consultas y reclamos se atenderán dentro de los términos legales aplicables. No envíes documentos de identidad salvo que sean necesarios para verificar la titularidad y se soliciten por un canal adecuado.</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">8. Niñas, niños y adolescentes</h2>
        <p>El registro de una persona menor de edad requiere conocimiento y autorización de su madre, padre o representante legal y debe realizarse bajo su acompañamiento. No solicitamos fecha de nacimiento como requisito general. Si un representante considera que se creó una cuenta de un menor sin autorización, debe contactarnos para revisar y limitar el tratamiento.</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">9. Seguridad</h2>
        <p>Aplicamos controles técnicos y administrativos orientados a proteger las cuentas y la información. Ningún sistema puede prometer seguridad absoluta. Si reconoces un registro no solicitado, usa el enlace de reporte en el correo de verificación; si ya no lo tienes, contáctanos por PQRS o teléfono.</p>
      </section>

      <section>
        <h2 class="mb-2 text-xl font-extrabold text-slate-900">10. Cambios a esta política</h2>
        <p>Publicaremos la versión vigente en esta página y actualizaremos la fecha. Si un cambio requiere una nueva autorización, la solicitaremos por separado y no marcaremos casillas por ti.</p>
        <p>Normas de referencia: <a class="font-bold text-emerald-800 underline" href="https://www.sic.gov.co/" target="_blank" rel="noopener noreferrer">Superintendencia de Industria y Comercio</a> y <a class="font-bold text-emerald-800 underline" href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=44306" target="_blank" rel="noopener noreferrer">Ley 1480 de 2011</a>.</p>
      </section>
    </div>
    <div class="mt-9 flex flex-wrap gap-3 border-t border-slate-100 pt-6">
      <a class="rounded-xl bg-emerald-800 px-5 py-3 text-sm font-bold text-white" href="{{ route('pqrs.create') }}">Solicitar acceso o eliminación de mis datos</a>
      <a class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700" href="{{ route('cookies') }}">Política de cookies</a>
      <a class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700" href="{{ route('terms') }}">Términos y condiciones</a>
    </div>
  </div>
</article>
@endsection
