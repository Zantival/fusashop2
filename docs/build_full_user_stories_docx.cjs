const fs = require("fs");
const path = require("path");
const docx = require("docx");

const {
  Document, Paragraph, TextRun, Table, TableRow, TableCell,
  HeadingLevel, AlignmentType, WidthType, BorderStyle, ShadingType,
  PageNumber, NumberFormat, Header, Footer, Packer
} = docx;

// Colors - FusaShop Institutional Palette
const PRIMARY = "087B55";    // Emerald Green
const SECONDARY = "07563F";  // Deep Forest
const ACCENT = "E97824";     // Orange
const TEXT_DARK = "19342B";  // Ink Slate
const TEXT_MUTED = "52675E"; // Secondary Text
const BG_LIGHT = "F3F8F5";   // Light mint
const BG_GRAY = "F7F9F8";    // Table row alt
const BORDER = "C3D9CE";     // Thin border

function createBorder(color = BORDER, size = 1) {
  return { style: BorderStyle.SINGLE, size: size, color: color };
}

const tableBorders = {
  top: createBorder(BORDER, 1),
  bottom: createBorder(BORDER, 1),
  left: createBorder(BORDER, 1),
  right: createBorder(BORDER, 1),
  insideHorizontal: createBorder(BORDER, 1),
  insideVertical: createBorder(BORDER, 1)
};

function cell(text, isHeader = false, widthPercent = null, colSpan = 1, options = {}) {
  const p = new Paragraph({
    alignment: options.alignment || AlignmentType.LEFT,
    spacing: { before: 50, after: 50 },
    children: [
      new TextRun({
        text: text,
        bold: isHeader || options.bold,
        size: isHeader ? 18 : (options.size || 17),
        color: isHeader ? "FFFFFF" : (options.color || TEXT_DARK),
        font: "Segoe UI"
      })
    ]
  });

  return new TableCell({
    columnSpan: colSpan,
    width: widthPercent ? { size: widthPercent, type: WidthType.PERCENTAGE } : undefined,
    shading: {
      type: ShadingType.CLEAR,
      fill: isHeader ? PRIMARY : (options.fill || "FFFFFF")
    },
    margins: { top: 90, bottom: 90, left: 130, right: 130 },
    children: [p]
  });
}

function userStoryTable(hu) {
  const rows = [
    // Header Row: ID + Title
    new TableRow({
      children: [
        new TableCell({
          columnSpan: 4,
          shading: { type: ShadingType.CLEAR, fill: PRIMARY },
          margins: { top: 110, bottom: 110, left: 150, right: 150 },
          children: [
            new Paragraph({
              spacing: { before: 30, after: 30 },
              children: [
                new TextRun({ text: `${hu.id}: ${hu.title}`, bold: true, size: 20, color: "FFFFFF", font: "Segoe UI" })
              ]
            })
          ]
        })
      ]
    }),
    // Como
    new TableRow({
      children: [
        cell("Como:", true, 18, 1, { fill: SECONDARY, size: 16.5 }),
        cell(hu.as, false, 82, 3, { fill: "FFFFFF" })
      ]
    }),
    // Quiero
    new TableRow({
      children: [
        cell("Quiero:", true, 18, 1, { fill: SECONDARY, size: 16.5 }),
        cell(hu.want, false, 82, 3, { fill: BG_GRAY })
      ]
    }),
    // Para
    new TableRow({
      children: [
        cell("Para:", true, 18, 1, { fill: SECONDARY, size: 16.5 }),
        cell(hu.soThat, false, 82, 3, { fill: "FFFFFF" })
      ]
    }),
    // Criterios Header
    new TableRow({
      children: [
        cell("Criterios de Aceptación Verificables", true, 100, 4, { fill: BG_LIGHT, color: SECONDARY, bold: true, size: 17 })
      ]
    })
  ];

  // Acceptance criteria
  hu.criteria.forEach((crit, idx) => {
    rows.push(new TableRow({
      children: [
        cell(`• ${crit}`, false, 100, 4, { fill: idx % 2 === 0 ? "FFFFFF" : BG_GRAY, size: 16.5 })
      ]
    }));
  });

  // Business Rules / Alignment if present
  if (hu.rules && hu.rules.length > 0) {
    rows.push(new TableRow({
      children: [
        cell("Reglas de Negocio e Integración FusaShop", true, 100, 4, { fill: BG_LIGHT, color: SECONDARY, bold: true, size: 16.5 })
      ]
    }));
    hu.rules.forEach((rule, idx) => {
      rows.push(new TableRow({
        children: [
          cell(`✓ ${rule}`, false, 100, 4, { fill: idx % 2 === 0 ? "FFFFFF" : BG_GRAY, color: TEXT_MUTED, size: 16 })
        ]
      }));
    });
  }

  // Metadata: Priority & Estimate
  rows.push(new TableRow({
    children: [
      cell("Prioridad:", true, 18, 1, { fill: SECONDARY, size: 15.5 }),
      cell(hu.priority, false, 32, 1, { fill: "FFFFFF", bold: true, size: 15.5 }),
      cell("Estimación:", true, 18, 1, { fill: SECONDARY, size: 15.5 }),
      cell(hu.estimate, false, 32, 1, { fill: "FFFFFF", bold: true, size: 15.5 })
    ]
  }));

  // Evidence
  rows.push(new TableRow({
    children: [
      cell("Evidencia:", true, 18, 1, { fill: SECONDARY, size: 15.5 }),
      cell(hu.evidence, false, 82, 3, { fill: BG_GRAY, color: TEXT_MUTED, size: 15.5 })
    ]
  }));

  // Code Reference
  if (hu.codeRef) {
    rows.push(new TableRow({
      children: [
        cell("Implementación:", true, 18, 1, { fill: SECONDARY, size: 15.5 }),
        cell(hu.codeRef, false, 82, 3, { fill: BG_LIGHT, color: PRIMARY, bold: true, size: 15 })
      ]
    }));
  }

  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: tableBorders,
    rows: rows
  });
}

