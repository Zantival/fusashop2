/**
 * Generador de Diagrama Entidad-Relación (Modelo Peter Chen / Conceptual)
 * Estilo técnico formal en Blanco y Negro para Tesis de Grado — FusaShop
 */

const fs = require('fs');
const path = require('path');

console.log('Generando Diagrama Entidad-Relación Conceptual en Blanco y Negro...');

const svgWidth = 1400;
const svgHeight = 1100;

const svgContent = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${svgWidth} ${svgHeight}" width="${svgWidth}" height="${svgHeight}">
  <style>
    .diagram-title { font-family: 'Segoe UI', Arial, sans-serif; font-size: 20px; font-weight: 800; fill: #000000; letter-spacing: 0.5px; }
    .diagram-subtitle { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; font-weight: 500; fill: #334155; }
    
    /* Entidades (Rectángulos) */
    .entity-box { fill: #FFFFFF; stroke: #000000; stroke-width: 2.2; }
    .entity-text { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; font-weight: 800; fill: #000000; text-anchor: middle; dominant-baseline: middle; letter-spacing: 0.8px; }
    
    /* Relaciones (Rombos) */
    .rel-diamond { fill: #FFFFFF; stroke: #000000; stroke-width: 2; }
    .rel-text { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11.5px; font-weight: 700; fill: #000000; text-anchor: middle; dominant-baseline: middle; }
    
    /* Atributos (Elipses) */
    .attr-ellipse { fill: #FFFFFF; stroke: #000000; stroke-width: 1.4; }
    .attr-text { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; font-weight: 500; fill: #000000; text-anchor: middle; dominant-baseline: middle; }
    .attr-pk { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; font-weight: 800; fill: #000000; text-anchor: middle; dominant-baseline: middle; text-decoration: underline; }
    
    /* Conectores y Cardinalidades */
    .conn-line { fill: none; stroke: #000000; stroke-width: 1.6; stroke-linecap: round; }
    .card-text { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; font-weight: 700; fill: #000000; }
    .rel-ratio { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; font-weight: 800; fill: #000000; text-anchor: middle; }
  </style>

  <!-- Fondo Blanco Técnico -->
  <rect width="${svgWidth}" height="${svgHeight}" fill="#FFFFFF" />

  <!-- Encabezado de la Lámina Técnica -->
  <rect x="40" y="25" width="${svgWidth - 80}" height="65" fill="#FFFFFF" stroke="#000000" stroke-width="1.5" />
  <text x="65" y="52" class="diagram-title">DIAGRAMA ENTIDAD-RELACIÓN CONCEPTUAL (MODELO DE PETER CHEN)</text>
  <text x="65" y="74" class="diagram-subtitle">Plataforma E-Commerce FusaShop · Especificación Monocromática (Blanco y Negro) para Tesis de Grado</text>

  <!-- ========================================================================= -->
  <!-- 1. ENTIDAD: USUARIO (Centro Izquierdo)                                    -->
  <!-- ========================================================================= -->
  <rect x="220" y="380" width="130" height="50" class="entity-box" />
  <text x="285" y="405" class="entity-text">USUARIO</text>

  <!-- Atributos de USUARIO -->
  <ellipse cx="90" cy="310" rx="42" ry="17" class="attr-ellipse" />
  <text x="90" y="310" class="attr-pk">id_usuario</text>
  <line x1="132" y1="310" x2="220" y2="390" class="conn-line" />

  <ellipse cx="90" cy="355" rx="40" ry="17" class="attr-ellipse" />
  <text x="90" y="355" class="attr-text">Nombre</text>
  <line x1="130" y1="355" x2="220" y2="395" class="conn-line" />

  <ellipse cx="90" cy="400" rx="38" ry="17" class="attr-ellipse" />
  <text x="90" y="400" class="attr-text">Email</text>
  <line x1="128" y1="400" x2="220" y2="405" class="conn-line" />

  <ellipse cx="90" cy="445" rx="40" ry="17" class="attr-ellipse" />
  <text x="90" y="445" class="attr-text">Password</text>
  <line x1="130" y1="445" x2="220" y2="415" class="conn-line" />

  <ellipse cx="90" cy="490" rx="38" ry="17" class="attr-ellipse" />
  <text x="90" y="490" class="attr-text">Rol</text>
  <line x1="128" y1="490" x2="220" y2="425" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- RELACIÓN: USUARIO - POSEE - PERFIL_COMERCIO                               -->
  <!-- ========================================================================= -->
  <line x1="285" y1="380" x2="285" y2="285" class="conn-line" />
  <text x="295" y="340" class="card-text">(1, 1)</text>

  <polygon points="285,225 335,255 285,285 235,255" class="rel-diamond" />
  <text x="285" y="255" class="rel-text">Posee</text>
  <text x="285" y="215" class="rel-ratio">1:1</text>

  <line x1="285" y1="225" x2="285" y2="150" class="conn-line" />
  <text x="295" y="195" class="card-text">(0, 1)</text>

  <!-- ========================================================================= -->
  <!-- 2. ENTIDAD: PERFIL_COMERCIO (Superior Izquierdo)                          -->
  <!-- ========================================================================= -->
  <rect x="210" y="100" width="150" height="50" class="entity-box" />
  <text x="285" y="125" class="entity-text">PERFIL_COMERCIO</text>

  <!-- Atributos de PERFIL_COMERCIO -->
  <ellipse cx="90" cy="95" rx="40" ry="17" class="attr-ellipse" />
  <text x="90" y="95" class="attr-pk">id_perfil</text>
  <line x1="130" y1="95" x2="210" y2="115" class="conn-line" />

  <ellipse cx="90" cy="140" rx="42" ry="17" class="attr-ellipse" />
  <text x="90" y="140" class="attr-text">RazonSocial</text>
  <line x1="132" y1="140" x2="210" y2="130" class="conn-line" />

  <ellipse cx="90" cy="185" rx="35" ry="17" class="attr-ellipse" />
  <text x="90" y="185" class="attr-text">NIT</text>
  <line x1="125" y1="185" x2="210" y2="145" class="conn-line" />

  <ellipse cx="285" cy="50" rx="38" ry="16" class="attr-ellipse" />
  <text x="285" y="50" class="attr-text">EstadoKYC</text>
  <line x1="285" y1="66" x2="285" y2="100" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- RELACIÓN: PERFIL_COMERCIO - PUBLICA - PRODUCTO                            -->
  <!-- ========================================================================= -->
  <line x1="360" y1="125" x2="495" y2="125" class="conn-line" />
  <text x="400" y="115" class="card-text">(1, n)</text>

  <polygon points="555,95 615,125 555,155 495,125" class="rel-diamond" />
  <text x="555" y="125" class="rel-text">Publica</text>
  <text x="555" y="85" class="rel-ratio">1:N</text>

  <line x1="615" y1="125" x2="740" y2="125" class="conn-line" />
  <text x="670" y="115" class="card-text">(1, 1)</text>

  <!-- ========================================================================= -->
  <!-- 3. ENTIDAD: PRODUCTO (Superior Derecho)                                   -->
  <!-- ========================================================================= -->
  <rect x="740" y="100" width="140" height="50" class="entity-box" />
  <text x="810" y="125" class="entity-text">PRODUCTO</text>

  <!-- Atributos de PRODUCTO -->
  <ellipse cx="950" cy="70" rx="42" ry="17" class="attr-ellipse" />
  <text x="950" y="70" class="attr-pk">id_producto</text>
  <line x1="880" y1="110" x2="910" y2="78" class="conn-line" />

  <ellipse cx="960" cy="115" rx="38" ry="17" class="attr-ellipse" />
  <text x="960" y="115" class="attr-text">Nombre</text>
  <line x1="880" y1="125" x2="922" y2="117" class="conn-line" />

  <ellipse cx="955" cy="160" rx="35" ry="17" class="attr-ellipse" />
  <text x="955" y="160" class="attr-text">Precio</text>
  <line x1="880" y1="138" x2="922" y2="152" class="conn-line" />

  <ellipse cx="810" cy="48" rx="35" ry="16" class="attr-ellipse" />
  <text x="810" y="48" class="attr-text">Stock</text>
  <line x1="810" y1="64" x2="810" y2="100" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- RELACIÓN: USUARIO - REALIZA - PEDIDO                                      -->
  <!-- ========================================================================= -->
  <line x1="350" y1="405" x2="495" y2="405" class="conn-line" />
  <text x="390" y="395" class="card-text">(0, n)</text>

  <polygon points="555,375 615,405 555,435 495,405" class="rel-diamond" />
  <text x="555" y="405" class="rel-text">Realiza</text>
  <text x="555" y="365" class="rel-ratio">1:N</text>

  <line x1="615" y1="405" x2="740" y2="405" class="conn-line" />
  <text x="670" y="395" class="card-text">(1, 1)</text>

  <!-- ========================================================================= -->
  <!-- 4. ENTIDAD: PEDIDO (Centro)                                               -->
  <!-- ========================================================================= -->
  <rect x="740" y="380" width="140" height="50" class="entity-box" />
  <text x="810" y="405" class="entity-text">PEDIDO</text>

  <!-- Atributos de PEDIDO -->
  <ellipse cx="945" cy="345" rx="42" ry="17" class="attr-ellipse" />
  <text x="945" y="345" class="attr-pk">id_pedido</text>
  <line x1="880" y1="390" x2="905" y2="355" class="conn-line" />

  <ellipse cx="955" cy="390" rx="40" ry="17" class="attr-ellipse" />
  <text x="955" y="390" class="attr-text">Referencia</text>
  <line x1="880" y1="405" x2="915" y2="395" class="conn-line" />

  <ellipse cx="950" cy="435" rx="35" ry="17" class="attr-ellipse" />
  <text x="950" y="435" class="attr-text">Total</text>
  <line x1="880" y1="415" x2="915" y2="430" class="conn-line" />

  <ellipse cx="955" cy="480" rx="42" ry="17" class="attr-ellipse" />
  <text x="955" y="480" class="attr-text">EstadoPago</text>
  <line x1="875" y1="428" x2="915" y2="470" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- RELACIÓN N:M: PEDIDO - CONTIENE - PRODUCTO (Línea Vertical)               -->
  <!-- ========================================================================= -->
  <line x1="810" y1="380" x2="810" y2="295" class="conn-line" />
  <text x="820" y="340" class="card-text">(1, n)</text>

  <polygon points="810,235 865,265 810,295 755,265" class="rel-diamond" />
  <text x="810" y="265" class="rel-text">Contiene</text>
  <text x="880" y="265" class="rel-ratio">N:M</text>

  <line x1="810" y1="235" x2="810" y2="150" class="conn-line" />
  <text x="820" y="190" class="card-text">(0, n)</text>

  <!-- Atributos propios de la relación CONTIENE (ITEM_PEDIDO) -->
  <ellipse cx="660" cy="245" rx="38" ry="16" class="attr-ellipse" />
  <text x="660" y="245" class="attr-text">Cantidad</text>
  <line x1="698" y1="248" x2="755" y2="260" class="conn-line" />

  <ellipse cx="660" cy="285" rx="35" ry="16" class="attr-ellipse" />
  <text x="660" y="285" class="attr-text">Precio</text>
  <line x1="695" y1="282" x2="760" y2="272" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- RELACIÓN: PEDIDO - PAGA_CON - TRANSACCION_PAGO                            -->
  <!-- ========================================================================= -->
  <line x1="740" y1="420" x2="615" y2="520" class="conn-line" />
  <text x="690" y="450" class="card-text">(1, n)</text>

  <polygon points="565,500 625,530 565,560 505,530" class="rel-diamond" />
  <text x="565" y="530" class="rel-text">Cobra</text>
  <text x="565" y="490" class="rel-ratio">1:N</text>

  <line x1="505" y1="530" x2="380" y2="530" class="conn-line" />
  <text x="440" y="520" class="card-text">(1, 1)</text>

  <!-- 5. ENTIDAD: TRANSACCION_PAGO (Inferior Izquierda) -->
  <rect x="220" y="505" width="160" height="50" class="entity-box" />
  <text x="300" y="530" class="entity-text">TRANSACCION_PAGO</text>

  <ellipse cx="90" cy="550" rx="42" ry="17" class="attr-ellipse" />
  <text x="90" y="550" class="attr-pk">id_transacc</text>
  <line x1="132" y1="550" x2="220" y2="535" class="conn-line" />

  <ellipse cx="90" cy="595" rx="38" ry="17" class="attr-ellipse" />
  <text x="90" y="595" class="attr-text">Pasarela</text>
  <line x1="128" y1="595" x2="220" y2="545" class="conn-line" />

  <ellipse cx="90" cy="640" rx="42" ry="17" class="attr-ellipse" />
  <text x="90" y="640" class="attr-text">FirmaSHA</text>
  <line x1="132" y1="640" x2="220" y2="550" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- RELACIÓN: PEDIDO - DESGLOSA - SUBORDEN_VENDEDOR                           -->
  <!-- ========================================================================= -->
  <line x1="810" y1="430" x2="810" y2="525" class="conn-line" />
  <text x="820" y="475" class="card-text">(1, n)</text>

  <polygon points="810,525 870,555 810,585 750,555" class="rel-diamond" />
  <text x="810" y="555" class="rel-text">Desglosa</text>
  <text x="885" y="555" class="rel-ratio">1:N</text>

  <line x1="810" y1="585" x2="810" y2="650" class="conn-line" />
  <text x="820" y="620" class="card-text">(1, 1)</text>

  <!-- ========================================================================= -->
  <!-- 6. ENTIDAD: SUBORDEN_VENDEDOR (Centro Inferior)                           -->
  <!-- ========================================================================= -->
  <rect x="725" y="650" width="170" height="50" class="entity-box" />
  <text x="810" y="675" class="entity-text">SUBORDEN_VENDEDOR</text>

  <ellipse cx="975" cy="625" rx="44" ry="17" class="attr-ellipse" />
  <text x="975" y="625" class="attr-pk">id_suborden</text>
  <line x1="895" y1="660" x2="932" y2="632" class="conn-line" />

  <ellipse cx="985" cy="670" rx="42" ry="17" class="attr-ellipse" />
  <text x="985" y="670" class="attr-text">MontoBruto</text>
  <line x1="895" y1="675" x2="943" y2="670" class="conn-line" />

  <ellipse cx="980" cy="715" rx="40" ry="17" class="attr-ellipse" />
  <text x="980" y="715" class="attr-text">Comision</text>
  <line x1="895" y1="688" x2="941" y2="708" class="conn-line" />

  <ellipse cx="975" cy="760" rx="38" ry="17" class="attr-ellipse" />
  <text x="975" y="760" class="attr-text">MontoNeto</text>
  <line x1="880" y1="700" x2="940" y2="748" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- RELACIÓN: SUBORDEN - ACREDITA - BILLETERA_COMERCIO                        -->
  <!-- ========================================================================= -->
  <line x1="810" y1="700" x2="810" y2="795" class="conn-line" />
  <text x="820" y="745" class="card-text">(1, 1)</text>

  <polygon points="810,795 870,825 810,855 750,825" class="rel-diamond" />
  <text x="810" y="825" class="rel-text">Acredita</text>
  <text x="885" y="825" class="rel-ratio">1:1</text>

  <line x1="810" y1="855" x2="810" y2="920" class="conn-line" />
  <text x="820" y="890" class="card-text">(1, 1)</text>

  <!-- ========================================================================= -->
  <!-- 7. ENTIDAD: BILLETERA_COMERCIO (Inferior Centro)                          -->
  <!-- ========================================================================= -->
  <rect x="720" y="920" width="180" height="50" class="entity-box" />
  <text x="810" y="945" class="entity-text">BILLETERA_COMERCIO</text>

  <ellipse cx="985" cy="890" rx="42" ry="17" class="attr-ellipse" />
  <text x="985" y="890" class="attr-pk">id_entrada</text>
  <line x1="900" y1="930" x2="945" y2="902" class="conn-line" />

  <ellipse cx="985" cy="935" rx="38" ry="17" class="attr-ellipse" />
  <text x="985" y="935" class="attr-text">Credito</text>
  <line x1="900" y1="945" x2="947" y2="938" class="conn-line" />

  <ellipse cx="990" cy="980" rx="46" ry="17" class="attr-ellipse" />
  <text x="990" y="980" class="attr-text">SaldoResult</text>
  <line x1="900" y1="958" x2="946" y2="975" class="conn-line" />

  <ellipse cx="995" cy="1025" rx="52" ry="17" class="attr-ellipse" />
  <text x="995" y="1025" class="attr-text">ClaveIdempot</text>
  <line x1="885" y1="970" x2="946" y2="1015" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- 8. ENTIDAD: PQRS (Inferior Izquierda Extrema)                             -->
  <!-- ========================================================================= -->
  <line x1="285" y1="430" x2="285" y2="700" class="conn-line" />
  <text x="295" y="615" class="card-text">(0, n)</text>

  <polygon points="285,700 335,730 285,760 235,730" class="rel-diamond" />
  <text x="285" y="730" class="rel-text">Radica</text>
  <text x="285" y="690" class="rel-ratio">1:N</text>

  <line x1="285" y1="760" x2="285" y2="830" class="conn-line" />
  <text x="295" y="800" class="card-text">(1, 1)</text>

  <rect x="225" y="830" width="120" height="50" class="entity-box" />
  <text x="285" y="855" class="entity-text">PQRS</text>

  <ellipse cx="95" cy="790" rx="42" ry="17" class="attr-ellipse" />
  <text x="95" y="790" class="attr-pk">id_ticket</text>
  <line x1="137" y1="790" x2="225" y2="840" class="conn-line" />

  <ellipse cx="95" cy="835" rx="38" ry="17" class="attr-ellipse" />
  <text x="95" y="835" class="attr-text">TipoPQRS</text>
  <line x1="133" y1="835" x2="225" y2="850" class="conn-line" />

  <ellipse cx="95" cy="880" rx="42" ry="17" class="attr-ellipse" />
  <text x="95" y="880" class="attr-text">PlazoLegal</text>
  <line x1="137" y1="880" x2="225" y2="865" class="conn-line" />

  <!-- ========================================================================= -->
  <!-- 9. ENTIDAD: RESEÑA (Inferior Intermedio)                                  -->
  <!-- ========================================================================= -->
  <line x1="350" y1="425" x2="520" y2="675" class="conn-line" />
  <text x="440" y="580" class="card-text">(0, n)</text>

  <polygon points="520,675 570,705 520,735 470,705" class="rel-diamond" />
  <text x="520" y="705" class="rel-text">Valora</text>
  <text x="520" y="665" class="rel-ratio">1:N</text>

  <line x1="520" y1="735" x2="520" y2="830" class="conn-line" />
  <text x="530" y="785" class="card-text">(1, 1)</text>

  <rect x="460" y="830" width="120" height="50" class="entity-box" />
  <text x="520" y="855" class="entity-text">RESEÑA</text>

  <ellipse cx="460" cy="925" rx="38" ry="16" class="attr-ellipse" />
  <text x="460" y="925" class="attr-pk">id_resena</text>
  <line x1="475" y1="910" x2="495" y2="880" class="conn-line" />

  <ellipse cx="545" cy="925" rx="40" ry="16" class="attr-ellipse" />
  <text x="545" y="925" class="attr-text">Puntaje1_5</text>
  <line x1="540" y1="910" x2="530" y2="880" class="conn-line" />

  <!-- Conexión RESEÑA con PRODUCTO -->
  <path d="M 580 855 L 670 855 L 670 170 L 740 135" class="conn-line" stroke-dasharray="4,4" />
  <text x="680" y="500" class="card-text">(1, n) AplicaA</text>

  <!-- ========================================================================= -->
  <!-- 10. ENTIDAD: RESERVA (Extremo Derecho)                                    -->
  <!-- ========================================================================= -->
  <line x1="880" y1="125" x2="1140" y2="125" class="conn-line" />
  <text x="1000" y="115" class="card-text">(0, n)</text>

  <polygon points="1200,95 1255,125 1200,155 1145,125" class="rel-diamond" />
  <text x="1200" y="125" class="rel-text">Ofrece</text>
  <text x="1200" y="85" class="rel-ratio">1:N</text>

  <line x1="1255" y1="125" x2="1320" y2="125" class="conn-line" />
  <text x="1275" y="115" class="card-text">(1, 1)</text>

  <rect x="1250" y="180" width="130" height="50" class="entity-box" />
  <text x="1315" y="205" class="entity-text">RESERVA</text>
  <line x1="1315" y1="125" x2="1315" y2="180" class="conn-line" />

  <ellipse cx="1315" cy="275" rx="42" ry="17" class="attr-ellipse" />
  <text x="1315" y="275" class="attr-pk">id_reserva</text>
  <line x1="1315" y1="230" x2="1315" y2="258" class="conn-line" />

  <ellipse cx="1315" cy="320" rx="42" ry="17" class="attr-ellipse" />
  <text x="1315" y="320" class="attr-text">FechaCita</text>
  <line x1="1315" y1="292" x2="1315" y2="303" class="conn-line" />

  <!-- Leyenda Técnica Inferior Izquierda -->
  <rect x="40" y="990" width="360" height="80" fill="#F8FAFC" stroke="#000000" stroke-width="1.2" />
  <text x="55" y="1010" class="diagram-subtitle" font-weight="700">CONVENCIONES DEL MODELO CONCEPTUAL CHEN:</text>
  <text x="55" y="1030" class="diagram-subtitle">• Rectángulos: Entidades del sistema relacional.</text>
  <text x="55" y="1045" class="diagram-subtitle">• Rombos: Relaciones de negocio y cardinalidades (1:1, 1:N, N:M).</text>
  <text x="55" y="1060" class="diagram-subtitle">• Elipses: Atributos de entidad (Clave Primaria subrayada).</text>

</svg>`;

const outputPath = path.resolve('docs/diagrama-entidad-relacion-fusashop.svg');
fs.writeFileSync(outputPath, svgContent, 'utf-8');
console.log(`SVG del Diagrama E/R generado exitosamente en: ${outputPath}`);
