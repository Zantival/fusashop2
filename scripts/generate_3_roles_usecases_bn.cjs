/**
 * Generador Completo de los 3 Diagramas de Casos de Uso (UML 2.5)
 * Estilo Blanco y Negro con "Muñequitos" (Stick Figures)
 * 1. Consumidor (Cliente / Comprador)
 * 2. Comerciante (Vendedor de Tienda)
 * 3. Administrador / Analista (Gobernanza)
 * Genera archivos SVG, PNG (vía sharp) y un Visor HTML.
 */

const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

function drawActor(x, y, label, role = '') {
  return `
    <g class="actor-group" transform="translate(${x}, ${y})">
      <!-- Cabeza -->
      <circle cx="0" cy="-44" r="19" fill="#FFFFFF" stroke="#000000" stroke-width="2.4" />
      <!-- Tronco -->
      <line x1="0" y1="-25" x2="0" y2="24" stroke="#000000" stroke-width="2.4" />
      <!-- Brazos -->
      <line x1="-30" y1="-6" x2="30" y2="-6" stroke="#000000" stroke-width="2.4" />
      <!-- Piernas -->
      <line x1="0" y1="24" x2="-24" y2="65" stroke="#000000" stroke-width="2.4" />
      <line x1="0" y1="24" x2="24" y2="65" stroke="#000000" stroke-width="2.4" />
      <!-- Etiqueta del Actor -->
      <text x="0" y="90" font-family="'Segoe UI', Arial, sans-serif" font-size="13.5" font-weight="800" fill="#000000" text-anchor="middle">${label}</text>
      ${role ? `<text x="0" y="108" font-family="'Segoe UI', Arial, sans-serif" font-size="11" font-weight="600" fill="#333333" text-anchor="middle">${role}</text>` : ''}
    </g>
  `;
}

function drawUseCase(cx, cy, rx, ry, text1, text2 = '') {
  return `
    <g class="usecase-node">
      <ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#FFFFFF" stroke="#000000" stroke-width="1.8" />
      <text x="${cx}" y="${text2 ? cy - 7 : cy}" font-family="'Segoe UI', Arial, sans-serif" font-size="11.5" font-weight="700" fill="#000000" text-anchor="middle" dominant-baseline="middle">${text1}</text>
      ${text2 ? `<text x="${cx}" y="${cy + 9}" font-family="'Segoe UI', Arial, sans-serif" font-size="9.5" font-weight="500" fill="#222222" text-anchor="middle" dominant-baseline="middle">${text2}</text>` : ''}
    </g>
  `;
}

function drawTag(x, y, text) {
  const width = text.length * 7 + 10;
  return `
    <g transform="translate(${x}, ${y})">
      <rect x="${-width/2}" y="-8" width="${width}" height="16" fill="#FFFFFF" stroke="none" />
      <text x="0" y="4" font-family="'Segoe UI', Arial, sans-serif" font-size="9.5" font-weight="700" fill="#000000" text-anchor="middle">${text}</text>
    </g>
  `;
}

const defs = `
  <defs>
    <marker id="arrow-open" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
      <path d="M 0 1.5 L 8 5 L 0 8.5" fill="none" stroke="#000000" stroke-width="1.6" />
    </marker>
  </defs>
  <style>
    .title-main { font-family: 'Segoe UI', Arial, sans-serif; font-size: 18px; font-weight: 800; fill: #000000; letter-spacing: 0.8px; text-anchor: middle; }
    .title-sub { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11.5px; font-weight: 600; fill: #333333; text-anchor: middle; }
    .boundary-title { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12.5px; font-weight: 800; fill: #000000; letter-spacing: 0.5px; }
    .assoc-line { fill: none; stroke: #000000; stroke-width: 1.6; }
    .dep-line { fill: none; stroke: #000000; stroke-width: 1.4; stroke-dasharray: 5,4; }
    .legend-title { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; font-weight: 800; fill: #000000; }
    .legend-text { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; fill: #000000; }
  </style>
`;

