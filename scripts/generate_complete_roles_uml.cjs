/**
 * Generador de Diagramas de Casos de Uso COMPLETOS (Todas las funciones por rol)
 * Estilo EXACTO a la referencia del usuario:
 * - Muñequito de palitos UML
 * - Blanco y negro puro
 * - Caja rectangular con nombre del sistema
 * - Óvalos alineados con todas las acciones
 * - Líneas de asociación rectas sin flechas
 */

const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

function drawStickFigure(x, y, name, subname = '') {
  return `
    <g class="actor">
      <!-- Cabeza -->
      <circle cx="${x}" cy="${y - 45}" r="22" fill="#FFFFFF" stroke="#000000" stroke-width="3" />
      <!-- Torso -->
      <line x1="${x}" y1="${y - 23}" x2="${x}" y2="${y + 42}" stroke="#000000" stroke-width="3" stroke-linecap="round" />
      <!-- Brazos -->
      <line x1="${x - 32}" y1="${y}" x2="${x + 32}" y2="${y}" stroke="#000000" stroke-width="3" stroke-linecap="round" />
      <!-- Pierna izquierda -->
      <line x1="${x}" y1="${y + 42}" x2="${x - 26}" y2="${y + 98}" stroke="#000000" stroke-width="3" stroke-linecap="round" />
      <!-- Pierna derecha -->
      <line x1="${x}" y1="${y + 42}" x2="${x + 26}" y2="${y + 98}" stroke="#000000" stroke-width="3" stroke-linecap="round" />
      <!-- Nombre del Actor -->
      <text x="${x}" y="${y + 124}" font-family="Arial, Helvetica, sans-serif" font-size="17" font-weight="bold" fill="#000000" text-anchor="middle">${name}</text>
      ${subname ? `<text x="${x}" y="${y + 144}" font-family="Arial, Helvetica, sans-serif" font-size="13" fill="#333333" text-anchor="middle">${subname}</text>` : ''}
    </g>
  `;
}

function drawOval(cx, cy, text) {
  const rx = 155;
  const ry = 28;
  return `
    <g class="use-case">
      <ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#FFFFFF" stroke="#000000" stroke-width="2.6" />
      <text x="${cx}" y="${cy + 5}" font-family="Arial, Helvetica, sans-serif" font-size="14.5" font-weight="normal" fill="#000000" text-anchor="middle">${text}</text>
    </g>
  `;
}

