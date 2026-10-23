/**
 * Generador de Diagrama de Casos de Uso UML 2.5 en Blanco y Negro Formal
 * Estilo técnico sobrio para Tesis de Grado — Ecosistema FusaShop
 */

const fs = require('fs');
const path = require('path');

console.log('Generando Diagrama de Casos de Uso UML en Blanco y Negro...');

const svgWidth = 1450;
const svgHeight = 1250;

// Helper para dibujar un stick figure de actor UML
function drawActor(x, y, label, roleText = '') {
  return `
    <g transform="translate(${x}, ${y})">
      <!-- Cabeza -->
      <circle cx="0" cy="-35" r="14" fill="#FFFFFF" stroke="#000000" stroke-width="2" />
      <!-- Tronco -->
      <line x1="0" y1="-21" x2="0" y2="12" stroke="#000000" stroke-width="2" />
      <!-- Brazos -->
      <line x1="-22" y1="-10" x2="22" y2="-10" stroke="#000000" stroke-width="2" />
      <!-- Pierna Izquierda -->
      <line x1="0" y1="12" x2="-18" y2="40" stroke="#000000" stroke-width="2" />
      <!-- Pierna Derecha -->
      <line x1="0" y1="12" x2="18" y2="40" stroke="#000000" stroke-width="2" />
      <!-- Etiqueta de Nombre -->
      <text x="0" y="60" font-family="'Segoe UI', Arial, sans-serif" font-size="12" font-weight="800" fill="#000000" text-anchor="middle">${label}</text>
      ${roleText ? `<text x="0" y="74" font-family="'Segoe UI', Arial, sans-serif" font-size="10" font-weight="500" fill="#475569" text-anchor="middle">${roleText}</text>` : ''}
    </g>
  `;
}

// Helper para dibujar una elipse de caso de uso
function drawUseCase(cx, cy, rx, ry, title, subtitle = '') {
  return `
    <g>
      <ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#FFFFFF" stroke="#000000" stroke-width="1.8" />
      <text x="${cx}" y="${subtitle ? cy - 6 : cy}" font-family="'Segoe UI', Arial, sans-serif" font-size="11.5" font-weight="700" fill="#000000" text-anchor="middle" dominant-baseline="middle">${title}</text>
      ${subtitle ? `<text x="${cx}" y="${cy + 10}" font-family="'Segoe UI', Arial, sans-serif" font-size="9.5" font-weight="400" fill="#334155" text-anchor="middle" dominant-baseline="middle">${subtitle}</text>` : ''}
    </g>
  `;
}

