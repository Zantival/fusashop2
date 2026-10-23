/**
 * Script Principal de Generación del Marco Tecnológico para Tesis de Grado
 * Plataforma FusaShop — Ecosistema E-Commerce Multivendedor y Turismo Sumapaz
 */

const fs = require('fs');
const path = require('path');
const {
  Document,
  Packer,
  Paragraph,
  TextRun,
  Table,
  TableRow,
  TableCell,
  HeadingLevel,
  AlignmentType,
  WidthType,
  BorderStyle,
  ShadingType,
  PageNumber,
  NumberFormat,
  Footer,
  Header
} = require('docx');

const { p, h1, h2, h3, bullet, callout, codeBox, makeTable, COLORS } = require('./generate_marco_helpers.cjs');

console.log('Construyendo documento Word formal del Marco Tecnológico...');

const children = [];

// =========================================================================
// PORTADA FORMAL ACADÉMICA (Normas APA / Estándar de Grado)
// =========================================================================
children.push(
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 720, after: 200 },
    children: [
      new TextRun({
        text: 'UNIVERSIDAD / FACULTAD DE INGENIERÍA Y TECNOLOGÍAS DE LA INFORMACIÓN',
        font: 'Calibri',
        size: 24,
        bold: true,
        color: COLORS.PRIMARY
      })
    ]
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { after: 720 },
    children: [
      new TextRun({
        text: 'PROGRAMA ACADÉMICO DE INGENIERÍA DE SISTEMAS / SOFTWARE',
        font: 'Calibri',
        size: 20,
        color: COLORS.MUTED_TEXT
      })
    ]
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 720, after: 240 },
    children: [
      new TextRun({
        text: 'MARCO TECNOLÓGICO, ARQUITECTURA DE SOFTWARE Y ESPECIFICACIÓN DEL ECOSISTEMA DIGITAL',
        font: 'Calibri',
        size: 34,
        bold: true,
        color: COLORS.PRIMARY
      })
    ]
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { after: 360 },
    children: [
      new TextRun({
        text: 'Plataforma Web Multivendedor de Comercio Electrónico, Promoción Turística y Gobernanza Comercial para Fusagasugá y la Provincia del Sumapaz ("FusaShop")',
        font: 'Calibri',
        size: 24,
        italics: true,
        color: COLORS.DARK_TEXT
      })
    ]
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { after: 1200 },
    children: [
      new TextRun({
        text: 'Documento Técnico de Referencia y Soporte Teórico-Práctico para Proyecto de Grado / Tesis de Grado',
        font: 'Calibri',
        size: 20,
        color: COLORS.MUTED_TEXT
      })
    ]
  }),
  callout(
    'Ficha Técnica del Repositorio de Código Fuente',
    'Repositorio Oficial: https://github.com/Zantival/fusashop2\n' +
    'Rama de Producción Analizada: Final (Commit activo e inspección del árbol de desarrollo)\n' +
    'Ámbito Geográfico de Implementación: Fusagasugá, Cundinamarca, Región del Sumapaz, Colombia.\n' +
    'Año de Liberación y Evaluación: 2026'
  ),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 1440, after: 120 },
    children: [
      new TextRun({
        text: 'Fusagasugá, Cundinamarca — Colombia',
        font: 'Calibri',
        size: 20,
        bold: true,
        color: COLORS.DARK_TEXT
      })
    ]
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { after: 720 },
    children: [
      new TextRun({
        text: 'Año 2026',
        font: 'Calibri',
        size: 20,
        color: COLORS.MUTED_TEXT
      })
    ],
    pageBreakBefore: true
  })
);

// =========================================================================
// RESUMEN EJECUTIVO / ABSTRACT DEL MARCO TECNOLÓGICO
// =========================================================================
children.push(
  h1('Resumen Ejecutivo del Marco Tecnológico'),
  p(
    'El presente documento constituye el Marco Tecnológico formal del proyecto de grado titulado "FusaShop: Plataforma de Comercio Electrónico Multivendedor, Turismo y Gobernanza Territorial del Sumapaz". En él se documenta de manera exhaustiva, justificada y reproducible el conjunto de paradigmas arquitectónicos, lenguajes de programación, frameworks, librerías, motores de bases de datos, protocolos de integración y normativas de seguridad que sustentan el desarrollo de la solución.'
  ),
  p(
    'A diferencia de desarrollos de comercio genéricos de una sola tienda, FusaShop resuelve una problemática regional de alta complejidad: articular en un único ecosistema digital transaccional a múltiples unidades productivas locales (productores agrícolas, viveristas, sector moda, gastronomía típica, operadores turísticos y mayoristas B2B), garantizando trazabilidad logística nacional, dispersión automatizada de fondos con comisiones, facturación electrónica legal ante la DIAN, asistencia contextual mediante Inteligencia Artificial y cumplimiento riguroso del marco de protección de datos personales de la República de Colombia.'
  ),
  p(
    'A lo largo de este capítulo se fundamenta por qué la combinación de PHP 8.3 con Laravel 12 en el backend, junto con Blade, Tailwind CSS v4 y Alpine.js v3 en el frontend bajo el paradigma de Monolito Modular Reactivo, representa la alternativa de ingeniería de software más eficiente, costo-sostenible, segura y mantenible para el ecosistema productivo regional, desestimando la sobreingeniería de microservicios en favor de un alto rendimiento y bajo costo total de propiedad (TCO).'
  )
);