function generateFullDiagram(config) {
  const count = config.useCases.length;
  const spacing = 78;
  const startY = 110;
  const boxH = count * spacing + 50;
  const H = boxH + 90;
  const W = config.rightActors ? 1120 : 920;

  const boxX = 250;
  const boxY = 40;
  const boxW = 460;
  const ovalCx = boxX + boxW / 2; // 480
  const ovalRx = 155;

  const leftActorX = 115;
  const leftActorY = Math.round(boxY + boxH / 2);

  // Generar óvalos
  let ovalsSvg = '';
  const ovalYPositions = [];
  config.useCases.forEach((ucText, i) => {
    const y = startY + i * spacing;
    ovalYPositions.push(y);
    ovalsSvg += drawOval(ovalCx, y, ucText);
  });

  // Líneas desde el actor principal (izquierdo) hacia TODOS los casos de uso
  let linesSvg = '';
  ovalYPositions.forEach((targetY, i) => {
    const targetX = ovalCx - ovalRx; // Borde izquierdo del óvalo
    // Distribución suave a lo largo del torso para abanico limpio
    const originY = leftActorY - 20 + (i / (count - 1)) * 55;
    linesSvg += `<line x1="${leftActorX + 32}" y1="${originY}" x2="${targetX}" y2="${targetY}" stroke="#000000" stroke-width="2.2" stroke-linecap="round" />\n`;
  });

  // Actor principal izquierdo
  const leftActorSvg = drawStickFigure(leftActorX, leftActorY, config.leftActorName, config.leftActorSub);

  // Actores secundarios derechos (opcionales)
  let rightActorsSvg = '';
  if (config.rightActors) {
    config.rightActors.forEach(ra => {
      rightActorsSvg += drawStickFigure(ra.x, ra.y, ra.name, ra.sub);
      ra.targets.forEach(targetIdx => {
        const targetY = ovalYPositions[targetIdx];
        const targetX = ovalCx + ovalRx; // Borde derecho del óvalo
        linesSvg += `<line x1="${ra.x - 32}" y1="${ra.y}" x2="${targetX}" y2="${targetY}" stroke="#000000" stroke-width="2.2" stroke-linecap="round" />\n`;
      });
    });
  }

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
  <!-- Fondo blanco -->
  <rect width="${W}" height="${H}" fill="#FFFFFF" />

  <!-- Caja del Sistema (Límite) -->
  <rect x="${boxX}" y="${boxY}" width="${boxW}" height="${boxH}" fill="#FFFFFF" stroke="#000000" stroke-width="3.5" />

  <!-- Nombre del Sistema arriba a la izquierda -->
  <text x="${boxX + 16}" y="${boxY + 30}" font-family="Arial, Helvetica, sans-serif" font-size="20" font-weight="bold" fill="#000000">${config.title}</text>

  <!-- Líneas de Conexión -->
  ${linesSvg}

  <!-- Óvalos con Todas las Funciones -->
  ${ovalsSvg}

  <!-- Actor Principal -->
  ${leftActorSvg}

  <!-- Actores Secundarios -->
  ${rightActorsSvg}
</svg>`;
}

async function main() {
  const docsDir = path.resolve('docs');
  const brainDir = path.resolve('C:/Users/HUAWEI/.gemini/antigravity/brain/f8818232-1594-4eb4-9ccd-acf39c8a9671');

  // =========================================================================
  // 1. CONSUMIDOR (Todas las funciones)
  // =========================================================================
  const consumerConfig = {
    title: 'FusaShop — Consumidor (Completo)',
    leftActorName: 'Consumidor',
    leftActorSub: '(Comprador)',
    useCases: [
      'Registrarse e iniciar sesión',
      'Gestionar perfil y direcciones',
      'Buscar y filtrar productos',
      'Administrar carrito de compras',
      'Realizar compra (Checkout)',
      'Cotizar tarifa de envío',
      'Pagar la compra en línea',
      'Consultar pedidos y facturas',
      'Rastrear despacho del pedido',
      'Solicitar reserva de servicios',
      'Calificar y reseñar productos',
      'Chatear con el comerciante',
      'Radicar y consultar PQRS'
    ],
    rightActors: [
      {
        name: 'Pasarela Wompi',
        sub: '(Pagos)',
        x: 970,
        y: 575,
        targets: [6] // Pagar la compra en línea
      },
      {
        name: 'Envia.com',
        sub: '(Logística)',
        x: 970,
        y: 735,
        targets: [5, 8] // Cotizar tarifa y Rastrear despacho
      }
    ]
  };

  // =========================================================================
  // 2. COMERCIANTE (Todas las funciones)
  // =========================================================================
  const merchantConfig = {
    title: 'FusaShop — Comerciante (Completo)',
    leftActorName: 'Comerciante',
    leftActorSub: '(Vendedor)',
    useCases: [
      'Registrar tienda y enviar KYC (RUT)',
      'Configurar perfil de la tienda',
      'Publicar y editar productos',
      'Administrar fotos y variantes',
      'Controlar existencias e inventario',
      'Gestionar pedidos recibidos',
      'Generar e imprimir guías de envío',
      'Emitir facturas electrónicas',
      'Gestionar reservas de servicios',
      'Consultar balance y liquidaciones',
      'Atender mensajes y chat de clientes',
      'Responder reseñas de compradores',
      'Solicitar banners de publicidad'
    ],
    rightActors: [
      {
        name: 'Envia.com',
        sub: '(Guías de envío)',
        x: 970,
        y: 575,
        targets: [6] // Generar guía de envío
      },
      {
        name: 'Factus / DIAN',
        sub: '(Factura Electrónica)',
        x: 970,
        y: 655,
        targets: [7] // Emitir facturas electrónicas
      }
    ]
  };

  // =========================================================================
  // 3. ADMINISTRADOR (Todas las funciones)
  // =========================================================================
  const adminConfig = {
    title: 'FusaShop — Administrador (Completo)',
    leftActorName: 'Administrador',
    leftActorSub: '(Analista / Gobierno)',
    useCases: [
      'Gestionar cuentas y roles de usuarios',
      'Bloquear o reactivar cuentas',
      'Verificar y validar documentos KYC',
      'Aprobar o rechazar tiendas (RUT)',
      'Administrar categorías y sectores',
      'Supervisar ventas y transacciones',
      'Liquidar comisiones a comercios',
      'Monitorear estado de integraciones',
      'Tramitar y resolver casos de PQRS',
      'Aprobar y publicar campañas / banners',
      'Consultar analítica y reportes de ventas',
      'Auditar trazabilidad y bitácora del sistema'
    ],
    rightActors: [
      {
        name: 'DIAN (RUT)',
        sub: '(Verificación)',
        x: 970,
        y: 290,
        targets: [2, 3] // Verificar KYC y Aprobar tiendas
      },
      {
        name: 'Pasarela Wompi',
        sub: '(Dispersión)',
        x: 970,
        y: 520,
        targets: [5, 6] // Supervisar ventas y Liquidar comisiones
      }
    ]
  };

  const diagrams = [
    { config: consumerConfig, name: 'diagrama-cu-consumidor-completo' },
    { config: merchantConfig, name: 'diagrama-cu-comerciante-completo' },
    { config: adminConfig, name: 'diagrama-cu-administrador-completo' }
  ];

  for (const d of diagrams) {
    const svgCode = generateFullDiagram(d.config);
    const svgPath = path.join(docsDir, `${d.name}.svg`);
    const pngPath = path.join(docsDir, `${d.name}.png`);
    const brainPngPath = path.join(brainDir, `${d.name}.png`);

    fs.writeFileSync(svgPath, svgCode, 'utf8');
    await sharp(Buffer.from(svgCode)).png().toFile(pngPath);
    await sharp(Buffer.from(svgCode)).png().toFile(brainPngPath);

    console.log(`[OK] Diagrama completo generado: ${d.name} (.svg y .png)`);
  }

  // Generar Visor HTML Completo
  const htmlPath = path.join(docsDir, 'diagramas-casos-de-uso-completos-bn.html');
  const htmlContent = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Diagramas de Casos de Uso Completos — FusaShop</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background: #F3F4F6; margin: 0; padding: 30px; text-align: center; }
    h1 { margin-bottom: 5px; color: #111; }
    p.lead { color: #555; margin-bottom: 35px; font-size: 15px; }
    .grid { display: flex; flex-direction: column; gap: 40px; max-width: 1050px; margin: 0 auto; }
    .card { background: #FFFFFF; border: 1px solid #CCC; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.06); }
    .card h2 { margin-top: 0; text-align: left; border-bottom: 2px solid #000; padding-bottom: 10px; font-size: 19px; }
    .img-box { margin: 15px 0; border: 1px solid #000; background: #FFF; padding: 10px; }
    .img-box img { max-width: 100%; height: auto; display: block; margin: 0 auto; }
    .actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 15px; }
    .btn { background: #000; color: #FFF; padding: 9px 16px; text-decoration: none; font-size: 13px; font-weight: bold; border-radius: 4px; }
    .btn:hover { background: #333; }
  </style>
</head>
<body>
  <h1>FusaShop · Diagramas de Casos de Uso Completos</h1>
  <p class="lead">Estilo formal con muñequitos de palitos, en blanco y negro, con todas las funciones del sistema por rol</p>

  <div class="grid">
    <!-- 1. Consumidor -->
    <div class="card">
      <h2>1. Rol Consumidor (Todas las 13 funciones de compra y postventa)</h2>
      <div class="img-box">
        <img src="diagrama-cu-consumidor-completo.png" alt="Consumidor Completo" />
      </div>
      <div class="actions">
        <a class="btn" href="diagrama-cu-consumidor-completo.png" download>Descargar PNG (Alta Resolución)</a>
        <a class="btn" href="diagrama-cu-consumidor-completo.svg" download>Descargar SVG (Vectorial)</a>
      </div>
    </div>

    <!-- 2. Comerciante -->
    <div class="card">
      <h2>2. Rol Comerciante (Todas las 13 funciones de panel de tienda y ventas)</h2>
      <div class="img-box">
        <img src="diagrama-cu-comerciante-completo.png" alt="Comerciante Completo" />
      </div>
      <div class="actions">
        <a class="btn" href="diagrama-cu-comerciante-completo.png" download>Descargar PNG (Alta Resolución)</a>
        <a class="btn" href="diagrama-cu-comerciante-completo.svg" download>Descargar SVG (Vectorial)</a>
      </div>
    </div>

    <!-- 3. Administrador -->
    <div class="card">
      <h2>3. Rol Administrador (Todas las 12 funciones de gobierno y supervisión)</h2>
      <div class="img-box">
        <img src="diagrama-cu-administrador-completo.png" alt="Administrador Completo" />
      </div>
      <div class="actions">
        <a class="btn" href="diagrama-cu-administrador-completo.png" download>Descargar PNG (Alta Resolución)</a>
        <a class="btn" href="diagrama-cu-administrador-completo.svg" download>Descargar SVG (Vectorial)</a>
      </div>
    </div>
  </div>
</body>
</html>`;

  fs.writeFileSync(htmlPath, htmlContent, 'utf8');
  console.log(`[OK] Visor unificado completo guardado en: ${htmlPath}`);
}

main().catch(console.error);
