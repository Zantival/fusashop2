const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

function drawStickFigure(x, y, name) {
  return `
    <g class="actor">
      <!-- Cabeza -->
      <circle cx="${x}" cy="${y - 45}" r="23" fill="#FFFFFF" stroke="#000000" stroke-width="3" />
      <!-- Torso -->
      <line x1="${x}" y1="${y - 22}" x2="${x}" y2="${y + 42}" stroke="#000000" stroke-width="3" stroke-linecap="round" />
      <!-- Brazos -->
      <line x1="${x - 32}" y1="${y}" x2="${x + 32}" y2="${y}" stroke="#000000" stroke-width="3" stroke-linecap="round" />
      <!-- Pierna izquierda -->
      <line x1="${x}" y1="${y + 42}" x2="${x - 28}" y2="${y + 98}" stroke="#000000" stroke-width="3" stroke-linecap="round" />
      <!-- Pierna derecha -->
      <line x1="${x}" y1="${y + 42}" x2="${x + 28}" y2="${y + 98}" stroke="#000000" stroke-width="3" stroke-linecap="round" />
      <!-- Nombre del Actor -->
      <text x="${x}" y="${y + 124}" font-family="Arial, Helvetica, sans-serif" font-size="18" fill="#000000" text-anchor="middle">${name}</text>
    </g>
  `;
}

function drawOval(cx, cy, textLines) {
  const rx = 115;
  const ry = 36;
  let textSvg = '';
  if (textLines.length === 1) {
    textSvg = `<text x="${cx}" y="${cy + 6}" font-family="Arial, Helvetica, sans-serif" font-size="18" fill="#000000" text-anchor="middle">${textLines[0]}</text>`;
  } else {
    textSvg = `
      <text x="${cx}" y="${cy - 4}" font-family="Arial, Helvetica, sans-serif" font-size="17" fill="#000000" text-anchor="middle">${textLines[0]}</text>
      <text x="${cx}" y="${cy + 17}" font-family="Arial, Helvetica, sans-serif" font-size="17" fill="#000000" text-anchor="middle">${textLines[1]}</text>
    `;
  }

  return `
    <g class="use-case">
      <ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#FFFFFF" stroke="#000000" stroke-width="3" />
      ${textSvg}
    </g>
  `;
}