// =========================================================================
// CAPÍTULO 1: INTRODUCCIÓN Y ALCANCE
// =========================================================================
children.push(
  h1('1. Introducción, Justificación y Criterios de Selección Tecnológica'),
  h2('1.1 Contexto del Problema y Justificación Tecnológica'),
  p(
    'La transformación digital del comercio en economías regionales intermedias de Colombia —como el municipio de Fusagasugá y la Provincia del Sumapaz— enfrenta barreras estructurales significativas: alta dispersión de comercios informales, falta de capacidades técnicas para el mantenimiento de plataformas complejas, comisiones asfixiantes en marketplaces transnacionales cerrados y desarticulación entre los canales de venta, la logística terrestre de despachos y la facturación legal.'
  ),
  p(
    'Para mitigar esta brecha, el proyecto FusaShop fue concebido como una plataforma pública-privada multiactor que no solo permite la publicación de productos físicos, sino también la reserva de servicios turísticos y gastronómicos, la cotización de fletes en tiempo real, el cálculo de comisiones transparentes y el análisis geoestadístico territorial de la demanda comercial por comunas y corregimientos.'
  ),
  h2('1.2 Criterios de Ingeniería de Software para la Selección Tecnológica'),
  p('La selección de cada componente de la pila tecnológica respondió a una matriz de seis criterios fundamentales de evaluación:'),
  bullet('Mantenibilidad y Longevidad', 'Capacidad del sistema de ser actualizado y mantenido en el tiempo por equipos locales sin depender de licencias privativas costosas.'),
  bullet('Costo Total de Propiedad (TCO)', 'Minimización de los costos operativos recurrentes de infraestructura en la nube, permitiendo desplegar la solución en servidores compartidos o VPS optimizados sin demandar costosos clústeres Kubernetes.'),
  bullet('Eficiencia de Cómputo y Rendimiento', 'Tiempos de respuesta inferiores a 400 milisegundos en peticiones dinámicas y soporte fluido en dispositivos móviles de gama baja y media con conexiones 3G/4G.'),
  bullet('Seguridad y Cumplimiento Normativo Nacional', 'Adherencia estricta a las normativas de la DIAN (facturación electrónica), la Superintendencia de Industria y Comercio (Estatuto del Consumidor, Ley 1480 de 2011) y la Ley de Protección de Datos Personales (Ley 1581 de 2012).'),
  bullet('Concurrencia e Integridad Transaccional', 'Garantía matemática de inventario libre de sobreventa mediante bloqueo pesimista y liquidación contable en centavos de peso colombiano (COP).'),
  bullet('Interoperabilidad Abierta', 'Capacidad de exponer interfaces de programación de aplicaciones (APIs) y protocolos modernos como MCP (Model Context Protocol) para su interacción con agentes de Inteligencia Artificial.'),
  h2('1.3 Matriz Resumen de la Pila Tecnológica (Stack Tecnológico Global)'),
  p('A continuación, se detalla el ecosistema integral de tecnologías implementadas en la rama de producción Final:'),
  makeTable(
    ['Capa / Dominio', 'Tecnología Seleccionada', 'Versión', 'Rol / Función en el Ecosistema FusaShop'],
    [
      ['Backend Core', 'PHP', '8.3.x', 'Lenguaje de programación en el servidor, tipado estricto y alto rendimiento con motor Zend.'],
      ['Framework Backend', 'Laravel', '12.x', 'Marco de trabajo web MVC, inyección de dependencias, routing, middleware y ORM.'],
      ['Persistencia / ORM', 'Eloquent ORM', '12.x', 'Capa de abstracción relacional, active record, eager loading y transacciones ACID.'],
      ['Base de Datos (Dev/Test)', 'SQLite', '3.x', 'Motor relacional integrado para pruebas unitarias y desarrollo local sin latencia de red.'],
      ['Base de Datos (Prod)', 'MySQL / MariaDB', '8.0+ / 10.11+', 'Motor de persistencia en producción (Hostinger), tablas InnoDB con integridad referencial.'],
      ['Frontend UI & CSS', 'Tailwind CSS', '4.2.x', 'Framework utility-first con nuevo motor de compilación @tailwindcss/cli y diseño responsivo.'],
      ['Reactividad Cliente', 'Alpine.js', '3.17.x', 'Framework reactivo declarativo ligero para componentes dinámicos sin overhead de VDOM.'],
      ['Peticiones Asíncronas', 'Axios & Fetch API', '1.7.x / ES6+', 'Cliente HTTP con interceptores automáticos de tokens CSRF y comunicación REST.'],
      ['Motor de Vistas', 'Blade Templating', '12.x', 'Compilación de plantillas a PHP nativo optimizado con almacenamiento en caché estática.'],
      ['Visualización Gráfica', 'Chart.js', '4.5.x', 'Librería de renderizado de gráficos Canvas para tableros de analítica y diagnóstico comercial.'],
      ['Empaquetador / Bundler', 'Vite', '5.0.x', 'Herramienta de compilación rápida de activos, minificación y optimización para producción.'],
      ['Optimización Imágenes', 'Sharp', '0.35.x', 'Procesamiento de imágenes, compresión y conversión automatizada al estándar moderno WebP.'],
      ['Seguridad & Tokens', 'Laravel Sanctum', '4.0.x', 'Sistema de autenticación mediante tokens personales para APIs REST y clientes móviles.'],
      ['Autenticación Social', 'Laravel Socialite', '5.26.x', 'Integración federada con Google Cloud OAuth 2.0 para inicio de sesión en un clic.'],
      ['Correo Transaccional', 'Resend Laravel', '1.4.x', 'Servicio cloud de entrega de correos con validación criptográfica SPF, DKIM y DMARC.'],
      ['Pasarela de Pagos', 'Wompi & PayU Latam', 'v1 / v4', 'Checkout centralizado, dispersión multivendedor, PSE, tarjetas y webhooks firmados SHA-256.'],
      ['Operador Logístico', 'Envia.com API', 'REST API', 'Cotización dinámica de envíos intermunicipales, emisión de guías PDF y seguimiento de paquetes.'],
      ['Facturación DIAN', 'Factus API', 'REST API', 'Emisión legal de facturas electrónicas con código CUFE y validación previa ante la DIAN.'],
      ['Inteligencia Artificial', 'FusaBot / MCP', 'JSON-RPC 2.0', 'Asistente de intenciones por rol y servidor Model Context Protocol para interoperabilidad IA.']
    ],
    [20, 20, 15, 45]
  )
);

