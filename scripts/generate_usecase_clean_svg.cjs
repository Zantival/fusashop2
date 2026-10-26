const fs = require('fs');
const path = require('path');

const width = 1100;
const height = 920;

function actor(x, y, label) {
  return `
    <g transform="translate(${x}, ${y})">
      <circle cx="0" cy="-36" r="15" fill="#FFFFFF" stroke="#000000" stroke-width="2" />
      <line x1="0" y1="-21" x2="0" y2="15" stroke="#000000" stroke-width="2" />
      <line x1="-24" y1="-8" x2="24" y2="-8" stroke="#000000" stroke-width="2" />
      <line x1="0" y1="15" x2="-18" y2="45" stroke="#000000" stroke-width="2" />
      <line x1="0" y1="15" x2="18" y2="45" stroke="#000000" stroke-width="2" />
      <text x="0" y="68" font-family="'Segoe UI', Arial, sans-serif" font-size="13" font-weight="700" fill="#000000" text-anchor="middle">${label}</text>
    </g>
  `;
}

function usecase(cx, cy, rx, ry, text) {
  return `
    <g>
      <ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#FFFFFF" stroke="#000000" stroke-width="1.8" />
      <text x="${cx}" y="${cy}" font-family="'Segoe UI', Arial, sans-serif" font-size="12.5" font-weight="600" fill="#000000" text-anchor="middle" dominant-baseline="central">${text}</text>
    </g>
  `;
}

const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${height}" width="${width}" height="${height}">
  <defs>
    <marker id="arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
      <path d="M 0 1.5 L 8 5 L 0 8.5" fill="none" stroke="#000000" stroke-width="1.6" />
    </marker>
  </defs>

  <style>
    .title { font-family: 'Segoe UI', Arial, sans-serif; font-size: 16px; font-weight: 800; fill: #000000; letter-spacing: 1px; text-anchor: middle; }
    .line { fill: none; stroke: #000000; stroke-width: 1.6; }
    .dash { fill: none; stroke: #000000; stroke-width: 1.4; stroke-dasharray: 5,4; }
    .tag { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; font-weight: 700; fill: #000000; text-anchor: middle; }
  </style>

  <rect width="${width}" height="${height}" fill="#FFFFFF" />

  <text x="550" y="45" class="title">DIAGRAMA GENERAL CASOS DE USO</text>

  <!-- ACTORES (MUÑEQUITOS) -->
  ${actor(150, 240, 'Cliente registrado')}
  ${actor(950, 240, 'Cliente')}

  <!-- CASOS DE USO SUPERIORES -->
  ${usecase(550, 95, 120, 26, 'Navegar por la tienda')}
  ${usecase(550, 175, 120, 26, 'Ver detalles del producto')}
  ${usecase(550, 255, 115, 26, 'Gestionar carrito')}
  ${usecase(550, 345, 105, 26, 'Registrarse')}
  ${usecase(550, 450, 105, 26, 'Iniciar sesión')}

  <!-- CASOS DE USO DERECHA (COMPRA Y PEDIDO) -->
  ${usecase(780, 520, 110, 26, 'Realizar compra')}
  ${usecase(550, 580, 105, 26, 'Ver pedido')}
  ${usecase(780, 650, 115, 26, 'Contactar soporte / PQRS')}
  ${usecase(780, 740, 110, 26, 'Calificar producto')}

  <!-- CASOS DE USO IZQUIERDA (DATOS / PERFIL) -->
  ${usecase(300, 540, 105, 26, 'Visualizar datos')}
  ${usecase(190, 670, 100, 26, 'Actualizar datos')}
  ${usecase(390, 670, 105, 26, 'Cambiar contraseña')}

  <!-- LÍNEAS SÓLIDAS DESDE ACTOR IZQUIERDO (Cliente Registrado) -->
  <line x1="180" y1="210" x2="430" y2="95" class="line" />
  <line x1="180" y1="225" x2="430" y2="175" class="line" />
  <line x1="180" y1="240" x2="435" y2="255" class="line" />
  <line x1="180" y1="275" x2="455" y2="435" class="line" />

  <!-- LÍNEAS SÓLIDAS DESDE ACTOR DERECHO (Cliente) -->
  <line x1="920" y1="210" x2="670" y2="95" class="line" />
  <line x1="920" y1="225" x2="670" y2="175" class="line" />
  <line x1="920" y1="240" x2="665" y2="255" class="line" />
  <line x1="920" y1="265" x2="655" y2="345" class="line" />

  <!-- FLECHAS DISCONTINUAS <<include>> Y <<extend>> -->
  <!-- Registrarse -> Iniciar sesión -->
  <line x1="550" y1="371" x2="550" y2="424" class="dash" marker-end="url(#arrow)" />
  <text x="595" y="402" class="tag">&lt;&lt;include&gt;&gt;</text>

  <!-- Iniciar sesión -> Realizar compra -->
  <path d="M 640 470 L 710 500" class="dash" marker-end="url(#arrow)" />
  <text x="695" y="475" class="tag">&lt;&lt;include&gt;&gt;</text>

  <!-- Iniciar sesión -> Ver pedido -->
  <line x1="550" y1="476" x2="550" y2="554" class="dash" marker-end="url(#arrow)" />
  <text x="505" y="520" class="tag">&lt;&lt;extend&gt;&gt;</text>

  <!-- Ver pedido -> Contactar soporte / PQRS -->
  <path d="M 640 595 L 705 630" class="dash" marker-end="url(#arrow)" />
  <text x="690" y="605" class="tag">&lt;&lt;extend&gt;&gt;</text>

  <!-- Ver pedido -> Calificar producto -->
  <path d="M 610 600 L 715 718" class="dash" marker-end="url(#arrow)" />
  <text x="685" y="680" class="tag">&lt;&lt;extend&gt;&gt;</text>

  <!-- Iniciar sesión -> Visualizar datos -->
  <path d="M 460 470 L 380 518" class="dash" marker-end="url(#arrow)" />
  <text x="395" y="485" class="tag">&lt;&lt;extend&gt;&gt;</text>

  <!-- Visualizar datos -> Actualizar datos -->
  <path d="M 275 565 L 215 644" class="dash" marker-end="url(#arrow)" />
  <text x="215" y="595" class="tag">&lt;&lt;extend&gt;&gt;</text>

  <!-- Visualizar datos -> Cambiar contraseña -->
  <path d="M 325 565 L 365 644" class="dash" marker-end="url(#arrow)" />
  <text x="380" y="595" class="tag">&lt;&lt;extend&gt;&gt;</text>

</svg>`;

fs.writeFileSync('docs/diagrama-casos-de-uso-fusashop.svg', svg, 'utf-8');
fs.writeFileSync('docs/diagrama-casos-de-uso-cliente-munecos.svg', svg, 'utf-8');
console.log('Diagrama limpio y espacioso guardado exitosamente.');