const svgContent = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${svgWidth} ${svgHeight}" width="${svgWidth}" height="${svgHeight}">
  <defs>
    <!-- Flecha para relaciones <<include>> y <<extend>> -->
    <marker id="arrow-uml" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
      <path d="M 0 1.5 L 8 5 L 0 8.5" fill="none" stroke="#000000" stroke-width="1.5" />
    </marker>
    <marker id="generalize-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="8" markerHeight="8" orient="auto-start-reverse">
      <polygon points="0,1.5 8,5 0,8.5" fill="#FFFFFF" stroke="#000000" stroke-width="1.5" />
    </marker>
  </defs>

  <style>
    .diagram-title { font-family: 'Segoe UI', Arial, sans-serif; font-size: 20px; font-weight: 800; fill: #000000; letter-spacing: 0.5px; }
    .diagram-subtitle { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; font-weight: 500; fill: #334155; }
    .boundary-title { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; font-weight: 800; fill: #000000; letter-spacing: 1px; text-transform: uppercase; }
    
    .assoc-line { fill: none; stroke: #000000; stroke-width: 1.5; }
    .rel-line { fill: none; stroke: #000000; stroke-width: 1.4; stroke-dasharray: 4,4; }
    .rel-tag { font-family: 'Segoe UI', Arial, sans-serif; font-size: 9.5px; font-weight: 700; fill: #000000; text-anchor: middle; background-color: #FFFFFF; }
    .section-label { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; font-weight: 800; fill: #475569; letter-spacing: 0.8px; text-transform: uppercase; }
  </style>

  <!-- Fondo Blanco Técnico -->
  <rect width="${svgWidth}" height="${svgHeight}" fill="#FFFFFF" />

  <!-- Encabezado de la Lámina -->
  <rect x="40" y="25" width="${svgWidth - 80}" height="65" fill="#FFFFFF" stroke="#000000" stroke-width="1.5" />
  <text x="65" y="52" class="diagram-title">DIAGRAMA GENERAL DE CASOS DE USO (UML 2.5)</text>
  <text x="65" y="74" class="diagram-subtitle">Plataforma E-Commerce FusaShop · Especificación Funcional en Blanco y Negro para Tesis de Grado</text>

  <!-- ========================================================================= -->
  <!-- LÍMITE DEL SISTEMA (System Boundary)                                      -->
  <!-- ========================================================================= -->
  <rect x="230" y="110" width="940" height="1100" fill="#FFFFFF" stroke="#000000" stroke-width="2" />
  <text x="250" y="135" class="boundary-title">Sistema FusaShop — Plataforma Transaccional Multivendedor</text>

  <!-- ========================================================================= -->
  <!-- ACTORES A LA IZQUIERDA (CLIENTE VISITANTE Y REGISTRADO)                   -->
  <!-- ========================================================================= -->
  ${drawActor(110, 240, 'Cliente Visitante', '(No autenticado)')}
  ${drawActor(110, 520, 'Cliente Registrado', '(Comprador)')}

  <!-- Generalización: Cliente Registrado hereda de Cliente Visitante -->
  <line x1="110" y1="470" x2="110" y2="330" class="assoc-line" marker-end="url(#generalize-arrow)" />

  <!-- ========================================================================= -->
  <!-- ACTORES A LA DERECHA (COMERCIANTE, ANALISTA Y SISTEMAS EXTERNOS)          -->
  <!-- ========================================================================= -->
  ${drawActor(1320, 270, 'Comerciante', '(Vendedor verificado)')}
  ${drawActor(1320, 680, 'Analista', '(Administrador)')}
  ${drawActor(1320, 1030, 'Sistemas Externos', '(Wompi, Envia, Factus)')}

  <!-- ========================================================================= -->
  <!-- MÓDULO 1: EXPLORACIÓN, CATÁLOGO Y ASISTENCIA (Superior)                   -->
  <!-- ========================================================================= -->
  <text x="250" y="170" class="section-label">1. Módulo Comprador: Catálogo, Búsqueda y Carrito</text>

  <!-- CU 01: Navegar catálogo -->
  ${drawUseCase(420, 205, 100, 26, 'Navegar por Catálogo', 'Vitrinas y rubros Sumapaz')}
  <!-- CU 02: Búsqueda Omnibar -->
  ${drawUseCase(420, 275, 105, 26, 'Buscar Productos/Servicios', 'Predictivo en tiempo real')}
  <!-- CU 03: Consultar FusaBot -->
  ${drawUseCase(720, 205, 105, 26, 'Consultar Asistente FusaBot', 'Recomendación semántica')}
  <!-- CU 04: Ver detalle producto -->
  ${drawUseCase(420, 345, 100, 26, 'Ver Detalle de Producto', 'Fotos, reseñas y ficha técnica')}
  <!-- CU 05: Gestionar Carrito -->
  ${drawUseCase(420, 415, 95, 26, 'Gestionar Carrito', 'Cantidades y verificación')}

  <!-- Asociaciones Cliente Visitante -->
  <line x1="140" y1="210" x2="320" y2="205" class="assoc-line" />
  <line x1="140" y1="235" x2="315" y2="275" class="assoc-line" />
  <line x1="140" y1="260" x2="320" y2="345" class="assoc-line" />
  <line x1="140" y1="285" x2="325" y2="415" class="assoc-line" />
  <line x1="140" y1="225" x2="615" y2="205" class="assoc-line" />

  <!-- ========================================================================= -->
  <!-- MÓDULO 2: AUTENTICACIÓN Y CUENTA                                          -->
  <!-- ========================================================================= -->
  <!-- CU 06: Registrarse -->
  ${drawUseCase(420, 485, 90, 25, 'Registrarse', 'Creación de cuenta')}
  <!-- CU 07: Iniciar Sesión -->
  ${drawUseCase(420, 555, 95, 25, 'Iniciar Sesión', 'Google OAuth o Email 2FA')}

  <line x1="140" y1="300" x2="330" y2="485" class="assoc-line" />
  <line x1="140" y1="520" x2="325" y2="555" class="assoc-line" />

  <!-- CU 06 include CU 07 -->
  <line x1="420" y1="510" x2="420" y2="530" class="rel-line" marker-end="url(#arrow-uml)" />
  <text x="455" y="523" class="rel-tag">&lt;&lt;include&gt;&gt;</text>

  <!-- ========================================================================= -->
  <!-- MÓDULO 3: CHECKOUT, COMPRA Y TRANSACCIÓN (Centro)                         -->
  <!-- ========================================================================= -->
  <text x="250" y="615" class="section-label">2. Módulo Transaccional: Checkout y Liquidación</text>

  <!-- CU 08: Realizar Compra -->
  ${drawUseCase(420, 660, 95, 26, 'Realizar Compra', 'Checkout multivendedor')}
  <line x1="140" y1="540" x2="325" y2="660" class="assoc-line" />

  <!-- CU 09: Cotizar Flete Envia -->
  ${drawUseCase(720, 600, 100, 25, 'Cotizar Flete Logístico', 'API Envia.com')}
  <!-- CU 10: Procesar Pago Pasarela -->
  ${drawUseCase(720, 665, 105, 26, 'Procesar Pago en Pasarela', 'Wompi PSE / Tarjetas / PayU')}
  <!-- CU 11: Redimir Puntos/Cupones -->
  ${drawUseCase(720, 730, 105, 25, 'Redimir Puntos FusaCoins', 'O Cupón Comercial')}

  <!-- Relaciones de Realizar Compra -->
  <!-- CU 08 include CU 09 -->
  <line x1="495" y1="645" x2="625" y2="615" class="rel-line" marker-end="url(#arrow-uml)" />
  <text x="560" y="622" class="rel-tag">&lt;&lt;include&gt;&gt;</text>

  <!-- CU 08 include CU 10 -->
  <line x1="515" y1="662" x2="615" y2="665" class="rel-line" marker-end="url(#arrow-uml)" />
  <text x="565" y="658" class="rel-tag">&lt;&lt;include&gt;&gt;</text>

  <!-- CU 08 extend CU 11 -->
  <line x1="620" y1="718" x2="505" y2="678" class="rel-line" marker-end="url(#arrow-uml)" />
  <text x="565" y="705" class="rel-tag">&lt;&lt;extend&gt;&gt;</text>

  <!-- Conexión Pasarela con Sistemas Externos -->
  <line x1="825" y1="665" x2="1280" y2="1010" class="assoc-line" />
  <!-- Conexión Envia con Sistemas Externos -->
  <line x1="820" y1="600" x2="1280" y2="1000" class="assoc-line" />

  <!-- ========================================================================= -->
  <!-- MÓDULO 4: POSTVENTA Y SERVICIOS (Inferior Izquierda)                       -->
  <!-- ========================================================================= -->
  <text x="250" y="785" class="section-label">3. Módulo Postventa, Reservas y Fidelización</text>

  <!-- CU 12: Consultar Pedidos -->
  ${drawUseCase(420, 830, 95, 25, 'Consultar Pedidos', 'Guías y estado')}
  <line x1="140" y1="565" x2="330" y2="825" class="assoc-line" />

  <!-- CU 13: Solicitar Reserva -->
  ${drawUseCase(420, 900, 95, 25, 'Solicitar Reserva', 'Turismo y gastronomía')}
  <line x1="140" y1="585" x2="330" y2="895" class="assoc-line" />

  <!-- CU 14: Calificar Producto -->
  ${drawUseCase(420, 970, 95, 25, 'Calificar Producto', 'Reseñas con análisis NLP')}
  <line x1="140" y1="605" x2="330" y2="965" class="assoc-line" />

  <!-- CU 15: Radicar PQRS -->
  ${drawUseCase(420, 1040, 95, 25, 'Radicar PQRS', 'Ley 1480 / 15 días')}
  <line x1="140" y1="625" x2="330" y2="1035" class="assoc-line" />

  <!-- ========================================================================= -->
  <!-- MÓDULO 5: GESTIÓN COMERCIAL (COMERCIANTE / DERECHA)                       -->
  <!-- ========================================================================= -->
  <text x="750" y="170" class="section-label">4. Módulo Comerciante y Venta</text>

  <!-- CU 16: Gestionar Catálogo -->
  ${drawUseCase(980, 205, 105, 26, 'Gestionar Catálogo', 'Crear y editar productos')}
  <!-- CU 17: Gestionar KYC y Perfil -->
  ${drawUseCase(980, 275, 105, 26, 'Gestionar Perfil Comercial', 'Documentación KYC y banco')}
  <!-- CU 18: Procesar Pedidos y Guías -->
  ${drawUseCase(980, 345, 110, 26, 'Gestionar Envíos y Guías', 'Rótulos Envia.com PDF')}
  <!-- CU 19: Gestionar Reservas -->
  ${drawUseCase(980, 415, 100, 25, 'Administrar Reservas', 'Confirmar o cancelar citas')}
  <!-- CU 20: Consultar Billetera -->
  ${drawUseCase(980, 485, 105, 26, 'Consultar Billetera', 'Liquidaciones y dispersión')}
  <!-- CU 21: Responder Reseñas -->
  ${drawUseCase(980, 555, 100, 25, 'Responder Reseñas', 'Atención directa a clientes')}

  <!-- Asociaciones Comerciante -->
  <line x1="1280" y1="240" x2="1085" y2="205" class="assoc-line" />
  <line x1="1280" y1="260" x2="1085" y2="275" class="assoc-line" />
  <line x1="1280" y1="280" x2="1090" y2="345" class="assoc-line" />
  <line x1="1280" y1="300" x2="1080" y2="415" class="assoc-line" />
  <line x1="1280" y1="320" x2="1085" y2="485" class="assoc-line" />
  <line x1="1280" y1="340" x2="1080" y2="555" class="assoc-line" />

  <!-- ========================================================================= -->
  <!-- MÓDULO 6: ADMINISTRACIÓN Y GOBERNANZA (ANALISTA / DERECHA)                -->
  <!-- ========================================================================= -->
  <text x="750" y="740" class="section-label">5. Módulo Gobernanza y Analítica Territorial</text>

  <!-- CU 22: Aprobar KYC Comercios -->
  ${drawUseCase(980, 780, 105, 26, 'Verificar KYC Comercios', 'Aprobación documental')}
  <!-- CU 23: Diagnóstico de Ventas -->
  ${drawUseCase(980, 850, 110, 26, 'Diagnosticar Ventas', 'Sistema experto 12 causas')}
  <!-- CU 24: Mapa de Calor Territorial -->
  ${drawUseCase(980, 920, 110, 26, 'Consultar Mapa Territorial', 'Geoanalítica Comunas')}
  <!-- CU 25: Gestionar PQRS -->
  ${drawUseCase(980, 990, 100, 25, 'Tramitar PQRS', 'Resolución legal formal')}
  <!-- CU 26: Auditar Integraciones -->
  ${drawUseCase(980, 1060, 105, 26, 'Auditar Integraciones', 'Logs de Factus/Wompi')}

  <!-- Asociaciones Analista -->
  <line x1="1280" y1="650" x2="1085" y2="780" class="assoc-line" />
  <line x1="1280" y1="670" x2="1090" y2="850" class="assoc-line" />
  <line x1="1280" y1="690" x2="1090" y2="920" class="assoc-line" />
  <line x1="1280" y1="710" x2="1080" y2="990" class="assoc-line" />
  <line x1="1280" y1="730" x2="1085" y2="1060" class="assoc-line" />

  <!-- Leyenda Técnica Inferior Izquierda -->
  <rect x="40" y="1120" width="180" height="90" fill="#FFFFFF" stroke="#000000" stroke-width="1.2" />
  <text x="50" y="1138" font-family="'Segoe UI', Arial, sans-serif" font-size="10" font-weight="800" fill="#000000">CONVENCIONES UML 2.5:</text>
  <text x="50" y="1156" font-family="'Segoe UI', Arial, sans-serif" font-size="9" fill="#000000">— Línea continua: Asociación actor-CU</text>
  <text x="50" y="1172" font-family="'Segoe UI', Arial, sans-serif" font-size="9" fill="#000000">--&gt; Línea discontinua: Inclusión / Extensión</text>
  <text x="50" y="1188" font-family="'Segoe UI', Arial, sans-serif" font-size="9" fill="#000000">--▷ Flecha hueca: Generalización / Herencia</text>
  <text x="50" y="1202" font-family="'Segoe UI', Arial, sans-serif" font-size="9" fill="#000000">⬭ Elipse: Caso de Uso del Sistema</text>

</svg>`;

const outputPath = path.resolve('docs/diagrama-casos-de-uso-fusashop.svg');
fs.writeFileSync(outputPath, svgContent, 'utf-8');
console.log(`SVG de Casos de Uso generado exitosamente en: ${outputPath}`);