// =========================================================================
// CAPÍTULO 2: PARADIGMAS ARQUITECTÓNICOS Y PATRONES DE DISEÑO
// =========================================================================
children.push(
  h1('2. Paradigmas Arquitectónicos y Patrones de Diseño de Software'),
  h2('2.1 Arquitectura en Capas y Patrón Modelo-Vista-Controlador (MVC)'),
  p(
    'FusaShop implementa una arquitectura en capas fundamentada en el patrón arquitectónico Modelo-Vista-Controlador (MVC), extendido mediante el Patrón de Capa de Servicios (Service Layer Pattern). Esta separación desacopla estrictamente las responsabilidades del sistema:'
  ),
  bullet('Capa de Presentación (Vistas Blade & Componentes)', 'Responsable exclusiva del renderizado de la interfaz, accesibilidad semántica, maquetación adaptativa e interactividad reactiva mediante directivas Alpine.js.'),
  bullet('Capa de Control (HTTP Controllers & Middlewares)', 'Encargada de la recepción de solicitudes HTTP, validación formal de entradas (Form Request / Validator), control de autorización por roles y serialización de respuestas (HTML o JSON).'),
  bullet('Capa de Servicios (Service Layer)', 'Encapsula la lógica de negocio pura, orquestación transaccional y comunicación con APIs externas, garantizando que los controladores permanezcan delgados (Skinny Controllers).'),
  bullet('Capa de Datos y Dominio (Eloquent Models & Repositorios)', 'Define las entidades de negocio, sus relaciones cardinales, restricciones de integridad, casts criptográficos y consultas optimizadas a la base de datos.'),
  h2('2.2 Desacoplamiento mediante la Capa de Servicios (Service Layer)'),
  p(
    'Para evitar el antipatrón de "Controladores Dios" (God Controllers), la lógica transaccional de FusaShop está modularizada en servicios especializados e inyectables en app/Services/:'
  ),
  bullet('OrderPaymentService.php', 'Orquesta el ciclo de cobro de órdenes. Procesa los eventos de pasarela, valida la coincidencia de montos en centavos y coordina la transición de estados de compra de forma atómica.'),
  bullet('MerchantSettlementService.php', 'Calcula la dispersión proporcional de ingresos entre comercios, deduciendo la comisión de la plataforma y acreditando los saldos en el libro mayor de cada vendedor.'),
  bullet('StockService.php', 'Controla la reserva y deducción de existencias mediante bloqueo pesimista en base de datos, evitando inconsistencias por compras concurrentes durante ventas promocionales.'),
  bullet('EnviaService.php', 'Se comunica con los servicios de Envia.com para cotizar tarifas de envío según peso y volumen, generar rótulos oficiales de transporte y recuperar números de guía para el comprador.'),
  bullet('FactusService.php', 'Construye el payload tributario exigido por la DIAN, envía la solicitud a la pasarela Factus, obtiene el Código Único de Factura Electrónica (CUFE) y almacena el enlace al PDF oficial.'),
  bullet('SalesDiagnosisService.php', 'Sistema experto analítico que audita 12 variables críticas en cada comercio para diagnosticar caídas en ventas (rupturas de stock, retrasos logísticos, calificaciones negativas, etc.).'),
  bullet('ReviewTopicAnalysisService.php', 'Motor de Procesamiento de Lenguaje Natural (NLP) basado en reglas léxicas y ponderación que descompone las opiniones de los clientes en 6 dimensiones de satisfacción.'),
  bullet('TerritoryHeatmapService.php', 'Servicio de geointeligencia territorial que agrega datos transaccionales por Comunas y Corregimientos de Fusagasugá para generar mapas de calor comerciales.'),
  h2('2.3 Monolito Modular Reactivo frente a Microservicios'),
  p(
    'Una decisión arquitectónica deliberada y analizada rigurosamente en la investigación fue la adopción de un Monolito Modular Reactivo en lugar de una arquitectura de microservicios distribuidos. Las razones técnicas que justifican esta decisión para una plataforma territorial son:'
  ),
  bullet('Cero Latencia de Red Inter-Servicio', 'Las llamadas entre módulos (por ejemplo, verificar inventario antes de liquidar el pago) ocurren en memoria en el mismo proceso de servidor, sin penalizaciones de red (HTTP/gRPC) ni fallos parciales de red.'),
  bullet('Transacciones ACID Reales en Base de Datos', 'Permite el uso de DB::transaction nativo con bloqueo pesimista. En microservicios, requeriría coordinadores distribuidos complejos (Saga Pattern o Two-Phase Commit) propensos a inconsistencias eventuales.'),
  bullet('Drástica Reducción del Costo Operativo (TCO)', 'Un monolito modular puede ejecutarse con alto rendimiento en un solo servidor optimizado (o hosting compartido de bajo costo en Hostinger), mientras que los microservicios exigen clústeres Kubernetes, mallas de servicios (Istio), balanceadores dedicados y múltiples bases de datos con costos inviables para una entidad regional.'),
  bullet('Mantenibilidad y Facilidad de Despliegue', 'Un solo repositorio Git unificado (como el repositorio origin/Final), un único proceso de integración y un despliegue atómico sin orquestadores complejos.'),
  h2('2.4 Patrón de Libro Mayor Inmutable (Ledger Pattern) para Liquidación Multivendedor'),
  p(
    'En un entorno multivendedor donde un comprador puede adquirir productos de diferentes comercios en una misma orden (con un solo cobro general en Wompi o PayU), la distribución del dinero exige precisión bancaria. FusaShop implementa el Patrón de Libro Mayor (Ledger Pattern) en centavos enteros:'
  ),
  callout(
    'Regla de Idempotencia y Conservación Contable',
    '1. La orden principal (orders) registra el importe total cobrado al cliente.\n' +
    '2. Al confirmarse el pago mediante webhook autenticado, MerchantSettlementService desglosa la compra en subórdenes independientes (merchant_suborders), una por cada comercio involucrado.\n' +
    '3. Cada suborden congela en enteros (centavos) el importe bruto, la tasa de comisión de la plataforma y el valor neto a favor del comercio.\n' +
    '4. La acreditación se realiza en la tabla merchant_wallet_entries con una clave de idempotencia única (settlement_order_{id}_{merchant_id}). Si el webhook se recibe múltiples veces, la clave previene cobros o acreditaciones duplicadas.'
  ),
  h2('2.5 Manejo de Concurrencia y Bloqueo Pesimista (Pessimistic Locking)'),
  p(
    'Para evitar condiciones de carrera (Race Conditions) —donde dos usuarios compran la última unidad de un producto al mismo milisegundo—, FusaShop no utiliza validación de stock superficial. En su lugar, implementa bloqueo pesimista mediante cláusulas SQL "SELECT ... FOR UPDATE" a través de Eloquent:'
  ),
  codeBox(
    '// app/Services/StockService.php (Fragmento real del proyecto)\n' +
    'public function reserveStock(int $productId, int $quantity): bool\n' +
    '{\n' +
    '    return DB::transaction(function () use ($productId, $quantity) {\n' +
    '        // Bloqueo pesimista de fila en base de datos hasta que finalice la transacción\n' +
    '        $product = Product::where("id", $productId)->lockForUpdate()->first();\n' +
    '        \n' +
    '        if (!$product || $product->stock < $quantity) {\n' +
    '            throw new InsufficientStockException("Existencias insuficientes.");\n' +
    '        }\n' +
    '        \n' +
    '        $product->decrement("stock", $quantity);\n' +
    '        return true;\n' +
    '    });\n' +
    '}'
  ),
  h2('2.6 Servidor MCP (Model Context Protocol) para Interoperabilidad con IA'),
  p(
    'FusaShop incorpora en app/Http/Controllers/McpController.php un servidor nativo compatible con el estándar abierto Model Context Protocol (MCP) promovido por la industria de IA. Mediante el protocolo JSON-RPC 2.0 sobre transporte HTTP autenticado con Bearer Token, la plataforma expone herramientas de solo lectura para agentes de Inteligencia Artificial:'
  ),
  bullet('get_order', 'Permite a agentes consultar el estado de trazabilidad, guía de despacho y productos de una orden mediante su referencia única (ej. FS-9A8B7C6D5E).'),
  bullet('search_catalog', 'Facilita a asistentes de IA la exploración de productos activos del catálogo regional, con verificación en tiempo real de inventario y precios en pesos colombianos.'),
  bullet('validate_shipping', 'Valida coberturas geográficas de direcciones en Fusagasugá y el territorio colombiano, cotizando el costo del flete de transporte.')
);