// =============================================================================
// 1. DIAGRAMA CONSUMIDOR
// =============================================================================
function generateConsumerSVG() {
  const W = 1050;
  const H = 980;

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
  ${defs}
  <rect width="${W}" height="${H}" fill="#FFFFFF" />

  <!-- Encabezado -->
  <text x="${W/2}" y="38" class="title-main">DIAGRAMA DE CASOS DE USO — ROL CONSUMIDOR</text>
  <text x="${W/2}" y="60" class="title-sub">Plataforma FusaShop · Especificación UML 2.5 en Blanco y Negro (Con Muñequito)</text>

  <!-- Límite del Sistema -->
  <rect x="230" y="80" width="780" height="810" fill="#FFFFFF" stroke="#000000" stroke-width="1.8" />
  <text x="245" y="104" class="boundary-title">Sistema FusaShop — Módulo Consumidor / Comprador</text>

  <!-- Muñequito: Consumidor -->
  ${drawActor(115, 430, 'Consumidor', '(Cliente / Comprador)')}

  <!-- Casos de Uso Principales (Columna Izquierda) -->
  ${drawUseCase(420, 145, 120, 26, 'CU-C01: Gestionar Cuenta', 'Registro y Verificación 6 Dígitos')}
  ${drawUseCase(420, 230, 120, 26, 'CU-C02: Explorar Catálogo', 'Búsqueda Asistida y Filtros')}
  ${drawUseCase(420, 315, 120, 26, 'CU-C03: Gestionar Carrito', 'Agregar / Modificar ítems')}
  ${drawUseCase(420, 410, 120, 26, 'CU-C04: Realizar Compra', 'Checkout de Productos Físicos')}
  ${drawUseCase(420, 525, 120, 26, 'CU-C07: Consultar Historial', 'Pedidos, Recibos y Facturas')}
  ${drawUseCase(420, 635, 120, 26, 'CU-C09: Reservar Servicios', 'Agendamiento Local y Citas')}
  ${drawUseCase(420, 725, 120, 26, 'CU-C11: Mensajería / Chat', 'Comunicación con Tienda')}
  ${drawUseCase(420, 810, 120, 26, 'CU-C12: Radicar PQRS', 'Peticiones, Quejas y Reclamos')}

  <!-- Casos de Uso Subordinados (Columna Derecha) -->
  ${drawUseCase(780, 145, 120, 25, 'CU-C01.1: Autenticar Cuenta', 'Login Email o Google OAuth')}
  ${drawUseCase(780, 365, 120, 25, 'CU-C05: Cotizar Envío', 'API Envia.com / Transportadora')}
  ${drawUseCase(780, 445, 120, 25, 'CU-C06: Procesar Pago', 'Pasarela Wompi / PSE')}
  ${drawUseCase(780, 515, 120, 25, 'CU-C08: Rastrear Envío', 'Guía y Trazabilidad en Ruta')}
  ${drawUseCase(780, 590, 120, 25, 'CU-C10: Calificar Producto', 'Reseña de Compra Recibida')}

  <!-- Asociaciones Directas Actor -> Casos de Uso -->
  <line x1="145" y1="390" x2="300" y2="145" class="assoc-line" />
  <line x1="145" y1="405" x2="300" y2="230" class="assoc-line" />
  <line x1="145" y1="420" x2="300" y2="315" class="assoc-line" />
  <line x1="145" y1="435" x2="300" y2="410" class="assoc-line" />
  <line x1="145" y1="455" x2="300" y2="525" class="assoc-line" />
  <line x1="145" y1="475" x2="300" y2="635" class="assoc-line" />
  <line x1="145" y1="495" x2="300" y2="725" class="assoc-line" />
  <line x1="145" y1="510" x2="300" y2="810" class="assoc-line" />

  <!-- Dependencias <<include>> -->
  <!-- CU-C01 -> CU-C01.1 -->
  <line x1="540" y1="145" x2="660" y2="145" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 140, '«include»')}

  <!-- CU-C04 -> CU-C05 -->
  <path d="M 540 400 L 660 375" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(598, 382, '«include»')}

  <!-- CU-C04 -> CU-C06 -->
  <path d="M 540 420 L 660 440" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(598, 437, '«include»')}

  <!-- Dependencias <<extend>> -->
  <!-- CU-C08 -> CU-C07 -->
  <path d="M 660 520 L 540 525" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 517, '«extend»')}

  <!-- CU-C10 -> CU-C07 -->
  <path d="M 660 580 L 538 540" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 568, '«extend»')}

  <!-- Cuadro de Convenciones Inferior -->
  <rect x="230" y="905" width="780" height="55" fill="#FFFFFF" stroke="#000000" stroke-width="1.2" />
  <text x="245" y="925" class="legend-title">CONVENCIONES UML 2.5 (CASO DE USO):</text>
  <text x="245" y="942" class="legend-text">• Muñequito: Actor Consumidor (Iniciador). • Rectángulo: Límite del sistema. • Elipses: Casos de uso funcionales.</text>
  <text x="245" y="954" class="legend-text">• Línea continua: Asociación directa. • Línea discontinua con flecha: Dependencias «include» (obligatoria) y «extend» (condicional).</text>