// Full 26 User Stories Data + 4 Architecture Stories
const modulesData = [
  {
    title: "Módulo 1: Autenticación y Perfiles",
    description: "Gestión segura de identidades, control de acceso basado en roles (RBAC) y perfiles diferenciados para consumidores, comerciantes y personal administrativo.",
    stories: [
      {
        id: "HU001",
        title: "Inicio de sesión y autenticación del consumidor",
        as: "Consumidor registrado o nuevo visitante",
        want: "Registrarme e iniciar sesión mediante correo electrónico, contraseña segura o autenticación social (Google), verificando mi identidad por código OTP",
        soThat: "Acceder a mi cuenta, consultar mi historial de compras, gestionar direcciones y hacer seguimiento en tiempo real a mis pedidos",
        criteria: [
          "El sistema permite el registro con validación de correo electrónico único, nombre y contraseña con hash seguro (Bcrypt) (RF01).",
          "El sistema maneja autenticación segura basada en sesiones web protegidas contra CSRF y tokens de acceso Sanctum para clientes API (RF02).",
          "El sistema implementa verificación de correo electrónico mediante código OTP de 6 dígitos con ventana de vigencia de 15 minutos (`/email/verify-code`) y soporte de reenvío controlado (`/email/resend-code`) (RF01, RF02).",
          "El sistema permite recuperar la contraseña mediante un enlace seguro con token temporal enviado al correo (`/forgot-password`) y soporte de solicitud asistida offline (RF03).",
          "El sistema bloquea automáticamente el inicio de sesión y despliega mensaje explicativo si la cuenta está marcada como bloqueada (`is_blocked == true`) (RF24)."
        ],
        rules: [
          "Control de fuerza bruta: bloqueo temporal de solicitudes tras 5 intentos fallidos consecutivos.",
          "Persistencia de carrito de compras anónimo al iniciar sesión exitosamente."
        ],
        priority: "Alta (Must have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Base funcional del sistema; requerida obligatoriamente por los tres roles de la plataforma.",
        codeRef: "AuthController.php, SocialAuthController.php, User.php, tests/Feature/SixDigitEmailVerificationAndNotificationsTest.php"
      },
      {
        id: "HU002",
        title: "Inicio de sesión y panel de control del comerciante",
        as: "Comerciante aliado / MiPyme de Fusagasugá",
        want: "Acceder a un panel de administración seguro, responsivo y adaptado a dispositivos móviles",
        soThat: "Gestionar mi catálogo de productos, inventario, pedidos y finanzas desde cualquier lugar sin barreras técnicas",
        criteria: [
          "El sistema asigna y valida el rol “comerciante” (`merchant`) mediante middleware de autorización `role:merchant` (RF02, RF04).",
          "El panel ofrece diseño 100% responsivo y ligero optimizado para navegadores móviles y conexiones de baja latencia.",
          "Si el comerciante no ha completado su perfil comercial inicial, es redirigido automáticamente al flujo de onboarding (`/merchant/onboarding/profile`) (RF04).",
          "El sistema valida que el comercio cuente con estado KYC aprobado (`kyc_status == approved`) antes de habilitar la publicación de productos en la vitrina pública."
        ],
        rules: [
          "Aislamiento multi-inquilino: el comerciante solo puede ver, editar y gestionar sus propios productos y pedidos.",
          "Verificación documental requerida: RUT y certificado de Cámara de Comercio en formato PDF/imagen."
        ],
        priority: "Alta (Must have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Encuesta; predominio de gestión comercial desde smartphone entre comerciantes de Fusagasugá.",
        codeRef: "MerchantController.php, CompanyProfile.php, views/merchant/dashboard.blade.php, views/merchant/profile.blade.php"
      },
      {
        id: "HU003",
        title: "Inicio de sesión del analista o administrador",
        as: "Analista de datos o administrador de la plataforma",
        want: "Acceder a un dashboard centralizado con métricas globales, reportería y gobernanza",
        soThat: "Monitorear el rendimiento de la plataforma, auditar transacciones y evaluar el cumplimiento de objetivos del proyecto",
        criteria: [
          "El sistema asigna y restringe el acceso al rol “analista/administrador” (`role:analyst`) con verificación estricta de credenciales (RF02, RF04).",
          "El sistema valida correo único y registro administrativo con trazabilidad de accesos (RF01, RF24).",
          "El dashboard consolida métricas clave en tiempo real: volumen transaccional, comercios activos, órdenes en curso y estado de integraciones (RF20).",
          "Acceso a herramientas de auditoría, inspección documental KYC y supervisión técnica global."
        ],
        rules: [
          "Sesiones administrativas con tiempo de expiración estricto por inactividad.",
          "Registro obligatorio de toda acción administrativa en la bitácora de auditoría."
        ],
        priority: "Alta (Must have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Objetivo específico 3; evaluar el desempeño de la plataforma frente a los requerimientos establecidos.",
        codeRef: "AnalystController.php, DashboardMetricsService.php, views/analyst/dashboard.blade.php"
      }
    ]
  },
  {
    title: "Módulo 2: Catálogo e Inventario",
    description: "Publicación comercial, categorización multisectorial, especificaciones de productos y control de existencias con alertas de desabastecimiento.",
    stories: [
      {
        id: "HU004",
        title: "Catálogo y publicación de productos y servicios",
        as: "Comerciante",
        want: "Crear, editar, publicar y archivar productos y servicios con fotografías, precios, inventario y variantes",
        soThat: "Exhibir mi catálogo digitalmente a los clientes de Fusagasugá con información completa y atractiva",
        criteria: [
          "El comerciante puede crear, editar, pausar y eliminar productos físicos o servicios (`type: product|service`) (RF05).",
          "El sistema soporta una imagen de portada principal y una galería de hasta 5 imágenes con validación de tipo MIME y compresión automática (RF06).",
          "Los productos se organizan por categorías y sectores económicos oficiales del municipio (comercio, gastronomía, servicios, artesanías) (RF07).",
          "Soporte para configuración de opciones y variantes (tallas, colores, unidades de medida) y especificaciones técnicas estructuradas (RF05)."
        ],
        rules: [
          "Precio mayor o igual a cero y stock inicial no negativo.",
          "Generación automática de slug único amigable para SEO."
        ],
        priority: "Alta (Must have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Encuesta; la falta de conocimiento técnico y la baja digitalización exigen un formulario de carga simple y guiado.",
        codeRef: "Product.php, MerchantController@productStore, MerchantController@productUpdate, views/merchant/product-form.blade.php"
      },
      {
        id: "HU005",
        title: "Vitrina comercial y motor de búsqueda",
        as: "Consumidor",
        want: "Buscar y filtrar productos por nombre, categoría, rango de precio, valoración y asistencia inteligente",
        soThat: "Encontrar lo que necesito de manera rápida y tomar decisiones de compra informadas",
        criteria: [
          "La búsqueda en texto busca coincidencias en nombre, descripción y etiquetas de categoría (RF08).",
          "Filtros combinados por rango de precio (mínimo y máximo), categoría comercial y promedio de estrellas de valoración (RF08).",
          "Integración de asistente virtual FusaBot para búsqueda semántica guiada y sugerencias contextuales (`/assistant/search`) (RF09).",
          "Visualización de productos destacados y registro automático de popularidad por visualizaciones (`views_count`, `search_logs`)."
        ],
        rules: [
          "Solo se muestran productos pertenecientes a comercios con verificación KYC aprobada y estado activo.",
          "Resultados paginados de 12 o 24 artículos por página."
        ],
        priority: "Alta (Must have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Encuesta; canal preferido por los compradores y comportamiento de búsqueda reportado por comerciantes.",
        codeRef: "ConsumerController@catalog, ConsumerController@assistantSearch, SearchLog.php, views/consumer/catalog.blade.php"
      },
      {
        id: "HU006",
        title: "Gestión de inventario y alertas de stock bajo",
        as: "Comerciante",
        want: "Monitorear el stock de mis productos y recibir alertas automáticas cuando esté por debajo del mínimo configurado",
        soThat: "Evitar quedarme sin existencias y reabastecer oportunamente los artículos de alta rotación",
        criteria: [
          "El sistema registra las existencias totales y las unidades reservadas en compras en proceso mediante bloqueo pesimista (`reserved_stock`, `StockService`) (RF10).",
          "El sistema permite configurar un umbral de stock bajo (`low_stock_threshold`) por producto (RF11).",
          "Al descender el stock disponible al umbral configurado o a cero, el sistema dispara notificación automática en plataforma y por correo (`OutOfStockNotification`, `SendLowStockAlert`) (RF11).",
          "Edición rápida de inventario desde el panel central de existencias (`/merchant/inventory`)."
        ],
        rules: [
          "No se permite reducir el stock por debajo del número de unidades actualmente reservadas en órdenes pendientes.",
          "Cuando el stock llega a cero, el producto se marca como agotado en la vitrina pública."
        ],
        priority: "Media (Should have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Encuesta; predominio de control informal/manual de inventarios en MiPymes locales.",
        codeRef: "StockService.php, OutOfStockNotification.php, SendLowStockAlert.php, views/merchant/inventory.blade.php"
      }
    ]
  },
  {
    title: "Módulo 3: Transacciones y Logística",
    description: "Núcleo transaccional de compras, carrito persistente, integración bancaria Wompi, cotización en tiempo real y guías de transporte con Envía.",
    stories: [
      {
        id: "HU007",
        title: "Carrito de compras y preparación de checkout",
        as: "Consumidor",
        want: "Agregar productos al carrito, modificar cantidades, seleccionar ítems y proceder al pago en un flujo continuo",
        soThat: "Completar mi compra sin fricciones y con total claridad en los costos",
        criteria: [
          "El consumidor puede agregar, actualizar cantidades y eliminar productos del carrito antes de pagar (RF12).",
          "Soporte de checkout selectivo: marcar ítems específicos para comprar conservando el resto en el carrito (RF12).",
          "El sistema calcula y desglosa subtotal, descuentos de cupones, uso de puntos FusaCoins y costo de flete antes de confirmar el pedido (RF13).",
          "Validación de stock disponible antes de iniciar la orden para prevenir sobreventas (RF10, RF12)."
        ],
        rules: [
          "El carrito persiste en base de datos (`carts`, `cart_items`) asociado al usuario autenticado.",
          "Actualización automática de precios en caso de cambio de tarifas antes del checkout."
        ],
        priority: "Alta (Must have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Núcleo mínimo viable de comercio electrónico; base del objetivo general del proyecto.",
        codeRef: "Cart.php, CartItem.php, ConsumerController@cartIndex, ConsumerController@cartSelect, views/consumer/cart.blade.php"
      },
      {
        id: "HU008",
        title: "Gestión de direcciones de despacho y cobertura logística",
        as: "Consumidor",
        want: "Registrar y administrar una libreta de direcciones de despacho con validación de cobertura y tarifas",
        soThat: "Agilizar mis compras futuras y asegurar la entrega correcta de mis pedidos en Fusagasugá y la región",
        criteria: [
          "El consumidor puede crear, editar y eliminar direcciones de despacho asociadas a su cuenta (`shipping_addresses`).",
          "El sistema valida la dirección (departamento, municipio, código postal) mediante la API de geocodificación de Envía antes de guardarla.",
          "El sistema consulta en tiempo real, mediante la API de cotización de Envía (`EnviaService`), la cobertura y tarifa de flete según el peso y volumen del paquete (`buildPackage`).",
          "Opción para marcar una dirección como predeterminada para compras rápidas."
        ],
        rules: [
          "Tarifa calculada con base en las dimensiones y peso real de todos los productos incluidos en el pedido.",
          "Manejo de dirección de origen configurada por cada comercio en su perfil."
        ],
        priority: "Alta (Must have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Requisito técnico derivado de la integración logística con Envía; indispensable para cotizar fletes y generar guías.",
        codeRef: "ShippingAddress.php, EnviaService.php, AddressController.php, views/consumer/profile.blade.php"
      },
      {
        id: "HU009",
        title: "Pasarela de pagos en línea (Wompi)",
        as: "Consumidor",
        want: "Pagar mis pedidos mediante PSE, tarjetas de crédito/débito, Nequi o Bancolombia con confirmación inmediata",
        soThat: "Completar mi compra de manera confiable con el medio de pago electrónico de mi preferencia",
        criteria: [
          "El sistema integra el Checkout Widget oficial de Wompi (Sandbox y Producción) para procesar pagos por PSE, tarjetas y Nequi sin almacenar datos de tarjetas en el servidor (cumplimiento PCI-DSS) (RF14-RF16).",
          "Generación de firma de integridad criptográfica SHA-256 (`integritySignature`) que protege monto, moneda y referencia contra adulteraciones (RF16).",
          "Endpoint seguro que recibe los webhooks firmados por Wompi con control anti-replay y actualiza el estado del pedido entre `PENDING`, `APPROVED` y `DECLINED` (RF16).",
          "Al confirmarse el estado `APPROVED`, se genera el comprobante digital en PDF y se dispara la emisión de la factura electrónica DIAN (RF17)."
        ],
        rules: [
          "Idempotencia estricta en el procesamiento de eventos de webhook para evitar duplicidad de cobros.",
          "Liberación automática de reservas de inventario si el pago es rechazado o expira."
        ],
        priority: "Alta (Must have)",
        estimate: "8 días ideales (13 Story Points)",
        evidence: "Encuesta; baja confianza en compras digitales; una pasarela bancaria certificada es crítica para superar esta barrera.",
        codeRef: "WompiService.php, WompiWebhookController.php, OrderPaymentService.php, PaymentTransaction.php"
      },
      {
        id: "HU010",
        title: "Seguimiento y despacho automatizado de pedidos",
        as: "Consumidor y Comerciante",
        want: "Conocer el estado de mi pedido en tiempo real y contar con generación automática de guías de despacho",
        soThat: "Saber en todo momento dónde está la compra y despachar la mercancía sin intermediación manual",
        criteria: [
          "El sistema actualiza el estado del pedido con base en los eventos recibidos de Wompi y Envía (`pending` -> `processing` -> `shipped` -> `delivered`) (RF18).",
          "Al confirmarse el pago, el sistema genera automáticamente mediante la API de Envía la guía de despacho (`tracking_number`, `tracking_url`, `label_url`) sin requerir digitación manual del comerciante (RF19).",
          "El comerciante puede descargar e imprimir la etiqueta logística en PDF desde su panel para rotular el paquete (RF19).",
          "El sistema notifica cada cambio de estado mediante notificaciones internas de la plataforma y correo electrónico (RF19)."
        ],
        rules: [
          "La guía solo se genera para pedidos con estado de pago aprobado.",
          "Webhook de Envía (`EnviaWebhookController`) actualiza el estado a entregado automáticamente."
        ],
        priority: "Alta (Must have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Encuesta; la confianza del cliente en la compra digital depende de la trazabilidad verificable del pedido.",
        codeRef: "GenerateEnviaShipment.php, EnviaWebhookController.php, MerchantController@printLabel, views/merchant/orders.blade.php"
      }
    ]
  },
  {
    title: "Módulo 4: Analítica y Administración",
    description: "Inteligencia de negocios, reportería histórica comparativa, diagnóstico comercial automático y gobernanza de la plataforma.",
    stories: [
      {
        id: "HU011",
        title: "Dashboard de analítica e inteligencia de negocio",
        as: "Analista o administrador",
        want: "Visualizar métricas de ventas, tráfico, productos más vendidos, comisiones y modelos analíticos",
        soThat: "Evaluar el desempeño operativo de la plataforma y respaldar las decisiones con datos",
        criteria: [
          "El sistema muestra métricas de ventas brutas, comisiones recaudadas, ticket promedio, tasa de conversión y valoraciones en tiempo real (RF20).",
          "Gráficos interactivos de tendencias temporales (diarias, semanales, mensuales) y distribución por categorías (RF20).",
          "Ejecución de modelos de analítica y Machine Learning en Python (`ml_runs`, `analytics.py`) para diagnóstico y predicción.",
          "El sistema permite exportar reportes de analítica en formatos CSV y vista para impresión en PDF (`/sales-report-print`) (RF21)."
        ],
        rules: [
          "Acceso exclusivo para usuarios con rol analista o administrador.",
          "Cálculo de métricas optimizado mediante índices en base de datos para garantizar respuesta menor a 2 segundos."
        ],
        priority: "Media (Should have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Objetivo específico 3 del proyecto; evaluar el desempeño de la plataforma con base en indicadores medibles.",
        codeRef: "AnalystController@dashboard, DashboardMetricsService.php, MlRun.php, views/analyst/dashboard.blade.php"
      },
      {
        id: "HU012",
        title: "Panel de rendimiento comercial del comerciante",
        as: "Comerciante",
        want: "Ver un resumen de mis ventas diarias, semanales y mensuales comparado con el período anterior y diagnóstico de rotación",
        soThat: "Tomar mejores decisiones de abastecimiento, fijación de precios y promociones en mi negocio",
        criteria: [
          "El sistema muestra un resumen de ventas netas por período con comparación histórica porcentual (RF22).",
          "El sistema muestra el ranking de productos más vendidos y alerta sobre artículos de baja rotación (RF23).",
          "Módulo de diagnóstico comercial asistido (`SalesDiagnosisService`) que emite recomendaciones automáticas de mejora.",
          "Exportación del historial de ventas a formato CSV descargable para conciliación contable (`/reports/export-csv`)."
        ],
        rules: [
          "Los datos de ventas excluyen fletes de transporte e impuestos retenidos.",
          "Comparativa histórica frente a los 30 días anteriores o mismo mes del año previo."
        ],
        priority: "Media (Should have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Encuesta; bajo uso reportado de herramientas de analítica de negocio entre las MiPymes encuestadas.",
        codeRef: "MerchantController@salesReports, SalesDiagnosisService.php, views/merchant/reports.blade.php"
      },
      {
        id: "HU013",
        title: "Administración de usuarios, seguridad y auditoría",
        as: "Analista o administrador",
        want: "Gestionar cuentas de comerciantes y consumidores, validar expedientes KYC y auditar operaciones críticas",
        soThat: "Mantener la plataforma segura, ordenada y conforme al marco legal y normativo",
        criteria: [
          "El sistema permite activar, suspender y eliminar cuentas con registro inmutable en bitácora de auditoría (RF24).",
          "Módulo de inspección documental KYC para comercios (RUT y Cámara de Comercio) con aprobación o rechazo motivado (RF24).",
          "El sistema permite asignar y modificar roles y permisos de usuarios registrados (RF25).",
          "Registro detallado en `admin_audit_logs` con usuario ejecutor, acción, IP, agente de usuario y estado anterior/posterior en JSON."
        ],
        rules: [
          "El borrado de usuarios implementa SoftDeletes conservando la integridad referencial de pedidos.",
          "Protección contra auto-eliminación o auto-bloqueo del superadministrador principal."
        ],
        priority: "Media (Should have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Requisito de gobernanza y seguridad de la plataforma (alineado con ISO/IEC 27001).",
        codeRef: "AnalystController@users, AnalystController@updateKyc, AdminAuditLog.php, views/analyst/users.blade.php"
      }
    ]
  },
  {
    title: "Módulo 5: Comunicación, Soporte y Valoraciones",
    description: "Canales de comunicación directa cliente-vendedor, notificaciones de eventos, centro de atención de PQRS legal y reputación.",
    stories: [
      {
        id: "HU014",
        title: "Chat directo y mensajería vinculada a productos",
        as: "Consumidor",
        want: "Enviar mensajes directos al comerciante para consultar detalles de un producto antes de comprarlo",
        soThat: "Resolver mis dudas sobre tallas, disponibilidad o garantías antes de decidir la compra",
        criteria: [
          "El sistema habilita mensajería directa vinculada a la ficha del producto consultado (`messages` con `product_id`) (RF26).",
          "Soporte de sondeo dinámico (`/chat/{user}/poll`) para actualización en tiempo real de la conversación sin recargar la página (RF26).",
          "El sistema notifica al comerciante (notificación en plataforma y correo) al recibir un nuevo mensaje (RF27).",
          "Contador visual de mensajes no leídos en la barra superior del comerciante y consumidor."
        ],
        rules: [
          "Filtro contra inyección de scripts (XSS) y sanitización de texto en cada mensaje enviado.",
          "Enlace complementario directo para apertura de WhatsApp Business del comercio."
        ],
        priority: "Media (Should have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Encuesta; el canal WhatsApp Business (mensajería directa) es el predominante entre comerciantes.",
        codeRef: "ChatController.php, Message.php, views/chat/show.blade.php, views/chat/index.blade.php"
      },
      {
        id: "HU015",
        title: "Configuración de notificaciones personalizadas",
        as: "Consumidor y Comerciante",
        want: "Configurar mis preferencias de notificaciones por canal y tipo de evento",
        soThat: "Recibir solo los avisos que me interesan y evitar la saturación de comunicaciones",
        criteria: [
          "El sistema permite configurar preferencias de notificación por canal (campanita web, correo) y tipo de evento (`notification_preferences`) (RF28).",
          "Bandeja centralizada de notificaciones en plataforma con opción de marcar individualmente o en lote como leídas (`/notifications/read-all`).",
          "Disparo selectivo de notificaciones ante compras, despachos, entregas, mensajes recibidos y respuestas a tickets."
        ],
        rules: [
          "Las notificaciones transaccionales críticas (facturas, confirmación de pago) se envían obligatoriamente por correo.",
          "Persistencia en base de datos (`notifications`) con UUID único."
        ],
        priority: "Baja (Could have)",
        estimate: "2 días ideales (3 Story Points)",
        evidence: "Buena práctica de usabilidad (ISO/IEC 25010); complementa la trazabilidad de HU009 y HU013.",
        codeRef: "User@notification_preferences, ConsumerController@updateProfile, views/notifications/index.blade.php"
      },
      {
        id: "HU016",
        title: "Centro de ayuda, FAQ y radicación formal de PQRS",
        as: "Consumidor y Comerciante",
        want: "Consultar preguntas frecuentes y radicar peticiones, quejas, reclamos o sugerencias con seguimiento de SLA",
        soThat: "Resolver dudas operativas y hacer valer mis derechos como consumidor bajo la ley colombiana",
        criteria: [
          "Módulo formal de radicación de PQRS categorizado por tipo (`peticion`, `queja`, `reclamo`, `sugerencia`) con adjuntos y prioridad (RF31).",
          "Cálculo automático de fecha límite de respuesta legal (15 días hábiles según Ley 1755 de 2015) (`due_at`) con alertas de vencimiento.",
          "Centro de ayuda con preguntas frecuentes (FAQ) sobre envíos, pagos, devoluciones y garantías.",
          "Panel administrativo para que el analista asigne, gestione y responda los tickets con historial conversacional estructurado (`conversation_history`)."
        ],
        rules: [
          "Todo radicado genera un código único de seguimiento enviado al correo del solicitante.",
          "Cumplimiento formal del Estatuto del Consumidor (Ley 1480 de 2011)."
        ],
        priority: "Media (Should have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Cumplimiento normativo obligatorio en Colombia y necesidad de mitigar la barrera de desconfianza técnica.",
        codeRef: "PQRSController.php, PQRS.php, views/pqrs/create.blade.php, views/analyst/pqrs/index.blade.php"
      },
      {
        id: "HU017",
        title: "Sistema de valoraciones, reseñas y reputación comercial",
        as: "Consumidor verificado",
        want: "Dejar una valoración con estrellas y reseña de los productos que he comprado",
        soThat: "Compartir mi experiencia y ayudar a otros usuarios a decidir su compra",
        criteria: [
          "El sistema habilita la opción de calificar únicamente tras la confirmación de entrega del pedido (`delivered`) (RF29).",
          "Calificación obligatoria de 1 a 5 estrellas y comentario opcional (`reviews`) (RF29).",
          "El sistema calcula y muestra el promedio ponderado de valoraciones en la ficha de cada producto y perfil de la tienda (RF30).",
          "El comerciante puede publicar una respuesta oficial a la reseña recibida (`merchant_reply`, `replied_at`)."
        ],
        rules: [
          "Un comprador solo puede calificar una vez cada producto adquirido por pedido.",
          "Filtro de moderación de lenguaje ofensivo o inapropiado."
        ],
        priority: "Media (Should have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Encuesta; “confianza en la marca y opiniones” como principal factor de decisión de compra en línea.",
        codeRef: "Review.php, ConsumerController@productDetail, MerchantController@replyToReview, views/consumer/product-detail.blade.php"
      }
    ]
  },
  {
    title: "Módulo 6: Fidelización y Evaluación de Impacto",
    description: "Mecanismos de atracción y retención comercial, puntos de lealtad FusaCoins, y analítica de impacto socioeconómico municipal.",
    stories: [
      {
        id: "HU018",
        title: "Motor de cupones y descuentos comerciales",
        as: "Comerciante",
        want: "Crear códigos de descuento y cupones con condiciones configurables (monto mínimo, vigencia, límite de uso)",
        soThat: "Atraer nuevos compradores y promover campañas estacionales de venta",
        criteria: [
          "El comerciante puede crear cupones de descuento fijo en COP o porcentual con condiciones configurables (RF32).",
          "Parámetros de control: código único en mayúsculas, monto mínimo de orden, fecha de expiración y límite de redenciones (RF32).",
          "El sistema valida y aplica el cupón en tiempo real en el checkout, recalculando el total antes del pago (RF33).",
          "Los cupones aplican exclusivamente a los productos ofertados por el comercio que los generó."
        ],
        rules: [
          "El descuento no puede superar el subtotal de los productos correspondientes.",
          "Incremento atómico del contador de usos (`used_count`) para evitar condiciones de carrera."
        ],
        priority: "Baja (Could have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Encuesta; “promociones digitales” como una de las estrategias digitales predominantes declaradas.",
        codeRef: "Coupon.php, CouponController.php, CheckoutService.php, views/merchant/coupons/index.blade.php"
      },
      {
        id: "HU019",
        title: "Programa de puntos y fidelización (FusaCoins)",
        as: "Consumidor",
        want: "Acumular puntos por cada compra completada y canjearlos como descuento en pedidos futuros",
        soThat: "Obtener beneficios económicos tangibles por mi fidelidad al comercio local de Fusagasugá",
        criteria: [
          "El sistema acredita puntos automáticamente bajo la regla de 1 punto por cada $10.000 COP tras confirmarse la entrega (RF34).",
          "El consumidor puede consultar su saldo acumulado en su perfil e historial de acreditaciones (RF35).",
          "Redención directa en el checkout: el cliente decide cuántos puntos aplicar para descontar dinero del total a pagar (RF35).",
          "El comerciante puede configurar si un producto admite o no puntos de fidelización (`allows_points`)."
        ],
        rules: [
          "Conversión monetaria: 1 punto FusaCoin equivale a un valor fijo descontable en COP.",
          "Los puntos utilizados se debitan de inmediato al aprobarse la transacción."
        ],
        priority: "Baja (Could have)",
        estimate: "3 días ideales (5 Story Points)",
        evidence: "Complementa HU017 como mecanismo de retención e incentivo al consumo dentro del marketplace local.",
        codeRef: "LoyaltyPoint.php, OrderPaymentService.php, ConsumerController@cartIndex, LoyaltyPointsNotification.php"
      },
      {
        id: "HU020",
        title: "Módulo de evaluación de impacto socioeconómico",
        as: "Analista de datos / Investigador",
        want: "Visualizar indicadores de impacto del proyecto (nuevos clientes, incremento en ventas, cobertura territorial)",
        soThat: "Validar los resultados de la investigación y medir la efectividad de la digitalización en Fusagasugá",
        criteria: [
          "El sistema genera un dashboard de indicadores de impacto (ventas totales, comercios digitalizados, cobertura geográfica y retención) (RF36).",
          "Cálculo de crecimiento intermensual y distribución de transacciones por barrios/comunas del municipio (RF36).",
          "Métricas comparativas entre comercios tradicionales que adoptaron la plataforma vs. su línea base diagnóstica."
        ],
        rules: [
          "Actualización automática de métricas mediante tareas programadas diarias.",
          "Filtrado por rangos de fechas personalizables."
        ],
        priority: "Media (Should have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Objetivo específico 3: determinar el nivel de cumplimiento de la plataforma frente a los requerimientos establecidos.",
        codeRef: "AnalystController@dashboard, SalesDiagnosisService.php, python/analytics.py"
      },
      {
        id: "HU021",
        title: "Exportación de datasets anonimizados para investigación",
        as: "Analista / Investigador",
        want: "Exportar conjuntos de datos anónimos de transacciones y comportamiento del consumidor en CSV y Excel",
        soThat: "Sustentar el análisis estadístico final de la tesis de grado con rigor metodológico",
        criteria: [
          "El sistema exporta datos de transacciones, productos y liquidaciones en formatos estándar CSV y Excel (RF37).",
          "Anonimización estricta de datos personales sensibles (nombres, teléfonos, direcciones exactas) cumpliendo con la Ley 1581 de 2012 (RF37).",
          "Los datasets incluyen identificadores alfanuméricos disociados, montos, categorías, métodos de pago, costos logísticos y fechas."
        ],
        rules: [
          "Cumplimiento del principio de seguridad y confidencialidad en el tratamiento de datos de la Ley de Habeas Data.",
          "Descarga protegida por autenticación y permisos de analista."
        ],
        priority: "Media (Should have)",
        estimate: "2 días ideales (3 Story Points)",
        evidence: "Objetivo específico 3 y marco normativo de protección de datos personales (Ley 1581 de 2012).",
        codeRef: "AnalystController@ordersExportCsv, AnalystController@settlementsExportCsv"
      }
    ]
  },
  {
    title: "Módulo 7: Caracterización del Comerciante",
    description: "Variables sociodemográficas y empresariales capturadas durante el registro para segmentar la madurez digital y operativa de las MiPymes.",
    stories: [
      {
        id: "HU022",
        title: "Caracterización del tamaño de la empresa (Personal ocupado)",
        as: "Comerciante",
        want: "Indicar el número de empleados de mi negocio al crear y actualizar mi perfil comercial",
        soThat: "Que la plataforma clasifique mi capacidad operativa y adapte sugerencias pertinentes a mi escala",
        criteria: [
          "El formulario de registro y perfil comercial incluye un campo numérico validado de número de empleados (`employee_count`).",
          "Clasificación automática en el backend según el Decreto 957 de 2019: Microempresa (1-10), Pequeña (11-50), Mediana (51-200).",
          "El dato queda disponible para el analista en el dashboard de segmentación para estudios de impacto."
        ],
        rules: [
          "Campo numérico entero mayor o igual a 1.",
          "Actualizable en cualquier momento desde el perfil de la tienda."
        ],
        priority: "Media (Should have)",
        estimate: "1 día ideal (2 Story Points)",
        evidence: "Encuesta; variable “Tamaño de la empresa (Número de empleados)” utilizada para tipificar el tejido microempresarial.",
        codeRef: "CompanyProfile@employee_count, MerchantController@storeProfile, views/merchant/profile.blade.php"
      },
      {
        id: "HU023",
        title: "Registro de la antigüedad en el mercado",
        as: "Comerciante",
        want: "Indicar cuánto tiempo lleva mi negocio operando en Fusagasugá al crear mi perfil",
        soThat: "Que la plataforma pueda segmentar y priorizar el acompañamiento a negocios nuevos o consolidados",
        criteria: [
          "El formulario de perfil comercial incluye un campo de selección con rangos estandarizados de antigüedad: menos de 1 año, 1 a 3 años, 4 a 6 años, 7 a 10 años, más de 10 años.",
          "El dato se almacena en el perfil comercial (`company_profiles`) y se valida con la fecha de expedición de la matrícula mercantil del RUT.",
          "El analista puede filtrar y comparar el desempeño de negocios tradicionales vs. emprendimientos recientes en el panel de segmentación."
        ],
        rules: [
          "Selección obligatoria dentro del catálogo de opciones predefinidas.",
          "Permite evaluar correlación entre años de operación y resistencia al cambio tecnológico."
        ],
        priority: "Media (Should have)",
        estimate: "1 día ideal (2 Story Points)",
        evidence: "Encuesta; variable “Antigüedad en el mercado” de los comerciantes de Fusagasugá.",
        codeRef: "CompanyProfile.php, MerchantController@storeProfile, views/merchant/profile.blade.php"
      },
      {
        id: "HU024",
        title: "Registro del modelo comercial predominante",
        as: "Comerciante",
        want: "Indicar si mi venta es principalmente física, híbrida o digital al configurar mi tienda",
        soThat: "Que la plataforma me sugiera el nivel de configuración inicial más adecuado a mi madurez digital",
        criteria: [
          "El formulario incluye un selector con las categorías identificadas en la investigación: venta física tradicional (mostrador), modelo híbrido (mostrador y pedidos telefónicos/WhatsApp), y venta digital.",
          "El sistema utiliza este dato para adaptar la interfaz inicial, mostrando un flujo simplificado a comercios con venta principalmente física.",
          "El dato alimenta el diagnóstico de madurez digital del comercio visible en el panel del comerciante y analista."
        ],
        rules: [
          "Sincronizado con el campo de modelo de negocio (`business_type`) de la empresa.",
          "Permite reconfiguración a medida que el comercio incrementa su volumen digital."
        ],
        priority: "Media (Should have)",
        estimate: "2 días ideales (3 Story Points)",
        evidence: "Encuesta; variable “Modelo comercial predominante” para evaluar la transición hacia la omnicanalidad.",
        codeRef: "CompanyProfile@business_type, MerchantController@editStore, views/merchant/profile.blade.php"
      }
    ]
  },
  {
    title: "Módulo 8: Diagnóstico de Barreras y Adopción",
    description: "Herramientas de onboarding asistido e importación ágil para eliminar la fricción técnica y facilitar la incorporación de comerciantes tradicionales.",
    stories: [
      {
        id: "HU025",
        title: "Onboarding guiado y tutorial interactivo para comerciantes",
        as: "Comerciante con baja alfabetización digital",
        want: "Seguir un tutorial interactivo paso a paso al ingresar por primera vez a la plataforma",
        soThat: "Completar la configuración de mi tienda y publicar mi primer producto sin requerir ayuda externa",
        criteria: [
          "Al primer inicio de sesión el sistema despliega un recorrido visual de 5 pasos: 1. Datos básicos, 2. Carga de RUT/KYC, 3. Cuenta de dispersión, 4. Primer producto, 5. Activación.",
          "El comerciante puede completar la publicación de un producto en 3 clics asistidos desde el tutorial.",
          "El recorrido puede omitirse y retomarse en cualquier momento desde el Centro de Ayuda (**HU016**) y la guía inicial (`/how-to-start-guide`).",
          "Barra visual de progreso que indica el porcentaje de completitud de la tienda en el dashboard comercial."
        ],
        rules: [
          "Persistencia del estado de onboarding para no mostrar el tutorial nuevamente si el usuario ya lo completó.",
          "Retroalimentación visual inmediata con validaciones en tiempo real."
        ],
        priority: "Alta (Must have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Encuesta; “Falta de conocimiento técnico” es la barrera percibida con mayor frecuencia (68%) entre las MiPymes encuestadas.",
        codeRef: "MerchantController@onboardingProfile, how_to_start_guide, views/merchant/profile.blade.php"
      },
      {
        id: "HU026",
        title: "Migración asistida de catálogo desde WhatsApp Business",
        as: "Comerciante que vende principalmente por WhatsApp Business",
        want: "Importar mi catálogo existente o sincronizar fichas comerciales con FusaShop",
        soThat: "Digitalizar mi negocio sin duplicar el trabajo de cargar productos manualmente uno a uno",
        criteria: [
          "El sistema permite cargar un archivo de catálogo estructurado (CSV / Excel con columnas: nombre, precio, descripción, categoría, imagen).",
          "El sistema valida y prellena nombre, precio, descripción e imagen de cada producto importado para revisión final del comerciante.",
          "Generación automática de botón de contacto directo a WhatsApp en cada producto publicado para preservar el canal de atención preferido.",
          "Soporte para carga masiva de imágenes asociadas a los productos del catálogo."
        ],
        rules: [
          "Verificación de duplicados por nombre de producto antes de insertar.",
          "Resumen de importación: cantidad de productos creados exitosamente y reporte de errores si los hubiere."
        ],
        priority: "Media (Should have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Encuesta; WhatsApp Business es el canal digital predominante (74%) declarado por las MiPymes de Fusagasugá.",
        codeRef: "Product.php, MerchantController@products, CompanyProfile@whatsapp, views/consumer/merchant-profile.blade.php"
      }
    ]
  },
  {
    title: "Módulo 9: Arquitectura Transaccional Avanzada (Adicionales FusaShop)",
    description: "Funcionalidades avanzadas implementadas en FusaShop que garantizan cumplimiento tributario DIAN, liquidación multi-comercio y monetización.",
    stories: [
      {
        id: "HU027",
        title: "Facturación electrónica DIAN automatizada (Factus)",
        as: "Comerciante y Consumidor",
        want: "Que la plataforma expida automáticamente la factura electrónica legal con CUFE y XML ante la DIAN al confirmarse el pago",
        soThat: "Garantizar la validez tributaria de las ventas sin requerir software contable adicional por parte del comerciante",
        criteria: [
          "Integración asíncrona mediante background job (`IssueFactusInvoice`) con la API certificada de Factus al registrar pago `APPROVED` (RF17).",
          "Asignación de número fiscal consecutivo, generación del Código Único de Facturación Electrónica (`invoice_cufe`) y estampado cronológico.",
          "Descarga directa del PDF oficial de la factura y del archivo XML firmado desde el panel de órdenes del comprador y del comercio.",
          "Notificación por correo al comprador con el enlace directo para consultar su factura electrónica."
        ],
        rules: [
          "Manejo de reintentos exponenciales con registro de fallas en `integration_logs` si el proveedor fiscal presenta intermitencia.",
          "Cumplimiento de la Resolución 000042 de la DIAN para facturación electrónica."
        ],
        priority: "Alta (Must have)",
        estimate: "5 días ideales (8 Story Points)",
        evidence: "Marco legal tributario colombiano (Ley 2155 de 2021); obligatoriedad de factura electrónica para comercio formal.",
        codeRef: "FactusService.php, IssueFactusInvoice.php, Order@invoice_cufe, views/merchant/orders.blade.php"
      },
      {
        id: "HU028",
        title: "Liquidación multi-comercio y billetera digital (Wallet)",
        as: "Comerciante y Administrador",
        want: "Que los pagos de órdenes con múltiples vendedores se dividan automáticamente restando la comisión de plataforma",
        soThat: "Asegurar dispersiones transparentes, conciliación contable exacta y control de fondos disponibles",
        criteria: [
          "División automática del pedido principal en subórdenes comerciales independientes (`merchant_suborders`) por cada vendedor.",
          "Cálculo y retención automática de la comisión de plataforma del 8% (`commission_cents`, `settings.commission_rate`).",
          "Libro contable inmutable (`merchant_wallet_entries`) con registro de débitos y créditos en centavos COP e idempotencia por transacción.",
          "Panel financiero del comerciante (`/merchant/finances`) con saldo disponible, historial de dispersiones y cuenta bancaria inscrita."
        ],
        rules: [
          "Los fondos pasan a estado disponible (`available`) únicamente cuando el pedido es entregado a satisfacción.",
          "Exportación de lotes de liquidación para dispersión bancaria vía analista (`/settlements/export`)."
        ],
        priority: "Alta (Must have)",
        estimate: "6 días ideales (10 Story Points)",
        evidence: "Modelo de negocio marketplace multi-vendedor; sostenibilidad financiera y formalización bancaria de MiPymes.",
        codeRef: "MerchantSettlementService.php, MerchantSuborder.php, MerchantWalletEntry.php, views/merchant/finances.blade.php"
      },
      {
        id: "HU029",
        title: "Sistema de reservas y agendamiento de citas",
        as: "Consumidor de servicios locales",
        want: "Reservar citas o servicios presenciales seleccionando fecha, franja horaria y número de personas",
        soThat: "Garantizar mi atención oportuna en negocios de gastronomía, estética y servicios profesionales sin filas",
        criteria: [
          "Módulo de reservas integrado (`reservations`) con selector interactivo de fecha (`preferred_date`) y hora (`preferred_time`).",
          "Configuración de número de asistentes (`party_size`) y notas especiales del cliente.",
          "Notificación inmediata al comercio para aceptar o rechazar la solicitud de agendamiento desde su panel.",
          "Opción para que el cliente consulte el estado de sus reservas y pueda cancelar oportunamente con antelación."
        ],
        rules: [
          "Generación de código alfanumérico único de referencia de reserva (FUSA-RES-XXXX).",
          "Restricción de reservas para fechas pasadas o días no hábiles del comercio."
        ],
        priority: "Media (Should have)",
        estimate: "4 días ideales (6 Story Points)",
        evidence: "Encuesta; el 31% del tejido empresarial de Fusagasugá corresponde al sector servicios y gastronomía.",
        codeRef: "Reservation.php, Consumer/ReservationController.php, Merchant/ReservationController.php, views/consumer/reservations.blade.php"
      },
      {
        id: "HU030",
        title: "Pauta publicitaria y banners patrocinados",
        as: "Comerciante aliado",
        want: "Solicitar pauta en el carrusel principal de la plataforma pagando la tarifa oficial",
        soThat: "Aumentar la visibilidad de mi marca y promocionar ofertas ante todos los visitantes de FusaShop",
        criteria: [
          "Formulario de solicitud de banner (`banner_requests`) con carga de pieza gráfica, enlace de destino y comprobante de pago de pauta.",
          "Configuración administrativa de la tarifa oficial de pauta en COP (`settings.banner_fee`).",
          "Panel de revisión para el analista con acciones de aprobación automática o rechazo motivado.",
          "Al aprobarse, el banner se activa inmediatamente en el carrusel de inicio (`global_banners`) registrando impresiones y clics."
        ],
        rules: [
          "Validación de dimensiones recomendadas (1920x600 px) y peso máximo de 3 MB.",
          "Manejo de fecha de inicio y vencimiento programado de la campaña publicitaria."
        ],
        priority: "Baja (Could have)",
        estimate: "4 días ideales (6 Story Points)",
        evidence: "Estrategia de monetización y visibilidad destacada demandada por comerciantes líderes de Fusagasugá.",
        codeRef: "BannerRequest.php, GlobalBanner.php, AnalystController@banners, MerchantController@bannerRequest"
      }
    ]
  }
];