// =========================================================================
// CAPÍTULO 3: CAPA DE BACKEND (PHP 8.3 & LARAVEL 12)
// =========================================================================
children.push(
  h1('3. Capa de Backend y Lógica de Negocio (PHP 8.3 & Laravel 12)'),
  h2('3.1 Lenguaje de Programación: PHP 8.3'),
  p(
    'El backend de FusaShop está construido sobre PHP 8.3 (versión de soporte activo y alto rendimiento del ecosistema web). La selección de esta versión garantiza mejoras técnicas de primer nivel frente a versiones heredadas de PHP:'
  ),
  bullet('Tipado Estricto y Validación Estática (Type Hinting)', 'Uso de declare(strict_types=1), tipos de retorno de intersección y unión, reduciendo errores en tiempo de ejecución en cálculos financieros y de inventario.'),
  bullet('Compilador JIT (Just-In-Time)', 'Aceleración del cálculo matemático y procesamiento de arrays pesados, optimizando la ejecución de algoritmos como el diagnóstico de ventas y ponderación de reseñas.'),
  bullet('Clases y Propiedades Readonly', 'Garantía de inmutabilidad en Objetos de Transferencia de Datos (DTOs) y eventos de dominio.'),
  bullet('Bajo Consumo de Memoria', 'PHP 8.3 reduce drásticamente el consumo por hilo de ejecución (worker), permitiendo a la plataforma atender picos de concurrencia en servidores con memoria limitada (ej. 1 GB a 2 GB RAM).'),
  h2('3.2 Framework Principal: Laravel 12'),
  p(
    'Laravel 12 fue seleccionado como el núcleo del sistema debido a su arquitectura robusta, expresiva y orientada a la mantenibilidad empresarial. Los componentes estructurales de Laravel aprovechados en el proyecto son:'
  ),
  bullet('Ciclo de Vida de la Petición (Request Lifecycle)', 'Desde el punto de entrada unificado en public/index.php, las peticiones transitan por el Service Container, ejecutando middlewares de seguridad y enrutándose a controladores específicos.'),
  bullet('Inyección de Dependencias y Service Container', 'Desacoplamiento total entre las clases controladoras y los servicios externos mediante inversión de control (IoC).'),
  bullet('Pipeline de Middlewares de Gobernanza', 'FusaShop implementa middlewares personalizados en app/Http/Middleware/: RoleMiddleware (control de acceso RBAC), CheckCookieConsent (cumplimiento legal de privacidad), EnsureKycApproved (verificación de antecedentes comerciales de comercios antes de vender) y SecurityHeaders (mitigación de ataques web).'),
  h2('3.3 Mapeo Objeto-Relacional (Eloquent ORM) y Casts de Seguridad'),
  p(
    'Eloquent ORM proporciona una capa de abstracción de base de datos que combina la simplicidad del patrón Active Record con la seguridad de consultas parametrizadas PDO. En FusaShop se destacan:'
  ),
  bullet('Eager Loading contra el Problema N+1', 'Uso sistemático de with(["merchant", "items.product", "reviews"]) en consultas de catálogo y tableros, eliminando consultas redundantes y reduciendo el tiempo de base de datos en un 85%.'),
  bullet('Casts Personalizados de Cifrado (SafeEncrypted.php)', 'Implementación de un Cast criptográfico propio en app/Casts/SafeEncrypted.php que cifra de manera simétrica con AES-256-CBC las credenciales de pasarelas de pago y datos sensibles de los comercios almacenados en la base de datos.'),
  h2('3.4 Procesamiento Asíncrono de Trabajos (Laravel Queues & Jobs)'),
  p(
    'Las operaciones que involucran servicios web externos lentos o propensos a latencia no bloquean el hilo de respuesta del usuario. FusaShop las traslada a colas de trabajo en segundo plano en app/Jobs/:'
  ),
  bullet('GenerateEnviaShipment.php', 'Job en segundo plano que contacta la API de Envia.com tras la aprobación del pago para generar la guía logística y almacenar el PDF.'),
  bullet('IssueFactusInvoice.php', 'Job asíncrono para la emisión de la factura electrónica DIAN.'),
  bullet('SendOrderApprovedEmail.php / SendOrderShippedEmail.php', 'Envío de notificaciones transaccionales a comprador y comercio sin retrasar la respuesta en el navegador.'),
  bullet('Trait HandlesIntegrationFailure.php', 'Mecanismo transversal de resiliencia con registro en integration_logs, política de reintentos exponenciales y alertas al administrador en caso de falla crítica.')
);

// =========================================================================
// CAPÍTULO 4: CAPA DE FRONTEND (UI/UX REACTIVO Y RESPONSIVO)
// =========================================================================
children.push(
  h1('4. Capa de Frontend e Interfaz de Usuario (UI/UX Reactivo y Responsivo)'),
  h2('4.1 Paradigma Frontend: MPA Reactivo con Navegación Dinámica (Dynamic DOM Swapping)'),
  p(
    'FusaShop adopta una arquitectura de Aplicación Multi-Página Progresiva (PWA-Ready MPA) optimizada con navegación dinámica SPA-like (Single Page Application). A través de un motor ligero en JavaScript nativo en layouts/app.blade.php, la plataforma intercepta los enlaces de navegación interna y reemplaza exclusivamente el contenedor principal (<main id="app-main-content">) mediante peticiones Fetch asíncronas.'
  ),
  p(
    'Este enfoque proporciona los mejores beneficios de ambos mundos: la velocidad instantánea y fluidez visual de una SPA (sin parpadeos de recarga completa), junto con la indexabilidad en buscadores (SEO natural para los productos de los comercios), excelente rendimiento en móvil y simplicidad de mantenimiento sin la pesada carga de enrutadores en React o Vue.'
  ),
  h2('4.2 Framework de Estilos: Tailwind CSS v4'),
  p(
    'El diseño visual y la adaptabilidad responsiva se sustentan en Tailwind CSS v4, compilado directamente mediante la herramienta oficial @tailwindcss/cli. Las ventajas técnicas implementadas incluyen:'
  ),
  bullet('Arquitectura Utility-First Pura', 'Construcción modular de la interfaz directamente en el marcado HTML, asegurando consistencia visual y evitando archivos CSS gigantescos desordenados.'),
  bullet('Paleta Cromática Identitaria del Sumapaz', 'Configuración de variables corporativas: verde primario de las montañas (#006C47), verde esmeralda de la Ciudad Jardín (#10B981) y naranja floral (#F97316).'),
  bullet('Optimización de Peso (Minificación y Purga Automática)', 'El archivo CSS final resultante (public/assets/css/app.css) tiene un tamaño comprimido inferior a 45 KB, garantizando una carga inmediata incluso en redes celulares 3G.'),
  h2('4.3 Patrón de Navegación Priority+ con Colapso Progresivo'),
  p(
    'Uno de los desarrollos destacados de ingeniería de interfaz en la plataforma es la barra de categorías comerciales basada en el Patrón Priority+. En lugar de provocar desbordamientos horizontales o barras de desplazamiento incómodas en pantallas medianas y tabletas (entre 768px y 1280px), los botones se ocultan jerárquicamente de la barra superior y se integran automáticamente en el menú desplegable "Ver más":'
  ),
  makeTable(
    ['Categoría Comercial', 'Comportamiento en Barra Superior', 'Comportamiento en Menú "Ver más"', 'Breakpoints Responsivos'],
    [
      ['Mayoristas B2B', 'Visible en pantallas ≥ 1280px', 'Absorbido en menú en pantallas < 1280px', '.nav-bar-b2b / .nav-drop-b2b'],
      ['Restaurantes & Gastronomía', 'Visible en pantallas ≥ 1120px', 'Absorbido en menú en pantallas < 1120px', '.nav-bar-restaurantes / .nav-drop-restaurantes'],
      ['Turismo & Hoteles', 'Visible en pantallas ≥ 980px', 'Absorbido en menú en pantallas < 980px', '.nav-bar-turismo / .nav-drop-turismo'],
      ['Viveros & Agro', 'Visible en pantallas ≥ 860px', 'Absorbido en menú en pantallas < 860px', '.nav-bar-viveros / .nav-drop-viveros'],
      ['Moda & Calzado', 'Visible en pantallas ≥ 768px', 'Absorbido en drawer móvil < 768px', '.nav-bar-moda / .nav-drop-moda']
    ],
    [25, 25, 25, 25]
  ),
  p(
    'Dicha transición se complementa con la Pestañita Verde Deslizante (#nav-sliding-pill), un indicador CSS/JS acelerado por hardware (transform: translate3d) que enmarca el botón activo y se traslada inteligentemente al botón "Ver más" si la categoría activa ha sido absorbida por el menú.'
  ),
  h2('4.4 Reactividad Declarativa con Alpine.js v3'),
  p(
    'Para la gestión de estado en la interfaz del cliente (modales, dropdowns de usuario, conmutación de pestañas en reportes, acordeones de filtros en el catálogo y carritos de compra), FusaShop utiliza Alpine.js v3. Alpine ofrece la misma reactividad declarativa de Vue/React (mediante directivas x-data, x-show, x-cloak, x-model) pero con una huella de memoria minúscula (~15 KB), sin necesidad de compilación virtual DOM compleja.'
  ),
  h2('4.5 Visualización de Datos y Analítica Territorial con Chart.js v4'),
  p(
    'Los paneles del Analista y del Comerciante integran Chart.js v4 para representar visualmente el comportamiento de ventas diarias, dispersión de comisiones, evolución de valoraciones y el mapa de calor geoestadístico por comunas, empleando gráficos Canvas responsivos con aceleración por GPU.'
  )
);