</svg>`;
}

// =============================================================================
// 2. DIAGRAMA COMERCIANTE
// =============================================================================
function generateMerchantSVG() {
  const W = 1050;
  const H = 1040;

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
  ${defs}
  <rect width="${W}" height="${H}" fill="#FFFFFF" />

  <!-- Encabezado -->
  <text x="${W/2}" y="38" class="title-main">DIAGRAMA DE CASOS DE USO — ROL COMERCIANTE</text>
  <text x="${W/2}" y="60" class="title-sub">Plataforma FusaShop · Especificación UML 2.5 en Blanco y Negro (Con Muñequito)</text>

  <!-- Límite del Sistema -->
  <rect x="230" y="80" width="780" height="870" fill="#FFFFFF" stroke="#000000" stroke-width="1.8" />
  <text x="245" y="104" class="boundary-title">Sistema FusaShop — Panel de Administración de Tienda</text>

  <!-- Muñequito: Comerciante -->
  ${drawActor(115, 460, 'Comerciante', '(Vendedor Verificado)')}

  <!-- Casos de Uso Principales (Columna Izquierda) -->
  ${drawUseCase(420, 140, 120, 26, 'CU-M01: Enviar Registro KYC', 'Carga de RUT y Documentos')}
  ${drawUseCase(420, 220, 120, 26, 'CU-M02: Configurar Tienda', 'Perfil Comercial y Políticas')}
  ${drawUseCase(420, 300, 120, 26, 'CU-M03: Publicar Catálogo', 'Crear y Modificar Productos')}
  ${drawUseCase(420, 380, 120, 26, 'CU-M04: Controlar Inventario', 'Stock y Alertas de Agotado')}
  ${drawUseCase(420, 460, 120, 26, 'CU-M05: Gestionar Pedidos', 'Recepción y Despacho')}
  ${drawUseCase(420, 550, 120, 26, 'CU-M08: Gestionar Reservas', 'Aprobar Citas y Servicios')}
  ${drawUseCase(420, 635, 120, 26, 'CU-M09: Consultar Finanzas', 'Ventas y Liquidaciones')}
  ${drawUseCase(420, 715, 120, 26, 'CU-M10: Atender Mensajes', 'Chat y Soporte a Clientes')}
  ${drawUseCase(420, 795, 120, 26, 'CU-M11: Responder Reseñas', 'Gestión de Reputación')}
  ${drawUseCase(420, 875, 120, 26, 'CU-M12: Solicitar Publicidad', 'Postulación de Banners')}

  <!-- Casos de Uso Subordinados (Columna Derecha) -->
  ${drawUseCase(780, 300, 120, 25, 'CU-M03.1: Gestionar Fotos', 'Variantes, Tallas y Precios')}
  ${drawUseCase(780, 425, 120, 25, 'CU-M06: Generar Guía Envío', 'Etiqueta Envia.com')}
  ${drawUseCase(780, 500, 120, 25, 'CU-M07: Emitir Factura DIAN', 'Integración Factus Electrónica')}
  ${drawUseCase(780, 635, 120, 25, 'CU-M09.1: Exportar Balance', 'Reporte de Ventas en CSV')}

  <!-- Asociaciones Directas Actor -> Casos de Uso -->
  <line x1="145" y1="410" x2="300" y2="140" class="assoc-line" />
  <line x1="145" y1="425" x2="300" y2="220" class="assoc-line" />
  <line x1="145" y1="440" x2="300" y2="300" class="assoc-line" />
  <line x1="145" y1="455" x2="300" y2="380" class="assoc-line" />
  <line x1="145" y1="470" x2="300" y2="460" class="assoc-line" />
  <line x1="145" y1="485" x2="300" y2="550" class="assoc-line" />
  <line x1="145" y1="500" x2="300" y2="635" class="assoc-line" />
  <line x1="145" y1="515" x2="300" y2="715" class="assoc-line" />
  <line x1="145" y1="530" x2="300" y2="795" class="assoc-line" />
  <line x1="145" y1="545" x2="300" y2="875" class="assoc-line" />

  <!-- Dependencias <<include>> y <<extend>> -->
  <!-- CU-M03 -> CU-M03.1 (include) -->
  <line x1="540" y1="300" x2="660" y2="300" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 295, '«include»')}

  <!-- CU-M06 -> CU-M05 (extend) -->
  <path d="M 660 435 L 538 455" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 440, '«extend»')}

  <!-- CU-M07 -> CU-M05 (extend) -->
  <path d="M 660 495 L 538 475" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 490, '«extend»')}

  <!-- CU-M09.1 -> CU-M09 (extend) -->
  <line x1="660" y1="635" x2="540" y2="635" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 630, '«extend»')}

  <!-- Cuadro de Convenciones Inferior -->
  <rect x="230" y="965" width="780" height="55" fill="#FFFFFF" stroke="#000000" stroke-width="1.2" />
  <text x="245" y="985" class="legend-title">CONVENCIONES UML 2.5 (PANEL COMERCIANTE):</text>
  <text x="245" y="1002" class="legend-text">• Muñequito: Actor Comerciante (Tienda Habilitada tras KYC). • Rectángulo: Límite del panel del vendedor.</text>
  <text x="245" y="1014" class="legend-text">• «include»: Inclusión mandataria del caso. • «extend»: Flujo opcional de apoyo logístico o tributario.</text>
</svg>`;
}

