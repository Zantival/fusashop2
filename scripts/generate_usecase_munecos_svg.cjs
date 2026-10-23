/**
 * Generador del Diagrama de Casos de Uso con Muñequitos (Stick Figures UML)
 * Estilo idéntico a la imagen de referencia: Actores a los costados,
 * elipses centrales en columna, flechas <<include>> y <<extend>>,
 * estrictamente en BLANCO Y NEGRO (sin colores).
 */

const fs = require('fs');
const path = require('path');

console.log('Generando Diagramas de Casos de Uso con Muñequitos UML (Blanco y Negro)...');

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

// =========================================================================
// DIAGRAMA 1: CASOS DE USO CLIENTE (IDÉNTICO A LA TOPOLOGÍA DE REFERENCIA)
// =========================================================================
const width1 = 980;
const height1 = 1380;

const svgClientContent = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width1} ${height1}" width="${width1}" height="${height1}">
  <defs>
    <!-- Flechas abiertas para <<include>> y <<extend>> -->
    <marker id="arrow-open" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
      <path d="M 0 1.5 L 8 5 L 0 8.5" fill="none" stroke="#000000" stroke-width="1.6" />
    </marker>
  </defs>

  <style>
    .title-main { font-family: 'Segoe UI', Arial, sans-serif; font-size: 18px; font-weight: 800; fill: #000000; letter-spacing: 0.8px; text-anchor: middle; }
    .title-sub { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11.5px; font-weight: 500; fill: #475569; text-anchor: middle; }
    .assoc-line { fill: none; stroke: #000000; stroke-width: 1.6; }
    .dep-line { fill: none; stroke: #000000; stroke-width: 1.4; stroke-dasharray: 5,4; }
    .tag-dep { font-family: 'Segoe UI', Arial, sans-serif; font-size: 9.5px; font-weight: 700; fill: #000000; text-anchor: middle; background: #FFFFFF; }
  </style>

  <!-- Fondo Blanco -->
  <rect width="${width1}" height="${height1}" fill="#FFFFFF" />

  <!-- Título Superior -->
  <text x="490" y="45" class="title-main">DIAGRAMA GENERAL CASOS DE USO — CLIENTE / COMPRADOR</text>
  <text x="490" y="68" class="title-sub">Plataforma FusaShop · Especificación UML en Blanco y Negro (Sin Color)</text>

  <!-- ========================================================================= -->
  <!-- MUÑEQUITO IZQUIERDA: CLIENTE REGISTRADO                                   -->
  <!-- ========================================================================= -->
  ${drawActor(140, 260, 'Cliente registrado', '(Comprador)')}

  <!-- ========================================================================= -->
  <!-- MUÑEQUITO DERECHA: CLIENTE (VISITANTE)                                    -->
  <!-- ========================================================================= -->
  ${drawActor(840, 260, 'Cliente', '(Visitante)')}

  <!-- ========================================================================= -->
  <!-- COLUMNA CENTRAL: CASOS DE USO                                             -->
  <!-- ========================================================================= -->
  <!-- 1. Navegar por la tienda -->
  ${drawUseCase(490, 130, 115, 26, 'Navegar por la tienda', 'Catálogo y vitrinas Sumapaz')}
  <!-- 2. Ver detalles del producto -->
  ${drawUseCase(490, 205, 115, 26, 'Ver detalles del producto', 'Precios, stock y fotos')}
  <!-- 3. Gestionar carrito -->
  ${drawUseCase(490, 280, 115, 26, 'Gestionar carrito', 'Agregar o modificar ítems')}
  <!-- 4. Registrarse -->
  ${drawUseCase(490, 365, 105, 26, 'Registrarse', 'Creación de cuenta nueva')}
  <!-- 5. Iniciar sesión -->
  ${drawUseCase(490, 460, 105, 26, 'Iniciar sesión', 'Google OAuth o Email 2FA')}
  <!-- 6. Realizar compra -->
  ${drawUseCase(680, 560, 110, 26, 'Realizar compra', 'Checkout con flete y pago')}
  <!-- 7. Cotizar flete logístico -->
  ${drawUseCase(830, 640, 105, 25, 'Cotizar flete Envia', 'API en tiempo real')}
  <!-- 8. Pagar en pasarela -->
  ${drawUseCase(830, 715, 105, 25, 'Pagar en pasarela', 'Wompi PSE / PayU')}
  <!-- 9. Redimir FusaCoins -->
  ${drawUseCase(680, 715, 100, 25, 'Redimir FusaCoins', 'O cupón de descuento')}
  <!-- 10. Ver pedido y trazabilidad -->
  ${drawUseCase(490, 680, 115, 26, 'Ver pedido', 'Trazabilidad y guía')}
  <!-- 11. Radicar PQRS -->
  ${drawUseCase(680, 800, 110, 26, 'Radicar PQRS', 'Garantía Ley 1480')}
  <!-- 12. Calificar producto -->
  ${drawUseCase(680, 875, 110, 26, 'Calificar producto', 'Reseña con análisis NLP')}
  <!-- 13. Visualizar datos del perfil -->
  ${drawUseCase(280, 560, 110, 26, 'Visualizar datos', 'Información de cuenta')}
  <!-- 14. Actualizar datos -->
  ${drawUseCase(280, 670, 105, 26, 'Actualizar datos', 'Direcciones de envío')}
  <!-- 15. Cambiar contraseña -->
  ${drawUseCase(280, 765, 105, 26, 'Cambiar contraseña', 'Seguridad y 2FA')}

  <!-- ========================================================================= -->
  <!-- LÍNEAS DE ASOCIACIÓN DESDE LOS MUÑEQUITOS                                 -->
  <!-- ========================================================================= -->
  <!-- Desde Cliente Registrado (Izquierda) -->
  <line x1="170" y1="230" x2="375" y2="130" class="assoc-line" />
  <line x1="170" y1="245" x2="375" y2="205" class="assoc-line" />
  <line x1="170" y1="260" x2="375" y2="280" class="assoc-line" />
  <line x1="170" y1="300" x2="410" y2="445" class="assoc-line" />

  <!-- Desde Cliente Visitante (Derecha) -->
  <line x1="810" y1="230" x2="605" y2="130" class="assoc-line" />
  <line x1="810" y1="245" x2="605" y2="205" class="assoc-line" />
  <line x1="810" y1="260" x2="605" y2="280" class="assoc-line" />
  <line x1="810" y1="285" x2="595" y2="365" class="assoc-line" />

  <!-- ========================================================================= -->
  <!-- RELACIONES <<include>> Y <<extend>> (LÍNEAS SEGMENTADAS CON FLECHA)       -->
  <!-- ========================================================================= -->
  <!-- Registrarse -> Iniciar sesión (include) -->
  <line x1="490" y1="391" x2="490" y2="434" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="535" y="416" class="tag-dep">&lt;&lt;include&gt;&gt;</text>

  <!-- Iniciar sesión -> Realizar compra (include) -->
  <path d="M 545 480 L 680 534" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="635" y="500" class="tag-dep">&lt;&lt;include&gt;&gt;</text>

  <!-- Realizar compra -> Cotizar flete (include) -->
  <path d="M 740 582 L 810 618" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="795" y="595" class="tag-dep">&lt;&lt;include&gt;&gt;</text>

  <!-- Realizar compra -> Pagar pasarela (include) -->
  <path d="M 770 585 L 830 690" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="815" y="630" class="tag-dep">&lt;&lt;include&gt;&gt;</text>

  <!-- Redimir FusaCoins -> Realizar compra (extend) -->
  <line x1="680" y1="690" x2="680" y2="586" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="640" y="640" class="tag-dep">&lt;&lt;extend&gt;&gt;</text>

  <!-- Iniciar sesión -> Ver pedido (extend) -->
  <line x1="490" y1="486" x2="490" y2="654" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="450" y="570" class="tag-dep">&lt;&lt;extend&gt;&gt;</text>

  <!-- Ver pedido -> Radicar PQRS (extend) -->
  <path d="M 545 700 L 650 778" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="620" y="735" class="tag-dep">&lt;&lt;extend&gt;&gt;</text>

  <!-- Ver pedido -> Calificar producto (extend) -->
  <path d="M 535 705 L 650 855" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="615" y="790" class="tag-dep">&lt;&lt;extend&gt;&gt;</text>

  <!-- Iniciar sesión -> Visualizar datos (extend) -->
  <path d="M 435 480 L 310 538" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="350" y="500" class="tag-dep">&lt;&lt;extend&gt;&gt;</text>

  <!-- Visualizar datos -> Actualizar datos (extend) -->
  <line x1="280" y1="586" x2="280" y2="644" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="240" y="618" class="tag-dep">&lt;&lt;extend&gt;&gt;</text>

  <!-- Visualizar datos -> Cambiar contraseña (extend) -->
  <path d="M 280 696 L 280 739" class="dep-line" marker-end="url(#arrow-open)" />
  <text x="240" y="722" class="tag-dep">&lt;&lt;extend&gt;&gt;</text>

  <!-- Leyenda Técnica Inferior -->
  <rect x="40" y="1260" width="900" height="75" fill="#FFFFFF" stroke="#000000" stroke-width="1.2" />
  <text x="60" y="1285" font-family="'Segoe UI', Arial, sans-serif" font-size="11" font-weight="800" fill="#000000">CONVENCIONES DEL DIAGRAMA DE CASOS DE USO (UML 2.5):</text>
  <text x="60" y="1305" font-family="'Segoe UI', Arial, sans-serif" font-size="10" fill="#000000">• Muñequitos (Stick Figures): Actores del sistema (Cliente Visitante y Cliente Registrado).</text>
  <text x="60" y="1322" font-family="'Segoe UI', Arial, sans-serif" font-size="10" fill="#000000">• Elipses: Casos de uso funcionales. Línea continua: Asociación directa de actor. Línea segmentada: Dependencias &lt;&lt;include&gt;&gt; y &lt;&lt;extend&gt;&gt;.</text>

</svg>`;

const outputPath1 = path.resolve('docs/diagrama-casos-de-uso-cliente-munecos.svg');
fs.writeFileSync(outputPath1, svgClientContent, 'utf-8');
console.log(`SVG Cliente con Muñequitos generado en: ${outputPath1}`);

// También copiamos a la ruta general para máxima conveniencia
fs.copyFileSync(outputPath1, path.resolve('docs/diagrama-casos-de-uso-fusashop.svg'));
console.log('Actualizado docs/diagrama-casos-de-uso-fusashop.svg con los muñequitos.');
