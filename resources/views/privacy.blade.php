@extends('layouts.app')

@section('title', 'Política de Privacidad')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-4xl">
    <div class="bg-surface-container-lowest p-8 md:p-12 rounded-3xl shadow-card border border-outline-variant/20">
        <h1 class="text-3xl md:text-4xl font-black text-primary mb-8 font-headline">Política de Privacidad y Tratamiento de Datos</h1>
        <p class="text-sm text-on-surface-variant mb-8">Última actualización: 21 de octubre de 2026.</p>
        
        <div class="prose prose-slate max-w-none text-on-surface-variant leading-relaxed space-y-6">
            <section>
                <h2 class="text-xl font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">security</span>
                    1. Responsable del Tratamiento
                </h2>
                <p>FusaShop, con domicilio en Fusagasugá, Cundinamarca, es el responsable del tratamiento de sus datos personales recolectados a través de nuestra plataforma e-commerce.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">analytics</span>
                    2. Finalidades de la Recolección
                </h2>
                <p>Sus datos serán utilizados para las siguientes finalidades:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Gestión de la cuenta de usuario y autenticación.</li>
                    <li>Procesamiento de pedidos, pagos y envíos.</li>
                    <li>Comunicación entre consumidores y comerciantes (Chat).</li>
                    <li>Envío de notificaciones sobre el estado de sus compras.</li>
                    <li>Generación de estadísticas operativas y mejora de la plataforma, aplicando las finalidades específicas descritas en esta política.</li>
                    <li>Cumplimiento de obligaciones legales y contables.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">storefront</span>
                    3. Información de ventas de los comerciantes
                </h2>
                <p>Para operar el marketplace, FusaShop registra información de las ventas realizadas por cada comercio en la plataforma, como pedidos, productos vendidos, cantidades, valores, fechas y estados de las transacciones. El tratamiento necesario para procesar pedidos, pagos, envíos, reclamos y obligaciones legales se realiza para prestar esos servicios.</p>
                <p>Adicionalmente, el Administrador puede consultar y analizar las métricas individuales del comercio exclusivamente para preparar reportes de desempeño, identificar tendencias y ofrecer información, diagnósticos o recomendaciones que ayuden al comerciante a mejorar sus ventas en línea. Esta finalidad no autoriza vender esos datos, publicarlos ni compartir el desempeño identificable de un comercio con otros comerciantes o con terceros para sus propios fines comerciales. Los análisis generales de la plataforma se presentarán de forma agregada cuando sea posible.</p>
                <p>Al registrarse como comerciante y publicar o vender productos en FusaShop, el comerciante declara haber leído esta finalidad y acepta el tratamiento de sus datos de ventas para los fines aquí descritos, sin perjuicio de los derechos que le reconoce la ley. El tratamiento de datos personales de compradores incluidos en los pedidos se rige por las finalidades de compra, pago, envío, soporte y cumplimiento legal indicadas en esta política.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">gavel</span>
                    4. Derechos del Titular (Ley 1581 de 2012)
                </h2>
                <p>De acuerdo con la legislación colombiana, usted tiene derecho a:</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Conocer, actualizar y rectificar sus datos personales.</li>
                    <li>Solicitar prueba de la autorización otorgada.</li>
                    <li>Ser informado sobre el uso que se le ha dado a sus datos.</li>
                    <li>Presentar quejas ante la Superintendencia de Industria y Comercio.</li>
                    <li>Revocar la autorización y/o solicitar la supresión del dato cuando no se respeten los principios, derechos y garantías constitucionales y legales.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">cookie</span>
                    5. Uso de Cookies
                </h2>
                <p>FusaShop utiliza cookies técnicas necesarias para mantener la sesión, proteger formularios y solicitudes contra falsificación (CSRF), conservar preferencias esenciales y recordar la opción elegida en el aviso de cookies. Sin algunas de estas cookies, el inicio de sesión y otras funciones podrían no operar correctamente.</p>
                <p>Según la configuración actual del aplicativo, no se utilizan cookies publicitarias ni herramientas de seguimiento o analítica de terceros. Si en el futuro se habilitan cookies opcionales de analítica o publicidad, esta política y el aviso de cookies se actualizarán y se solicitará la elección correspondiente antes de activarlas. Puede borrar o bloquear cookies desde su navegador; al hacerlo, algunas funciones esenciales podrían dejar de funcionar.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">verified_user</span>
                    6. Referencias de seguridad
                </h2>
                <p>FusaShop toma como referencias de buenas prácticas el OWASP Application Security Verification Standard (ASVS), actualmente en su versión 5.0.0, y los marcos de gestión de seguridad y privacidad ISO/IEC 27001:2022 e ISO/IEC 27701:2025. Estas referencias orientan el diseño y la mejora de controles técnicos y organizativos; su mención no significa que FusaShop haya obtenido una certificación, una auditoría independiente o una verificación formal de conformidad con dichos marcos.</p>
                <p class="text-sm">Referencias: <a class="text-primary underline" href="https://asvs.owasp.org/" target="_blank" rel="noopener noreferrer">OWASP ASVS</a>, <a class="text-primary underline" href="https://www.iso.org/standard/27001" target="_blank" rel="noopener noreferrer">ISO/IEC 27001</a> y <a class="text-primary underline" href="https://www.iso.org/standard/27701" target="_blank" rel="noopener noreferrer">ISO/IEC 27701</a>.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">contact_support</span>
                    7. Contacto para Solicitudes de los Titulares
                </h2>
                <p>Para conocer, actualizar o rectificar sus datos, solicitar prueba de autorización, presentar consultas o reclamos, o pedir la revocatoria o supresión cuando proceda conforme a la ley, puede contactarnos a través de:</p>
                <div class="mt-4 p-4 bg-primary/5 rounded-2xl border border-primary/10">
                    <p class="font-bold text-primary">Correo electrónico: <span class="text-on-surface-variant font-normal">privacidad@fusashop.com.co</span></p>
                    <p class="font-bold text-primary">Dirección: <span class="text-on-surface-variant font-normal">Calle de la Tecnología, Fusagasugá</span></p>
                </div>
            </section>
        </div>

        <div class="mt-12 pt-8 border-t border-outline-variant/20 flex justify-center">
            <a href="{{ route('register') }}" class="px-8 py-3 bg-primary-gradient text-white font-bold rounded-2xl shadow-md hover:scale-105 transition-transform">
                Volver al Registro
            </a>
        </div>
    </div>
</div>
@endsection