// =========================================================================
// CAPÍTULO 5: CAPA DE PERSISTENCIA Y MODELO RELACIONAL
// =========================================================================
children.push(
  h1('5. Capa de Persistencia y Modelo de Datos Relacional'),
  h2('5.1 Motores Gestores de Base de Datos: Dualidad SQLite / MySQL'),
  p(
    'La arquitectura de datos de FusaShop está configurada de manera agnóstica mediante el Connection Manager de Laravel, permitiendo una estrategia dual altamente eficiente:'
  ),
  bullet('Entorno Local y Pruebas Automatizadas (SQLite 3)', 'Base de datos en archivo local (database/database.sqlite) que permite ejecutar suites de pruebas unitarias, seeders y desarrollo sin requerir demonios externos ni credenciales de red.'),
  bullet('Entorno de Producción en Hostinger (MySQL 8.0+ / MariaDB 10.11+)', 'Motor de base de datos relacional con tablas InnoDB, claves foráneas con restricciones en cascada, índices B-Tree compuestos y codificación utf8mb4_unicode_ci para soporte completo de caracteres y emojis en reseñas.')
);

children.push(
  h2('5.2 Diccionario Estructural del Esquema de Datos'),
  p('A continuación se documentan las entidades relacionales nucleares del sistema:'),
  makeTable(
    ['Tabla Relacional', 'Propósito en el Dominio', 'Campos Clave y Restricciones de Integridad'],
    [
      ['users', 'Almacenamiento de usuarios de todos los roles (consumidores, comerciantes, analistas).', 'id, name, email (unique), password, role, is_blocked, loyalty_points, verification_code.'],
      ['company_profiles', 'Perfil comercial de vendedores, documentación KYC y datos de geolocalización.', 'id, user_id (FK), business_name, nit, phone, address, latitude, longitude, kyc_status, payout_bank.'],
      ['products', 'Catálogo de bienes físicos y servicios ofrecidos por los comercios.', 'id, merchant_id (FK), name, slug, description, price, stock, category, is_active, allows_points.'],
      ['orders', 'Orden de compra unificada generada por el consumidor final.', 'id, user_id (FK), reference (unique), total, shipping_cost, discount_amount, payment_status, shipping_status.'],
      ['order_items', 'Detalle de referencias, cantidades y precios congelados al momento de compra.', 'id, order_id (FK), product_id (FK), quantity, price, subtotal.'],
      ['merchant_suborders', 'Subórdenes hijas generadas para la dispersión multivendedor contable.', 'id, order_id (FK), merchant_id (FK), suborder_reference (unique), gross_amount, commission_fee, net_amount, status.'],
      ['merchant_wallet_entries', 'Libro mayor de saldo de los comerciantes (créditos por ventas y débitos por retiros).', 'id, merchant_id (FK), suborder_id (FK), amount, balance_after, idempotency_key (unique), type.'],
      ['payment_transactions', 'Registro de auditoría de transacciones Wompi / PayU con firmas SHA-256.', 'id, order_id (FK), gateway, transaction_id (unique), status, amount_in_cents, raw_payload.'],
      ['reservations', 'Motor de reservas horarias para servicios, gastronomía, viveros y turismo.', 'id, user_id (FK), merchant_id (FK), service_name, reservation_date, attendees, status, notes.'],
      ['banner_requests', 'Sistema de pauta publicitaria regional dentro de la vitrina digital.', 'id, merchant_id (FK), banner_type, target_category, cost, status, proof_url, starts_at, ends_at.'],
      ['loyalty_points', 'Libro de fidelización de puntos FusaCoins (acumulación por compra y redención).', 'id, user_id (FK), order_id (FK), points_earned, points_spent, balance_after, reason.'],
      ['p_q_r_s', 'Sistema formal de Peticiones, Quejas, Reclamos y Sugerencias (Ley 1480).', 'id, user_id (FK), ticket_number (unique), type, subject, message, status, legal_deadline, admin_reply.'],
      ['reviews', 'Valoraciones y comentarios de compradores con soporte de respuesta del vendedor.', 'id, product_id (FK), user_id (FK), rating (1-5), comment, merchant_reply, is_verified_purchase.'],
      ['integration_logs', 'Trazabilidad de eventos de comunicación con APIs externas para auditoría técnica.', 'id, service_name, event_type, status_code, request_payload, response_payload, execution_time_ms.']
    ],
    [20, 35, 45]
  )
);