// =============================================================================
// 3. DIAGRAMA ADMINISTRADOR / ANALISTA
// =============================================================================
function generateAdminSVG() {
  const W = 1050;
  const H = 1040;

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
  ${defs}
  <rect width="${W}" height="${H}" fill="#FFFFFF" />

  <!-- Encabezado -->
  <text x="${W/2}" y="38" class="title-main">DIAGRAMA DE CASOS DE USO — ROL ADMINISTRADOR</text>
  <text x="${W/2}" y="60" class="title-sub">Plataforma FusaShop · Especificación UML 2.5 en Blanco y Negro (Con Muñequito)</text>

  <!-- Límite del Sistema -->
  <rect x="230" y="80" width="780" height="870" fill="#FFFFFF" stroke="#000000" stroke-width="1.8" />
  <text x="245" y="104" class="boundary-title">Sistema FusaShop — Consola de Gobierno y Supervisión</text>

  <!-- Muñequito: Administrador / Analista -->
  ${drawActor(115, 460, 'Administrador', '(Analista / Gobierno)')}

  <!-- Casos de Uso Principales (Columna Izquierda) -->
  ${drawUseCase(420, 140, 120, 26, 'CU-A01: Administrar Cuentas', 'Bloqueo, Activación y Roles')}
  ${drawUseCase(420, 225, 120, 26, 'CU-A02: Verificar KYC Tiendas', 'Aprobación de RUT y Legalidad')}
  ${drawUseCase(420, 310, 120, 26, 'CU-A03: Clasificar Sectores', 'Categorías y Agrupación')}
  ${drawUseCase(420, 395, 120, 26, 'CU-A04: Supervisar Pedidos', 'Auditoría Global Multitienda')}
  ${drawUseCase(420, 485, 120, 26, 'CU-A05: Liquidar Comisiones', 'Cortes y Dispersión de Saldo')}
  ${drawUseCase(420, 575, 120, 26, 'CU-A06: Monitorear Servicios', 'Salud Webhooks Wompi/Envia')}
  ${drawUseCase(420, 665, 120, 26, 'CU-A07: Gestionar Campañas', 'Aprobación Banners y Vitrina')}
  ${drawUseCase(420, 750, 120, 26, 'CU-A08: Tramitar PQRS Legal', 'Cumplimiento SLA Consumidor')}
  ${drawUseCase(420, 825, 120, 26, 'CU-A09: Consultar Analítica', 'Diagnóstico Causal y ML')}
  ${drawUseCase(420, 900, 120, 26, 'CU-A10: Auditar Trazabilidad', 'Logs Inmutables de Sistema')}

  <!-- Casos de Uso Subordinados (Columna Derecha) -->
  ${drawUseCase(780, 140, 120, 25, 'CU-A01.1: Bloquear Usuario', 'Inhabilitación de Acceso')}
  ${drawUseCase(780, 225, 120, 25, 'CU-A02.1: Validar RUT Dian', 'Cotejo de Razón Social')}
  ${drawUseCase(780, 395, 120, 25, 'CU-A04.1: Conciliar Pagos', 'Firma Wompi e Idempotencia')}
  ${drawUseCase(780, 485, 120, 25, 'CU-A05.1: Exportar Balance', 'Reporte Contable en CSV')}
  ${drawUseCase(780, 575, 120, 25, 'CU-A06.1: Reintentar Jobs', 'Cola de Procesos Fallidos')}

  <!-- Asociaciones Directas Actor -> Casos de Uso -->
  <line x1="145" y1="410" x2="300" y2="140" class="assoc-line" />
  <line x1="145" y1="425" x2="300" y2="225" class="assoc-line" />
  <line x1="145" y1="440" x2="300" y2="310" class="assoc-line" />
  <line x1="145" y1="455" x2="300" y2="395" class="assoc-line" />
  <line x1="145" y1="470" x2="300" y2="485" class="assoc-line" />
  <line x1="145" y1="485" x2="300" y2="575" class="assoc-line" />
  <line x1="145" y1="500" x2="300" y2="665" class="assoc-line" />
  <line x1="145" y1="515" x2="300" y2="750" class="assoc-line" />
  <line x1="145" y1="530" x2="300" y2="825" class="assoc-line" />
  <line x1="145" y1="545" x2="300" y2="900" class="assoc-line" />

  <!-- Dependencias <<include>> y <<extend>> -->
  <!-- CU-A01.1 -> CU-A01 (extend) -->
  <line x1="660" y1="140" x2="540" y2="140" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 135, '«extend»')}

  <!-- CU-A02 -> CU-A02.1 (include) -->
  <line x1="540" y1="225" x2="660" y2="225" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 220, '«include»')}

  <!-- CU-A04 -> CU-A04.1 (include) -->
  <line x1="540" y1="395" x2="660" y2="395" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 390, '«include»')}

  <!-- CU-A05.1 -> CU-A05 (extend) -->
  <line x1="660" y1="485" x2="540" y2="485" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 480, '«extend»')}

  <!-- CU-A06.1 -> CU-A06 (extend) -->
  <line x1="660" y1="575" x2="540" y2="575" class="dep-line" marker-end="url(#arrow-open)" />
  ${drawTag(600, 570, '«extend»')}

  <!-- Cuadro de Convenciones Inferior -->
  <rect x="230" y="965" width="780" height="55" fill="#FFFFFF" stroke="#000000" stroke-width="1.2" />
  <text x="245" y="985" class="legend-title">CONVENCIONES UML 2.5 (ADMINISTRACIÓN Y GOBERNANZA):</text>
  <text x="245" y="1002" class="legend-text">• Muñequito: Actor Administrador / Analista. • Rectángulo: Módulo de supervisión y gobernanza general.</text>
  <text x="245" y="1014" class="legend-text">• «include»: Subproceso mandatario de validación/conciliación. • «extend»: Acción especializada o soporte condicional.</text>