// Build Document
async function generateDocx() {
  const docChildren = [];

  // Title & Header Block
  docChildren.push(
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 200, after: 100 },
      children: [
        new TextRun({ text: "FUSASHOP", bold: true, size: 36, color: PRIMARY, font: "Segoe UI" })
      ]
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 50, after: 150 },
      children: [
        new TextRun({ text: "ESPECIFICACIÓN FORMAL DE HISTORIAS DE USUARIO", bold: true, size: 26, color: SECONDARY, font: "Segoe UI" }),
        new TextRun({ text: "\nModelo Ágil de Requerimientos y Concordancia Técnica v2.0", size: 20, color: TEXT_MUTED, font: "Segoe UI" })
      ]
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 50, after: 300 },
      children: [
        new TextRun({ text: "Proyecto de Grado • Universidad de Cundinamarca • Fusagasugá • Octubre 2026", italics: true, size: 18, color: TEXT_MUTED, font: "Segoe UI" })
      ]
    })
  );

  // Executive Summary
  docChildren.push(
    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      spacing: { before: 200, after: 100 },
      children: [new TextRun({ text: "1. Resumen Ejecutivo y Matriz de Concordancia", bold: true, size: 24, color: PRIMARY, font: "Segoe UI" })]
    }),
    new Paragraph({
      spacing: { before: 80, after: 140 },
      children: [
        new TextRun({
          text: "El presente documento establece la especificación exhaustiva y normalizada de las Historias de Usuario (HU) de la plataforma FusaShop. Este marco alinea el levantamiento de requerimientos de la encuesta diagnóstica a MiPymes de Fusagasugá con la implementación real del código fuente (Laravel 12, PHP 8.3+, MariaDB y servicios de integración).",
          size: 19,
          font: "Segoe UI",
          color: TEXT_DARK
        })
      ]
    })
  );

  // Table of User Stories Summary
  const summaryHeaders = [
    cell("Módulo Funcional", true, 36, 1, { fill: SECONDARY }),
    cell("N° Historias", true, 16, 1, { fill: SECONDARY, alignment: AlignmentType.CENTER }),
    cell("Identificadores", true, 26, 1, { fill: SECONDARY, alignment: AlignmentType.CENTER }),
    cell("Estimación", true, 22, 1, { fill: SECONDARY, alignment: AlignmentType.CENTER })
  ];

  const summaryRows = [new TableRow({ children: summaryHeaders })];

  const summaryData = [
    ["Módulo 1: Autenticación y Perfiles", "3", "HU001 - HU003", "9 días ideales"],
    ["Módulo 2: Catálogo e Inventario", "3", "HU004 - HU006", "13 días ideales"],
    ["Módulo 3: Transacciones y Logística", "4", "HU007 - HU010", "21 días ideales"],
    ["Módulo 4: Analítica y Administración", "3", "HU011 - HU013", "13 días ideales"],
    ["Módulo 5: Comunicación, Soporte y Valoraciones", "4", "HU014 - HU017", "13 días ideales"],
    ["Módulo 6: Fidelización y Evaluación de Impacto", "4", "HU018 - HU021", "15 días ideales"],
    ["Módulo 7: Caracterización del Comerciante", "3", "HU022 - HU024", "4 días ideales"],
    ["Módulo 8: Diagnóstico de Barreras y Adopción", "2", "HU025 - HU026", "10 días ideales"],
    ["Subtotal Base de Investigación", "26", "HU001 - HU026", "98 días ideales"],
    ["Módulo 9: Arquitectura Transaccional Avanzada (Adicionales)", "4", "HU027 - HU030", "19 días ideales"],
    ["TOTAL GENERAL DEL SISTEMA", "30", "HU001 - HU030", "117 días ideales"]
  ];

  summaryData.forEach((row, i) => {
    const isTotal = i >= summaryData.length - 2;
    summaryRows.push(
      new TableRow({
        children: [
          cell(row[0], isTotal, 36, 1, { fill: isTotal ? BG_LIGHT : (i % 2 === 1 ? BG_GRAY : "FFFFFF"), bold: isTotal, color: isTotal ? PRIMARY : TEXT_DARK }),
          cell(row[1], isTotal, 16, 1, { fill: isTotal ? BG_LIGHT : (i % 2 === 1 ? BG_GRAY : "FFFFFF"), bold: isTotal, alignment: AlignmentType.CENTER }),
          cell(row[2], isTotal, 26, 1, { fill: isTotal ? BG_LIGHT : (i % 2 === 1 ? BG_GRAY : "FFFFFF"), bold: isTotal, alignment: AlignmentType.CENTER }),
          cell(row[3], isTotal, 22, 1, { fill: isTotal ? BG_LIGHT : (i % 2 === 1 ? BG_GRAY : "FFFFFF"), bold: isTotal, alignment: AlignmentType.CENTER })
        ]
      })
    );
  });

  docChildren.push(
    new Table({
      width: { size: 100, type: WidthType.PERCENTAGE },
      borders: tableBorders,
      rows: summaryRows
    }),
    new Paragraph({ spacing: { before: 180, after: 120 } })
  );

  // Iterating Modules and User Stories
  docChildren.push(
    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      spacing: { before: 300, after: 120 },
      children: [new TextRun({ text: "2. Fichas Detalladas de Historias de Usuario", bold: true, size: 24, color: PRIMARY, font: "Segoe UI" })]
    })
  );

  modulesData.forEach((mod, modIdx) => {
    docChildren.push(
      new Paragraph({
        heading: HeadingLevel.HEADING_2,
        spacing: { before: 260, after: 80 },
        children: [new TextRun({ text: mod.title, bold: true, size: 21, color: SECONDARY, font: "Segoe UI" })]
      }),
      new Paragraph({
        spacing: { before: 40, after: 140 },
        children: [new TextRun({ text: mod.description, italics: true, size: 18, color: TEXT_MUTED, font: "Segoe UI" })]
      })
    );

    mod.stories.forEach((story) => {
      docChildren.push(
        userStoryTable(story),
        new Paragraph({ spacing: { before: 140, after: 140 } })
      );
    });
  });

  // Section 3: Traceability Matrix (RF vs HU)
  docChildren.push(
    new Paragraph({
      heading: HeadingLevel.HEADING_1,
      spacing: { before: 300, after: 120 },
      children: [new TextRun({ text: "3. Matriz de Trazabilidad Requerimientos Funcionales (RF01 - RF37)", bold: true, size: 24, color: PRIMARY, font: "Segoe UI" })]
    })
  );

  const rfHeaders = [
    cell("Código", true, 14, 1, { fill: SECONDARY, alignment: AlignmentType.CENTER }),
    cell("Requerimiento Funcional (RF)", true, 56, 1, { fill: SECONDARY }),
    cell("HU Asociada", true, 16, 1, { fill: SECONDARY, alignment: AlignmentType.CENTER }),
    cell("Estado en Código", true, 14, 1, { fill: SECONDARY, alignment: AlignmentType.CENTER })
  ];

  const rfRows = [new TableRow({ children: rfHeaders })];

  const rfData = [
    ["RF01", "Registro de usuarios con validación de correo único", "HU001, HU003", "Concordante"],
    ["RF02", "Autenticación segura basada en sesiones y tokens Sanctum", "HU001, HU002, HU003", "Concordante"],
    ["RF03", "Recuperación de contraseña mediante enlace al correo", "HU001", "Concordante"],
    ["RF04", "Asignación de roles diferenciados (consumidor, comerciante, analista)", "HU002, HU003", "Concordante"],
    ["RF05", "CRUD de productos con estados (activo, pausado, archivado)", "HU004", "Concordante"],
    ["RF06", "Soporte de galería de hasta 5 imágenes con compresión", "HU004", "Concordante"],
    ["RF07", "Clasificación por categorías y subsectores económicos", "HU004", "Concordante"],
    ["RF08", "Búsqueda por texto, categoría, rango de precio y valoración", "HU005", "Concordante"],
    ["RF09", "Recomendación de productos relacionados y búsqueda asistida FusaBot", "HU005", "Concordante"],
    ["RF10", "Control de stock disponible y unidades reservadas pesimistas", "HU006, HU007", "Concordante"],
    ["RF11", "Alertas automáticas de stock bajo y agotamiento por correo y web", "HU006", "Concordante"],
    ["RF12", "Carrito de compras con checkout selectivo por ítem/tienda", "HU007", "Concordante"],
    ["RF13", "Cálculo y desglose dinámico de subtotal, fletes y descuentos", "HU007", "Concordante"],
    ["RF14", "Integración con pasarela Wompi para tarjetas, PSE y Nequi", "HU009", "Concordante"],
    ["RF15", "Cumplimiento de estándares de seguridad PCI-DSS vía widget", "HU009", "Concordante"],
    ["RF16", "Webhooks asíncronos con verificación SHA-256 y anti-replay", "HU009", "Concordante"],
    ["RF17", "Generación de comprobante en PDF y Facturación Electrónica DIAN", "HU009, HU027", "Concordante"],
    ["RF18", "Trazabilidad de estados del pedido con eventos Wompi y Envía", "HU010", "Concordante"],
    ["RF19", "Generación automatizada de guía de despacho y etiqueta PDF con Envía", "HU010", "Concordante"],
    ["RF20", "Dashboard analítico en tiempo real de ventas, tráfico y conversión", "HU011", "Concordante"],
    ["RF21", "Exportación de reportes analíticos a formatos CSV y vista PDF", "HU011", "Concordante"],
    ["RF22", "Resumen de ventas por período con comparativa histórica", "HU012", "Concordante"],
    ["RF23", "Ranking de productos más vendidos y diagnóstico de menor rotación", "HU012", "Concordante"],
    ["RF24", "Activación, suspensión y bloqueo de cuentas con bitácora de auditoría", "HU013", "Concordante"],
    ["RF25", "Asignación y modificación de permisos de usuarios", "HU013", "Concordante"],
    ["RF26", "Mensajería directa vinculada a la ficha del producto con polling", "HU014", "Concordante"],
    ["RF27", "Notificaciones push y correo al comerciante ante nuevos mensajes", "HU014", "Concordante"],
    ["RF28", "Configuración de preferencias de notificación por canal y evento", "HU015", "Concordante"],
    ["RF29", "Calificaciones de 1 a 5 estrellas y reseñas tras entrega confirmada", "HU017", "Concordante"],
    ["RF30", "Cálculo del promedio ponderado de valoraciones por producto", "HU017", "Concordante"],
    ["RF31", "Centro de ayuda y radicación de PQRS con control de SLA legal", "HU016", "Concordante"],
    ["RF32", "Creación de cupones de descuento fijo o porcentual con vigencia", "HU018", "Concordante"],
    ["RF33", "Validación y aplicación de cupones en tiempo real en checkout", "HU018", "Concordante"],
    ["RF34", "Acreditación automática de puntos de fidelidad tras entrega", "HU019", "Concordante"],
    ["RF35", "Consulta y redención de saldo de puntos FusaCoins en el pago", "HU019", "Concordante"],
    ["RF36", "Tablero de indicadores de impacto socioeconómico y territorial", "HU020", "Concordante"],
    ["RF37", "Exportación de datasets anonimizados según Ley 1581 de 2012", "HU021", "Concordante"]
  ];

  rfData.forEach((row, idx) => {
    rfRows.push(
      new TableRow({
        children: [
          cell(row[0], false, 14, 1, { fill: idx % 2 === 1 ? BG_GRAY : "FFFFFF", bold: true, alignment: AlignmentType.CENTER }),
          cell(row[1], false, 56, 1, { fill: idx % 2 === 1 ? BG_GRAY : "FFFFFF" }),
          cell(row[2], false, 16, 1, { fill: idx % 2 === 1 ? BG_GRAY : "FFFFFF", alignment: AlignmentType.CENTER }),
          cell(row[3], false, 14, 1, { fill: BG_LIGHT, color: PRIMARY, bold: true, alignment: AlignmentType.CENTER })
        ]
      })
    );
  });

  docChildren.push(
    new Table({
      width: { size: 100, type: WidthType.PERCENTAGE },
      borders: tableBorders,
      rows: rfRows
    })
  );

  const doc = new Document({
    sections: [
      {
        properties: {
          page: {
            margin: { top: 1200, bottom: 1200, left: 1300, right: 1300 }
          }
        },
        headers: {
          default: new Header({
            children: [
              new Paragraph({
                alignment: AlignmentType.RIGHT,
                spacing: { after: 120 },
                children: [
                  new TextRun({ text: "FusaShop  |  Especificación Formal de Historias de Usuario v2.0", bold: true, size: 16, color: PRIMARY, font: "Segoe UI" })
                ]
              })
            ]
          })
        },
        footers: {
          default: new Footer({
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 120 },
                children: [
                  new TextRun({ text: "FusaShop • Marketplace para Fusagasugá  |  Página ", size: 16, color: TEXT_MUTED, font: "Segoe UI" }),
                  new TextRun({ children: [PageNumber.CURRENT], size: 16, color: PRIMARY, bold: true, font: "Segoe UI" }),
                  new TextRun({ text: " de ", size: 16, color: TEXT_MUTED, font: "Segoe UI" }),
                  new TextRun({ children: [PageNumber.TOTAL_PAGES], size: 16, color: PRIMARY, bold: true, font: "Segoe UI" })
                ]
              })
            ]
          })
        },
        children: docChildren
      }
    ]
  });

  const buffer = await Packer.toBuffer(doc);

  const outDesktop = "C:/Users/HUAWEI/Desktop/FusaShop_Historias_de_Usuario_Corregidas.docx";
  const outDocs = "C:/Users/HUAWEI/Desktop/fusashop2/docs/FusaShop_Historias_de_Usuario_Corregidas.docx";

  fs.writeFileSync(outDesktop, buffer);
  fs.writeFileSync(outDocs, buffer);

  console.log("Documents successfully created!");
  console.log("  1. " + outDesktop);
  console.log("  2. " + outDocs);
}

generateDocx().catch(err => console.error("Error generating docx:", err));