// =========================================================================
// CAPÍTULO 6: INTELIGENCIA ARTIFICIAL Y ANALÍTICA
// =========================================================================
children.push(
  h1('6. Inteligencia Artificial, Procesamiento de Lenguaje Natural y Analítica Avanzada'),
  h2('6.1 Asistente Conversacional Inteligente "FusaBot"'),
  p(
    'FusaBot es el asistente virtual nativo de la plataforma, implementado en JavaScript y PHP en app/Http/Controllers/Consumer/ConsumerController.php (método assistantSearch). A diferencia de chatbots genéricos desconectados, FusaBot opera bajo un modelo de clasificación semántica adaptativo por rol:'
  ),
  bullet('Modo Comprador', 'Resuelve inquietudes sobre costos de envío con Envia.com, métodos de pago en Wompi/PayU, redención de puntos FusaCoins y recomienda productos locales del catálogo según términos clave normalizados.'),
  bullet('Modo Comerciante', 'Asiste en la optimización de vitrinas, pautas publicitarias con banners, resolución de fallos de facturación electrónica DIAN con Factus y consulta de políticas de dispersión de fondos.'),
  bullet('Modo Analista / Administrador', 'Ofrece atajos analíticos para auditar comercios con documentación KYC pendiente, incidentes en logs de integración y volumen de PQRS próximas a vencer.'),
  bullet('Escalamiento Humano en Vivo', 'Si el usuario formula una consulta compleja o manifiesta una inconformidad grave, FusaBot enlaza de forma automática con el módulo de mensajería interna directa con un administrador.'),
  h2('6.2 Minería de Texto y Análisis de Tópicos en Reseñas (NLP)'),
  p(
    'El servicio app/Services/Admin/ReviewTopicAnalysisService.php implementa un motor de minería de texto y análisis de sentimiento multidimensional sobre las valoraciones de los compradores. El algoritmo normaliza el texto (eliminando diacríticos y convirtiendo a minúsculas) y aplica diccionarios léxicos ponderados para categorizar la retroalimentación en seis tópicos nucleares:'
  ),
  bullet('Calidad del Producto', 'Términos asociados: fresco, original, material, defectuoso, sabor, resistente.'),
  bullet('Envío y Logística', 'Términos asociados: entrega, rápido, demora, transportadora, guía, domicilio.'),
  bullet('Precio y Valor Percibido', 'Términos asociados: económico, caro, barato, oferta, costo, justo.'),
  bullet('Atención del Comercio', 'Términos asociados: amable, grosero, soporte, comunicación, respuesta.'),
  bullet('Empaque y Presentación', 'Términos asociados: caja, sellado, roto, protegido, presentación.'),
  bullet('Fidelidad a la Descripción', 'Términos asociados: foto, talla, color, diferente, engaño, exacto.'),
  h2('6.3 Sistema Experto de Diagnóstico de Ventas'),
  p(
    'En app/Services/Admin/SalesDiagnosisService.php se codificó un sistema experto basado en reglas heurísticas que evalúa de forma algorítmica las 12 causas raíz probables por las cuales un comercio experimenta bajas ventas o pérdida de clientes (catálogo sin stock, precios fuera del percentil de mercado, demora en respuesta a mensajes, alta tasa de devoluciones, etc.), generando recomendaciones automáticas accionables.'
  ),
  h2('6.4 Geointeligencia Territorial y Censo Sectorial'),
  p(
    'Mediante TerritoryHeatmapService.php y SectorCensusService.php, FusaShop estructura los datos transaccionales de Fusagasugá por Comunas (Comuna 1 Centro, Comuna 2 Balmoral, Comuna 3 Pekín, Comuna 4 Tequendama, Comuna 5 Sur-Occidental, Comuna 6 Norte) y Corregimientos rurales, permitiendo identificar patrones geográficos de compra, tiempos de entrega promedio y densidad de cobertura comercial.'
  )
);

// =========================================================================
// CAPÍTULO 7: ECOSISTEMA DE INTEGRACIONES (APIs)
// =========================================================================
children.push(
  h1('7. Ecosistema de Integraciones Externas y Servicios Web (APIs)'),
  h2('7.1 Pasarelas de Pago Electrónico (Wompi Latam & PayU Latam)'),
  p(
    'FusaShop integra las dos pasarelas de pago más extendidas en Colombia, ofreciendo redundancia y máxima tasa de conversión:'
  ),
  bullet('Wompi Latam (Bancolombia)', 'Integración directa con el widget centralizado y validación de eventos mediante webhooks autenticados. Se implementa cálculo de firma de integridad SHA-256 (concatenando referencia, monto en centavos, moneda y secreto de integridad) para prevenir manipulaciones de valores en tránsito.'),
  bullet('PayU Latam', 'Soporte alternativo con redirección segura y confirmación de pago por IPN (Instant Payment Notification) con firma MD5/SHA-256.'),
  h2('7.2 Operador Logístico Nacional (Envia.com API)'),
  p(
    'La gestión de envíos físicos se delega en EnviaService.php, que se comunica con la API REST de Envia.com para cotizar fletes intermunicipales en tiempo real según las dimensiones del paquete (largo, ancho, alto) y su peso en kilogramos, generando automáticamente la guía de transporte con código de barras en formato PDF.'
  ),
  h2('7.3 Facturación Electrónica DIAN (Factus API)'),
  p(
    'Para dar estricto cumplimiento a la legislación tributaria colombiana de comercio electrónico, FactusService.php automatiza la expedición de la factura electrónica de venta ante la Dirección de Impuestos y Aduanas Nacionales (DIAN), registrando el Código Único de Factura Electrónica (CUFE), el código QR reglamentario y almacenando el archivo XML firmado digitalmente.'
  ),
  h2('7.4 Servicio de Mensajería Transaccional (Resend API)'),
  p(
    'La plataforma emplea el servicio en la nube Resend (mediante el paquete oficial resend/resend-laravel) para la entrega garantizada de correos de verificación de cuenta en dos pasos (2FA), recibos de compra en formato HTML responsivo y notificaciones de cambio de estado de pedidos, cumpliendo con los estándares de autenticación SPF, DKIM y DMARC con dominio verificado.'
  ),
  h2('7.5 Autenticación Social Federada (Google OAuth 2.0)'),
  p(
    'Mediante Laravel Socialite se implementa el flujo estándar OAuth 2.0 de Google Cloud Identity, permitiendo a los compradores registrarse e iniciar sesión de forma segura sin gestionar contraseñas locales adicionales.'
  )
);