function generateDiagram(config) {
  const W = 820;
  const H = 580;

  const boxX = 230;
  const boxY = 40;
  const boxW = 340;
  const boxH = 500;
  const ovalCx = boxX + boxW / 2; // 400

  const ovalY = [125, 225, 325, 425];

  let ovalsSvg = '';
  config.useCases.forEach((uc, i) => {
    ovalsSvg += drawOval(ovalCx, ovalY[i], uc);
  });

  const leftActorX = 115;
  const leftActorY = 250;
  const leftActorSvg = drawStickFigure(leftActorX, leftActorY, config.leftActorName);

  let rightActorSvg = '';
  const rightActorX = 705;
  const rightActorY = 260;
  if (config.rightActorName) {
    rightActorSvg = drawStickFigure(rightActorX, rightActorY, config.rightActorName);
  }

  let linesSvg = '';
  config.leftConnections.forEach(targetIdx => {
    const targetY = ovalY[targetIdx];
    const targetX = ovalCx - 115;
    let originY = leftActorY;
    if (targetIdx === 0) originY = leftActorY - 15;
    if (targetIdx === 1) originY = leftActorY;
    if (targetIdx === 2) originY = leftActorY + 25;
    if (targetIdx === 3) originY = leftActorY + 45;

    linesSvg += `<line x1="${leftActorX + 32}" y1="${originY}" x2="${targetX}" y2="${targetY}" stroke="#000000" stroke-width="3" stroke-linecap="round" />\n`;
  });

  if (config.rightConnections && config.rightActorName) {
    config.rightConnections.forEach(targetIdx => {
      const targetY = ovalY[targetIdx];
      const targetX = ovalCx + 115;
      let originY = rightActorY;
      if (targetIdx === 3) originY = rightActorY + 25;
      linesSvg += `<line x1="${rightActorX - 32}" y1="${originY}" x2="${targetX}" y2="${targetY}" stroke="#000000" stroke-width="3" stroke-linecap="round" />\n`;
    });
  }

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
  <rect width="${W}" height="${H}" fill="#FFFFFF" />
  <rect x="${boxX}" y="${boxY}" width="${boxW}" height="${boxH}" fill="#FFFFFF" stroke="#000000" stroke-width="3.5" />
  <text x="${boxX + 12}" y="${boxY + 28}" font-family="Arial, Helvetica, sans-serif" font-size="19" fill="#000000">${config.title}</text>
  ${linesSvg}
  ${ovalsSvg}
  ${leftActorSvg}
  ${rightActorSvg}
</svg>`;
}

async function main() {
  const docsDir = path.resolve('docs');
  const brainDir = path.resolve('C:/Users/HUAWEI/.gemini/antigravity/brain/f8818232-1594-4eb4-9ccd-acf39c8a9671');

  // Variantes con 2 actores (Estilo idéntico a la imagen con Crítico y Chef)
  const dualConfigs = [
    {
      name: 'diagrama-consumidor-simple',
      title: 'FusaShop (Consumidor)',
      leftActorName: 'Consumidor',
      leftConnections: [0, 1, 2],
      rightActorName: 'Repartidor',
      rightConnections: [3],
      useCases: [
        ['Buscar productos'],
        ['Pagar la compra'],
        ['Beber vino / Café'],
        ['Entregar el pedido']
      ]
    },
    {
      name: 'diagrama-comerciante-simple',
      title: 'FusaShop (Comerciante)',
      leftActorName: 'Comerciante',
      leftConnections: [0, 1, 2],
      rightActorName: 'Envia.com',
      rightConnections: [3],
      useCases: [
        ['Publicar productos'],
        ['Controlar inventario'],
        ['Gestionar pedidos'],
        ['Generar la guía']
      ]
    },
    {
      name: 'diagrama-administrador-simple',
      title: 'FusaShop (Administrador)',
      leftActorName: 'Administrador',
      leftConnections: [0, 1, 2],
      rightActorName: 'Comerciante',
      rightConnections: [3],
      useCases: [
        ['Gestionar usuarios'],
        ['Supervisar ventas'],
        ['Resolver reclamos'],
        ['Enviar documentos']
      ]
    }
  ];

  // Variantes con 1 actor exclusivo (todas las acciones del rol)
  const singleConfigs = [
    {
      name: 'diagrama-consumidor-solo',
      title: 'FusaShop (Consumidor)',
      leftActorName: 'Consumidor',
      leftConnections: [0, 1, 2, 3],
      rightActorName: null,
      rightConnections: [],
      useCases: [
        ['Buscar productos'],
        ['Gestionar carrito'],
        ['Pagar la compra'],
        ['Rastrear el pedido']
      ]
    },
    {
      name: 'diagrama-comerciante-solo',
      title: 'FusaShop (Comerciante)',
      leftActorName: 'Comerciante',
      leftConnections: [0, 1, 2, 3],
      rightActorName: null,
      rightConnections: [],
      useCases: [
        ['Publicar productos'],
        ['Controlar inventario'],
        ['Atender pedidos'],
        ['Consultar ventas']
      ]
    },
    {
      name: 'diagrama-administrador-solo',
      title: 'FusaShop (Administrador)',
      leftActorName: 'Administrador',
      leftConnections: [0, 1, 2, 3],
      rightActorName: null,
      rightConnections: [],
      useCases: [
        ['Gestionar usuarios'],
        ['Aprobar tiendas (KYC)'],
        ['Supervisar ventas'],
        ['Resolver reclamos']
      ]
    }
  ];

  const all = [...dualConfigs, ...singleConfigs];

  for (const item of all) {
    const svgCode = generateDiagram(item);
    const svgPathDocs = path.join(docsDir, `${item.name}.svg`);
    const pngPathDocs = path.join(docsDir, `${item.name}.png`);
    const pngPathBrain = path.join(brainDir, `${item.name}.png`);

    fs.writeFileSync(svgPathDocs, svgCode, 'utf8');
    await sharp(Buffer.from(svgCode)).png().toFile(pngPathDocs);
    await sharp(Buffer.from(svgCode)).png().toFile(pngPathBrain);

    console.log(`[OK] Guardado: ${item.name} (.svg y .png)`);
  }

  // Generar HTML visor
  const htmlPath = path.join(docsDir, 'diagramas-estilo-referencia.html');
  const htmlContent = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Diagramas de Caso de Uso - Estilo Referencia</title>
  <style>
    body { font-family: Arial, sans-serif; background: #F4F4F9; margin: 0; padding: 30px; text-align: center; }
    h1 { margin-bottom: 5px; }
    p { color: #555; margin-bottom: 30px; }
    .grid { display: flex; flex-direction: column; gap: 40px; max-width: 900px; margin: 0 auto; }
    .card { background: #FFFFFF; border: 1px solid #CCC; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .card h2 { margin-top: 0; text-align: left; border-bottom: 2px solid #000; padding-bottom: 8px; font-size: 18px; }
    .card img { max-width: 100%; height: auto; border: 1px solid #000; }
    .links { margin-top: 15px; display: flex; justify-content: flex-end; gap: 10px; }
    .btn { background: #000; color: #FFF; padding: 8px 14px; text-decoration: none; font-size: 13px; font-weight: bold; }
    .btn:hover { background: #333; }
  </style>
</head>
<body>
  <h1>FusaShop · Diagramas de Casos de Uso (Estilo Referencia)</h1>
  <p>Dibujados con la misma estructura, muñequitos de palitos y simplicidad de la imagen de referencia</p>

  <div class="grid">
    <div class="card">
      <h2>1. Rol Consumidor</h2>
      <img src="diagrama-consumidor-solo.png" alt="Consumidor" />
      <div class="links">
        <a class="btn" href="diagrama-consumidor-solo.png" download>Descargar PNG</a>
        <a class="btn" href="diagrama-consumidor-solo.svg" download>Descargar SVG</a>
      </div>
    </div>

    <div class="card">
      <h2>2. Rol Comerciante</h2>
      <img src="diagrama-comerciante-solo.png" alt="Comerciante" />
      <div class="links">
        <a class="btn" href="diagrama-comerciante-solo.png" download>Descargar PNG</a>
        <a class="btn" href="diagrama-comerciante-solo.svg" download>Descargar SVG</a>
      </div>
    </div>

    <div class="card">
      <h2>3. Rol Administrador</h2>
      <img src="diagrama-administrador-solo.png" alt="Administrador" />
      <div class="links">
        <a class="btn" href="diagrama-administrador-solo.png" download>Descargar PNG</a>
        <a class="btn" href="diagrama-administrador-solo.svg" download>Descargar SVG</a>
      </div>
    </div>
  </div>
</body>
</html>`;

  fs.writeFileSync(htmlPath, htmlContent, 'utf8');
  console.log(`[OK] Visor listo en: ${htmlPath}`);
}

main().catch(console.error);