</svg>`;
}

// =============================================================================
// EJECUCIÓN Y GENERACIÓN DE SVG + PNG
// =============================================================================
async function run() {
  const docsDir = path.resolve('docs');
  if (!fs.existsSync(docsDir)) {
    fs.mkdirSync(docsDir, { recursive: true });
  }

  const svg1 = generateConsumerSVG();
  const svg2 = generateMerchantSVG();
  const svg3 = generateAdminSVG();

  const fileSvg1 = path.join(docsDir, 'diagrama-cu-consumidor-bn.svg');
  const fileSvg2 = path.join(docsDir, 'diagrama-cu-comerciante-bn.svg');
  const fileSvg3 = path.join(docsDir, 'diagrama-cu-administrador-bn.svg');

  fs.writeFileSync(fileSvg1, svg1, 'utf8');
  fs.writeFileSync(fileSvg2, svg2, 'utf8');
  fs.writeFileSync(fileSvg3, svg3, 'utf8');

  console.log('[OK] 3 Archivos SVG guardados con éxito.');

  // Convertir a PNG de alta resolución
  const filePng1 = path.join(docsDir, 'diagrama-cu-consumidor-bn.png');
  const filePng2 = path.join(docsDir, 'diagrama-cu-comerciante-bn.png');
  const filePng3 = path.join(docsDir, 'diagrama-cu-administrador-bn.png');

  await sharp(Buffer.from(svg1)).png().toFile(filePng1);
  await sharp(Buffer.from(svg2)).png().toFile(filePng2);
  await sharp(Buffer.from(svg3)).png().toFile(filePng3);

  console.log('[OK] 3 Archivos PNG de alta resolución guardados con éxito.');

  // Visor HTML unificado
  const htmlPath = path.join(docsDir, 'diagramas-casos-de-uso-3-roles-bn.html');
  const htmlContent = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Diagramas de Casos de Uso FusaShop — Blanco y Negro</title>
  <style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #F8FAFC; color: #0F172A; margin: 0; padding: 25px; }
    .container { max-width: 1100px; margin: 0 auto; }
    h1 { text-align: center; margin-bottom: 6px; }
    p.lead { text-align: center; color: #475569; margin-top: 0; margin-bottom: 30px; font-size: 15px; }
    .card { background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; padding: 20px; margin-bottom: 35px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .card h2 { margin-top: 0; border-bottom: 2px solid #000000; padding-bottom: 8px; font-size: 19px; }
    .preview { text-align: center; background: #FFFFFF; border: 1px solid #000000; padding: 12px; margin-top: 12px; }
    .preview img { max-width: 100%; height: auto; display: block; margin: 0 auto; }
    .actions { margin-top: 14px; display: flex; gap: 10px; justify-content: flex-end; }
    .btn { padding: 8px 14px; background: #000000; color: #FFFFFF; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 700; }
    .btn:hover { background: #333333; }
  </style>
</head>
<body>
  <div class="container">
    <h1>FusaShop · Diagramas de Casos de Uso (UML 2.5)</h1>
    <p class="lead">Estilo formal en Blanco y Negro con Actores ("Muñequitos") por cada rol para Tesis y Documentación</p>

    <!-- 1. Consumidor -->
    <div class="card">
      <h2>1. Rol Consumidor (Cliente / Comprador)</h2>
      <div class="preview">
        <img src="diagrama-cu-consumidor-bn.png" alt="Diagrama de Casos de Uso Consumidor FusaShop" />
      </div>
      <div class="actions">
        <a class="btn" href="diagrama-cu-consumidor-bn.svg" download>Descargar SVG</a>
        <a class="btn" href="diagrama-cu-consumidor-bn.png" download>Descargar PNG</a>
      </div>
    </div>

    <!-- 2. Comerciante -->
    <div class="card">
      <h2>2. Rol Comerciante (Vendedor / Tienda)</h2>
      <div class="preview">
        <img src="diagrama-cu-comerciante-bn.png" alt="Diagrama de Casos de Uso Comerciante FusaShop" />
      </div>
      <div class="actions">
        <a class="btn" href="diagrama-cu-comerciante-bn.svg" download>Descargar SVG</a>
        <a class="btn" href="diagrama-cu-comerciante-bn.png" download>Descargar PNG</a>
      </div>
    </div>

    <!-- 3. Administrador -->
    <div class="card">
      <h2>3. Rol Administrador (Analista / Gobernanza)</h2>
      <div class="preview">
        <img src="diagrama-cu-administrador-bn.png" alt="Diagrama de Casos de Uso Administrador FusaShop" />
      </div>
      <div class="actions">
        <a class="btn" href="diagrama-cu-administrador-bn.svg" download>Descargar SVG</a>
        <a class="btn" href="diagrama-cu-administrador-bn.png" download>Descargar PNG</a>
      </div>
    </div>
  </div>
</body>
</html>`;

  fs.writeFileSync(htmlPath, htmlContent, 'utf8');
  console.log(`[OK] Visor HTML unificado listo en: ${htmlPath}`);
}

run().catch(console.error);