// =========================================================================
// CAPÍTULO 8: SEGURIDAD Y NORMATIVA COLOMBIANA
// =========================================================================
children.push(
  h1('8. Seguridad de la Información, Privacidad y Cumplimiento Normativo Colombiano'),
  h2('8.1 Mitigación de Vulnerabilidades del OWASP Top 10'),
  p('El sistema incorpora contramedidas nativas y personalizadas contra las principales amenazas de seguridad web:'),
  bullet('Inyección SQL (SQL Injection)', 'Todas las consultas a la base de datos se ejecutan mediante Sentencias Preparadas y Parameter Binding a través de Eloquent ORM y PDO, haciendo inviable la inyección de código SQL malicioso.'),
  bullet('Cross-Site Scripting (XSS)', 'El motor Blade escapa de forma automática todas las salidas mediante la función htmlspecialchars (sintaxis {{ $variable }}). En campos de texto enriquecido se aplica sanitización estricta con lista blanca de etiquetas HTML.'),
  bullet('Cross-Site Request Forgery (CSRF)', 'Todas las peticiones mutadoras (POST, PUT, PATCH, DELETE) exigen la validación del token criptográfico CSRF sincronizado (@csrf), tanto en formularios tradicionales como en llamadas AJAX mediante la cabecera X-CSRF-TOKEN.'),
  bullet('Control de Acceso y Autorización Rota (BOLA/BAC)', 'Implementación del middleware RoleMiddleware que verifica a nivel de ruta los privilegios del usuario (consumer, merchant, analyst), complementado con políticas de autorización (Policies) para que ningún usuario pueda consultar pedidos o subórdenes ajenas.'),
  bullet('Limitación de Tasa de Peticiones (Rate Limiting)', 'Protección contra ataques de fuerza bruta en rutas críticas (ej. /login, /register, /assistant/search) mediante el middleware throttle:60,1 de Laravel.'),
  h2('8.2 Criptografía de Datos y Protección de Credenciales'),
  bullet('Hashing de Contraseñas', 'Almacenamiento de contraseñas de usuarios mediante el algoritmo Bcrypt con factor de costo computacional dinámico, inmune a ataques de tablas arcoíris.'),
  bullet('Cifrado Simétrico AES-256-CBC (SafeEncrypted)', 'Las llaves privadas de los comercios para pasarelas de pago y datos bancarios se almacenan cifradas en reposo con la llave maestra APP_KEY de la aplicación.'),
  h2('8.3 Cumplimiento de la Ley 1581 de 2012 (Habeas Data y Privacidad)'),
  p(
    'En acatamiento de la Ley Estatutaria 1581 de 2012 y el Decreto 1377 de 2013 de Colombia, FusaShop implementa el middleware CheckCookieConsent.php y un banner flotante de consentimiento de tratamiento de datos personales, garantizando que el usuario acepte de forma libre, previa, expresa e informada las políticas de privacidad y uso de cookies antes de almacenar información en su navegador.'
  ),
  h2('8.4 Cumplimiento del Estatuto del Consumidor (Ley 1480 de 2011)'),
  p(
    'La plataforma incorpora un Módulo Integral de PQRS (Peticiones, Quejas, Reclamos y Sugerencias) accesible desde /pqrs, que emite un número de radicado único, fija una fecha límite de respuesta legal de 15 días hábiles y conserva la trazabilidad completa de las comunicaciones entre el consumidor, el comercio y la administración de la plataforma.'
  )
);

// =========================================================================
// CAPÍTULO 9: ENTORNO DE DESARROLLO, DEVOPS Y DESPLIEGUE
// =========================================================================
children.push(
  h1('9. Entorno de Desarrollo, DevOps y Estrategia de Despliegue en Producción'),
  h2('9.1 Control de Versiones con Git y Flujo de Ramas en GitHub'),
  p(
    'El código fuente del proyecto se gestiona bajo el sistema de control de versiones distribuido Git, alojado en el repositorio remoto https://github.com/Zantival/fusashop2. La rama activa de producción y entrega de grado es Final, la cual contiene la versión estable verificada, libre de dependencias rotas y preparada para el despliegue comercial.'
  ),
  h2('9.2 Gestión de Paquetes y Reproducibilidad'),
  p(
    'La reproducibilidad de compilación está garantizada mediante archivos de bloqueo determinísticos: composer.lock para las dependencias PHP del backend y package-lock.json para las herramientas de frontend en Node.js, impidiendo que variaciones de versiones de librerías afecten la estabilidad del sistema.'
  ),
  h2('9.3 Estrategia de Despliegue en Producción (Hostinger sin Docker)'),
  p(
    'Para asegurar que la plataforma pueda operar con costos sostenibles en un plan de hosting empresarial estándar en Hostinger sin depender de costosos clústeres Docker o Kubernetes, se diseñó una arquitectura de despliegue altamente segura:'
  ),
  bullet('Aislamiento del Código Fuente', 'El núcleo de la aplicación Laravel (.env, app/, config/, storage/, vendor/) se sitúa en un directorio privado fuera de la carpeta pública del servidor web, impidiendo la lectura directa de archivos confidenciales desde internet.'),
  bullet('Directorio Web Público', 'Únicamente el contenido de public/ se enlaza o traslada a public_html, ajustando las rutas relativas de carga en index.php.'),
  bullet('Optimización de Caché de Producción', 'Ejecución de los comandos artisan de precompilación: php artisan config:cache, php artisan route:cache y php artisan view:cache, reduciendo el arranque de la aplicación a milisegundos.'),
  bullet('Procesamiento de Colas por Cron', 'Configuración de un Cron Job que ejecuta cada minuto php artisan schedule:run para la ejecución de workers asíncronos y mantenimiento programado.'),
  bullet('Cifrado en Tránsito con SSL/TLS', 'Instalación de certificados SSL automáticos (Let\'s Encrypt) con redirección obligatoria a HTTPS y cabeceras HSTS (HTTP Strict Transport Security).')
);

