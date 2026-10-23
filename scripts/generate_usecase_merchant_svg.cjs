/**
 * Script para generar los dos diagramas de casos de uso completos con muñequitos:
 * 1. Cliente / Comprador (Topología de referencia)
 * 2. Comerciante y Analista Administrador
 */

const fs = require('fs');
const path = require('path');

function drawActor(x, y, label, role = '') {
  return `
    <g class="actor-group" transform="translate(${x}, ${y})">
      <!-- Cabeza -->
      <circle cx="0" cy="-40" r="16" fill="#FFFFFF" stroke="#000000" stroke-width="2.2" />
      <!-- Tronco -->
      <line x1="0" y1="-24" x2="0" y2="15" stroke="#000000" stroke-width="2.2" />
      <!-- Brazos -->
      <line x1="-26" y1="-10" x2="26" y2="-10" stroke="#000000" stroke-width="2.2" />
      <!-- Piernas -->
      <line x1="0" y1="15" x2="-20" y2="48" stroke="#000000" stroke-width="2.2" />
      <line x1="0" y1="15" x2="20" y2="48" stroke="#000000" stroke-width="2.2" />
      <!-- Texto -->
      <text x="0" y="70" font-family="'Segoe UI', Arial, sans-serif" font-size="12.5" font-weight="800" fill="#000000" text-anchor="middle">${label}</text>
      ${role ? `<text x="0" y="86" font-family="'Segoe UI', Arial, sans-serif" font-size="10" font-weight="500" fill="#475569" text-anchor="middle">${role}</text>` : ''}
    </g>
  `;
}

function drawUseCase(cx, cy, rx, ry, text1, text2 = '') {
  return `
    <g class="usecase-node">
      <ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#FFFFFF" stroke="#000000" stroke-width="1.8" />
      <text x="${cx}" y="${text2 ? cy - 6 : cy}" font-family="'Segoe UI', Arial, sans-serif" font-size="11.5" font-weight="700" fill="#000000" text-anchor="middle" dominant-baseline="middle">${text1}</text>
      ${text2 ? `<text x="${cx}" y="${cy + 10}" font-family="'Segoe UI', Arial, sans-serif" font-size="9.5" font-weight="400" fill="#334155" text-anchor="middle" dominant-baseline="middle">${text2}</text>` : ''}
    </g>
  `;
}

// Diagrama Comerciante & Analista
const width2 = 980;
const height2 = 1100;

const svgMerchantAdminContent = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width2} ${height2}" width="${width2}" height="${height2}">
  <defs>
    <marker id="arrow-open2" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
      <path d="M 0 1.5 L 8 5 L 0 8.5" fill="none" stroke="#000000" stroke-width="1.6" />
    </marker>
  </defs>

  <style>
    .title-main { font-family: 'Segoe UI', Arial, sans-serif; font-size: 18px; font-weight: 800; fill: #000000; letter-spacing: 0.8px; text-anchor: middle; }
    .title-sub { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11.5px; font-weight: 500; fill: #475569; text-anchor: middle; }
    .assoc-line { fill: none; stroke: #000000; stroke-width: 1.6; }
    .dep-line { fill: none; stroke: #000000; stroke-width: 1.4; stroke-dasharray: 5,4; }
    .tag-dep { font-family: 'Segoe UI', Arial, sans-serif; font-size: 9.5px; font-weight: 700; fill: #000000; text-anchor: middle; }
  </style>

  <rect width="${width2}" height="${height2}" fill="#FFFFFF" />

  <text x="490" y="45" class="title-main">DIAGRAMA CASOS DE USO — COMERCIANTE Y ADMINISTRADOR</text>
  <text x="490" y="68" class="title-sub">Gestión Multivendedor, Liquidaciones, Logística y Gobernanza Territorial</text>

  <!-- MUÑEQUITO IZQUIERDA: COMERCIANTE -->
  ${drawActor(140, 320, 'Comerciante', '(Vendedor verificado)')}

  <!-- MUÑEQUITO DERECHA: ANALISTA / ADMIN -->
  ${drawActor(840, 320, 'Analista Admin', '(Gobernanza)')}

  <!-- CASOS DE USO CENTRALES -->
  ${drawUseCase(490, 130, 120, 26, 'Gestionar Perfil y KYC', 'Documentación legal y cuenta')}
  ${drawUseCase(490, 210, 120, 26, 'Publicar Productos/Servicios', 'Precios, stock y variantes')}
  ${drawUseCase(490, 290, 120, 26, 'Administrar Envíos', 'Descarga de guías Envia.com')}
  ${drawUseCase(490, 370, 120, 26, 'Consultar Billetera', 'Liquidación de comisiones')}
  ${drawUseCase(490, 450, 120, 26, 'Atender Reservas', 'Turismo y gastronomía')}
  ${drawUseCase(490, 530, 120, 26, 'Responder Reseñas', 'Atención directa postventa')}
  ${drawUseCase(490, 620, 120, 26, 'Verificar KYC Comercios', 'Aprobación de habilitación')}
  ${drawUseCase(490, 700, 125, 26, 'Diagnosticar Ventas', 'Sistema experto 12 causas')}
  ${drawUseCase(490, 780, 125, 26, 'Mapa de Calor Territorial', 'Geoanalítica Comunas')}
  ${drawUseCase(490, 860, 120, 26, 'Tramitar y Resolver PQRS', 'Resolución legal formal')}
  ${drawUseCase(490, 940, 120, 26, 'Auditar Logs de Integración', 'Monitoreo Factus/Wompi')}

  <!-- ASOCIACIONES COMERCIANTE (IZQUIERDA) -->
  <line x1="170" y1="280" x2="370" y2="130" class="assoc-line" />
  <line x1="170" y1="295" x2="370" y2="210" class="assoc-line" />
  <line x1="170" y1="310" x2="370" y2="290" class="assoc-line" />
  <line x1="170" y1="325" x2="370" y2="370" class="assoc-line" />
  <line x1="170" y1="340" x2="370" y2="450" class="assoc-line" />
  <line x1="170" y1="355" x2="370" y2="530" class="assoc-line" />

  <!-- ASOCIACIONES ANALISTA (DERECHA) -->
  <line x1="810" y1="320" x2="610" y2="620" class="assoc-line" />
  <line x1="810" y1="335" x2="615" y2="700" class="assoc-line" />
  <line x1="810" y1="350" x2="615" y2="780" class="assoc-line" />
  <line x1="810" y1="365" x2="610" y2="860" class="assoc-line" />
  <line x1="810" y1="380" x2="610" y2="940" class="assoc-line" />

  <!-- Leyenda Técnica -->
  <rect x="40" y="1000" width="900" height="65" fill="#FFFFFF" stroke="#000000" stroke-width="1.2" />
  <text x="60" y="1025" font-family="'Segoe UI', Arial, sans-serif" font-size="11" font-weight="800" fill="#000000">CONVENCIONES UML 2.5 (ACTORES DE GESTIÓN):</text>
  <text x="60" y="1045" font-family="'Segoe UI', Arial, sans-serif" font-size="10" fill="#000000">• Muñequitos: Actor Comerciante (Vendedor) y Actor Analista (Administrador de Plataforma).</text>
</svg>`;

fs.writeFileSync(path.resolve('docs/diagrama-casos-de-uso-comercio-munecos.svg'), svgMerchantAdminContent, 'utf-8');
console.log('SVG Comerciante & Analista generado exitosamente.');