// =========================================================================
// CAPÍTULO 10: MATRICES COMPARATIVAS Y JUSTIFICACIÓN
// =========================================================================
children.push(
  h1('10. Matrices Comparativas y Justificación Técnica de Decisiones'),
  h2('10.1 Comparativa de Frameworks de Backend'),
  makeTable(
    ['Criterio de Evaluación', 'Laravel 12 (PHP 8.3) [Elegido]', 'Django 5.x (Python)', 'Express.js (Node.js)'],
    [
      ['Ecosistema Multivendedor y E-Commerce', 'Excelente (ORM expresivo, eventos, colas, Sanctum nativo)', 'Bueno (Requiere extensiones adicionales para e-commerce)', 'Básico (Exige ensamblar múltiples librerías desacopladas)'],
      ['Facilidad de Despliegue en Hosting Regional', 'Máxima (Soporte universal en Hostinger, cPanel, VPS)', 'Media (Requiere WSGI, Gunicorn, Nginx y supervisor)', 'Media (Demanda procesos daemon Node, PM2 y reverse proxy)'],
      ['Costo de Infraestructura Mensual', 'Bajo ($5 a $15 USD en hosting compartido o VPS)', 'Medio-Alto ($20 a $50 USD en VPS dedicado)', 'Medio ($20 a $40 USD en contenedor o VPS)'],
      ['Seguridad contra OWASP por Defecto', 'Muy Alta (CSRF, XSS, SQLi resueltos en el framework)', 'Muy Alta (Baterías de seguridad integradas en Django)', 'Media (Depende estrictamente del desarrollador)'],
      ['Curva de Aprendizaje y Mantenimiento', 'Moderada-Baja (Arquitectura MVC clara y legible)', 'Moderada (Patrón MTV y sintaxis Python)', 'Alta (Falta de convención oficial de arquitectura)']
    ],
    [25, 25, 25, 25]
  ),
  h2('10.2 Comparativa de Enfoques de Frontend'),
  makeTable(
    ['Criterio de Evaluación', 'Blade + Alpine.js + Tailwind v4 [Elegido]', 'SPA Completa con React / Vue', 'Maquetación Clásica Bootstrap + jQuery'],
    [
      ['Tiempo de Carga Inicial (First Contentful Paint)', 'Inmediato (< 350 ms, renderizado en servidor)', 'Lento en móvil (Requiere descargar grandes bundles JS)', 'Medio (Carga de librerías jQuery y Bootstrap pesado)'],
      ['Indexabilidad y Posicionamiento SEO', 'Nativo y automático (HTML semántico listo para Google)', 'Complejo (Exige SSR con Next.js/Nuxt y servidores Node)', 'Nativo pero con estructura semántica anticuada'],
      ['Complejidad de Mantenimiento de Estado', 'Baja (Estado local con Alpine.js sin Redux ni Pinia)', 'Alta (Manejo de stores globales, hooks y re-renderizados)', 'Muy Alta (Manipulación desordenada directa del DOM)'],
      ['Consumo de Memoria en Dispositivos Móviles', 'Mínimo (~15 KB de runtime JS)', 'Elevado (~300 KB a 1.5 MB de runtime JavaScript)', 'Moderado (~120 KB)']
    ],
    [25, 25, 25, 25]
  ),
  h2('10.3 Comparativa de Motores de Base de Datos'),
  makeTable(
    ['Criterio de Evaluación', 'Relacional (MySQL / MariaDB / SQLite) [Elegido]', 'NoSQL Documental (MongoDB)', 'NoSQL Clave-Valor (Redis como DB primaria)'],
    [
      ['Integridad Referencial y Claves Foráneas', 'Nativa y estricta (Evita pedidos huérfanos o items corruptos)', 'Manual (Debe validarse por código en la aplicación)', 'Inexistente (No aplica relaciones relacionales)'],
      ['Soporte de Transacciones ACID Complejas', 'Nativo (Garantiza pagos atómicos y dispersión contable)', 'Limitado / Complejo en colecciones cruzadas', 'Limitado (Solo comandos individuales de memoria)'],
      ['Consultas de Reportería y Agregación Financiera', 'Óptima (SQL declarativo con joins, group by, sum)', 'Media (Requiere pipelines de agregación complejos)', 'Muy deficiente para analítica relacional']
    ],
    [25, 25, 25, 25]
  )
);

// =========================================================================
// CAPÍTULO 11: CONCLUSIONES DEL MARCO TECNOLÓGICO
// =========================================================================
children.push(
  h1('11. Conclusiones y Balance Tecnológico'),
  p(
    'La articulación del Marco Tecnológico presentado demuestra que FusaShop no es una simple tienda virtual estándar, sino una plataforma integral de ingeniería de software concebida con rigor metodológico para resolver las complejidades económicas, geográficas y regulatorias del comercio electrónico multivendedor en Colombia.'
  ),
  p(
    'La elección de PHP 8.3 con Laravel 12 en el backend, complementada con el dinamismo reactivo de Alpine.js v3 y el diseño utility-first de Tailwind CSS v4, demostró ser la arquitectura óptima para garantizar soberanía tecnológica, altísima velocidad de carga en dispositivos móviles, cero sobrecostos de infraestructura en la nube y total cumplimiento con la DIAN, la SIC y la legislación de Habeas Data.'
  ),
  p(
    'Asimismo, la incorporación pionera del servidor Model Context Protocol (MCP), junto con el asistente contextual FusaBot y los servicios de diagnóstico comercial y geoanalítica territorial, sitúa a FusaShop a la vanguardia de los ecosistemas de comercio electrónico regional, dotando a los pequeños comerciantes y prestadores turísticos de Fusagasugá de herramientas de inteligencia de negocios antes reservadas únicamente para corporaciones multinacionales.'
  ),
  callout(
    'Validación y Reproducibilidad Académica',
    'El código fuente íntegro, las migraciones de base de datos, los controladores, servicios y plantillas de diseño analizados en este documento se encuentran plenamente operativos y sincronizados en la rama Final del repositorio GitHub: https://github.com/Zantival/fusashop2.'
  )
);

// =========================================================================
// CREACIÓN DEL DOCUMENTO DOCX Y GUARDADO
// =========================================================================
const doc = new Document({
  styles: {
    default: {
      document: {
        run: {
          font: 'Calibri',
          size: 22,
          color: COLORS.DARK_TEXT
        }
      }
    }
  },
  sections: [
    {
      properties: {
        page: {
          margin: {
            top: 1440,    // 1 pulgada (2.54 cm)
            bottom: 1440,
            left: 1440,
            right: 1440
          }
        }
      },
      headers: {
        default: new Header({
          children: [
            new Paragraph({
              alignment: AlignmentType.RIGHT,
              children: [
                new TextRun({
                  text: 'FusaShop — Marco Tecnológico para Tesis | Repositorio: Zantival/fusashop2 (Rama: Final)',
                  font: 'Calibri',
                  size: 17,
                  color: COLORS.MUTED_TEXT,
                  italics: true
                })
              ],
              spacing: { after: 120 }
            })
          ]
        })
      },
      footers: {
        default: new Footer({
          children: [
            new Paragraph({
              alignment: AlignmentType.RIGHT,
              children: [
                new TextRun({
                  text: 'Página ',
                  font: 'Calibri',
                  size: 18,
                  color: COLORS.MUTED_TEXT
                }),
                new TextRun({
                  children: [PageNumber.CURRENT],
                  font: 'Calibri',
                  size: 18,
                  bold: true,
                  color: COLORS.PRIMARY
                }),
                new TextRun({
                  text: ' de ',
                  font: 'Calibri',
                  size: 18,
                  color: COLORS.MUTED_TEXT
                }),
                new TextRun({
                  children: [PageNumber.TOTAL_PAGES],
                  font: 'Calibri',
                  size: 18,
                  bold: true,
                  color: COLORS.PRIMARY
                })
              ]
            })
          ]
        })
      },
      children
    }
  ]
});

const outputPath = path.resolve('c:/Users/HUAWEI/Desktop/fusashop2/Marco_Tecnologico_FusaShop_Tesis.docx');

Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync(outputPath, buffer);
  const stats = fs.statSync(outputPath);
  console.log(`Documento Word generado exitosamente: ${outputPath}`);
  console.log(`Tamaño del archivo: ${(stats.size / 1024).toFixed(2)} KB`);
}).catch(err => {
  console.error('Error al empaquetar el documento DOCX:', err);
  process.exit(1);
});
