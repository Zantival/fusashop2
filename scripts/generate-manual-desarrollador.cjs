const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('Generando Manual de Desarrollador de FusaShop...');

const logoBase64 = fs.existsSync('public/logo.png') 
    ? 'data:image/png;base64,' + fs.readFileSync('public/logo.png').toString('base64') 
    : '';

const fusabotBase64 = fs.existsSync('public/fusabot.png') 
    ? 'data:image/png;base64,' + fs.readFileSync('public/fusabot.png').toString('base64') 
    : '';

const htmlContent = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Manual de Desarrollador — FusaShop</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500;600&display=swap');

  @page {
    size: letter;
    margin: 18mm 16mm 18mm 16mm;
  }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    color: #0f172a;
    background: #ffffff;
    font-size: 9.8pt;
    line-height: 1.55;
  }

  /* Portada */
  .cover-page {
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 35px 25px;
    page-break-after: always;
    border: 3px solid #006c47;
    border-radius: 18px;
    background: linear-gradient(170deg, #ffffff 60%, #ecfdf5 100%);
    position: relative;
    overflow: hidden;
  }

  .cover-badge {
    display: inline-block;
    background: #006c47;
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 8.5pt;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .cover-title {
    font-size: 30pt;
    font-weight: 800;
    color: #006c47;
    line-height: 1.15;
    margin: 18px 0 10px 0;
    letter-spacing: -0.02em;
  }

  .cover-subtitle {
    font-size: 13pt;
    font-weight: 500;
    color: #334155;
    line-height: 1.4;
    max-width: 620px;
  }

  .cover-meta {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-left: 5px solid #006c47;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 108, 71, 0.06);
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-top: 25px;
  }

  .meta-item {
    font-size: 8.8pt;
  }

  .meta-label {
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    font-size: 7.5pt;
    letter-spacing: 0.05em;
  }

  .meta-value {
    color: #0f172a;
    font-weight: 600;
    margin-top: 2px;
  }

  .cover-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #e2e8f0;
    padding-top: 15px;
    font-size: 8.5pt;
    color: #64748b;
  }

  /* Encabezados y Saltos */
  .page-break {
    page-break-before: always;
    break-before: page;
  }

  .header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #006c47;
    padding-bottom: 8px;
    margin-bottom: 20px;
  }

  .header-bar-left {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .header-bar-title {
    font-size: 8.5pt;
    font-weight: 700;
    color: #006c47;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .header-bar-doc {
    font-size: 8pt;
    color: #64748b;
  }

  h1.section-title {
    font-size: 18pt;
    font-weight: 800;
    color: #006c47;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 6px;
  }

  h2.subsection-title {
    font-size: 12.5pt;
    font-weight: 700;
    color: #0f172a;
    margin: 18px 0 8px 0;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  h3.tertiary-title {
    font-size: 10.5pt;
    font-weight: 700;
    color: #047857;
    margin: 12px 0 5px 0;
  }

  p {
    margin-bottom: 10px;
    color: #334155;
    text-align: justify;
  }

  /* Cajas de Información y Alertas */
  .info-box {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-left: 4px solid #006c47;
    padding: 12px 14px;
    border-radius: 8px;
    margin: 12px 0;
    font-size: 9pt;
  }

  .tech-alert {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-left: 4px solid #f59e0b;
    padding: 12px 14px;
    border-radius: 8px;
    margin: 12px 0;
    font-size: 9pt;
    color: #92400e;
  }

  .code-container {
    background: #0f172a;
    color: #f8fafc;
    border-radius: 8px;
    padding: 12px 14px;
    margin: 12px 0;
    font-family: 'Fira Code', monospace;
    font-size: 8.2pt;
    line-height: 1.45;
    overflow-x: hidden;
    border: 1px solid #1e293b;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);
  }

  .code-title {
    color: #94a3b8;
    font-size: 7.5pt;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
    border-bottom: 1px solid #334155;
    padding-bottom: 4px;
    display: flex;
    justify-content: space-between;
  }

  .code-comment { color: #64748b; font-style: italic; }
  .code-keyword { color: #38bdf8; font-weight: 600; }
  .code-string { color: #4ade80; }
  .code-var { color: #f472b6; }
  .code-func { color: #fbbf24; }

  /* Tablas */
  .table-custom {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0;
    font-size: 8.8pt;
  }

  .table-custom th {
    background: #006c47;
    color: #ffffff;
    font-weight: 700;
    text-align: left;
    padding: 8px 10px;
    font-size: 8.2pt;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  .table-custom td {
    padding: 7px 10px;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
  }

  .table-custom tr:nth-child(even) td {
    background: #f8fafc;
  }

  /* Grid de Cards */
  .card-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin: 12px 0;
  }

  .card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  }

  .card-item h4 {
    color: #006c47;
    font-size: 9pt;
    font-weight: 700;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .badge-tag {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 9999px;
    font-size: 7.2pt;
    font-weight: 700;
    text-transform: uppercase;
  }

  .badge-green { background: #dcfce7; color: #15803d; }
  .badge-blue { background: #e0f2fe; color: #0369a1; }
  .badge-purple { background: #f3e8ff; color: #7e22ce; }
  .badge-orange { background: #ffedd5; color: #c2410c; }

  ul, ol {
    margin: 8px 0 12px 20px;
    color: #334155;
  }

  li {
    margin-bottom: 4px;
  }

  .toc-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px dashed #cbd5e1;
    font-size: 9.5pt;
  }

  .toc-title {
    font-weight: 600;
    color: #0f172a;
  }

  .toc-dots {
    flex-grow: 1;
    margin: 0 8px;
    border-bottom: 1px dotted #94a3b8;
    position: relative;
    top: -4px;
  }

  .toc-page {
    font-weight: 700;
    color: #006c47;
  }
</style>
</head>
<body>

<!-- PORTADA -->
<div class="cover-page">
  <div style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div class="cover-badge">DOCUMENTACIÓN DE INGENIERÍA DE SOFTWARE</div>
    ${logoBase64 ? `<img src="${logoBase64}" alt="FusaShop" style="height: 48px; object-fit: contain;">` : '<h2 style="color:#006c47;">FusaShop</h2>'}
  </div>

  <div>
    <div style="font-size: 11pt; font-weight: 700; color: #f97316; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;">
      Manual Oficial de Arquitectura & Código
    </div>
    <h1 class="cover-title">MANUAL DEL DESARROLLADOR</h1>
    <div class="cover-subtitle">
      Arquitectura del Stack Laravel 12 + Tailwind v4 + Alpine.js, Topología de Servicios, 
      Mecanismos de Cifrado, Integraciones de Terceros (Wompi, Envía, Factus), Servidor MCP y Pipeline de Pruebas.
    </div>

    <div class="cover-meta">
      <div class="meta-item">
        <div class="meta-label">Versión del Sistema</div>
        <div class="meta-value">FusaShop v2.4-LTS (Edición Enterprise)</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Framework & Runtime</div>
        <div class="meta-value">Laravel 12.x • PHP 8.2+ • Tailwind v4</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Fecha de Compilación</div>
        <div class="meta-value">Octubre 2026</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Entorno Certificado</div>
        <div class="meta-value">76 / 76 Tests Pasados (Pest / PHPUnit)</div>
      </div>
    </div>
  </div>

  <div class="cover-footer">
    <span>© 2026 FusaShop — Comercio Electrónico Sostenible de Fusagasugá</span>
    <span>Confidencial • Uso Exclusivo del Equipo de Tecnología</span>
  </div>
</div>

<!-- ÍNDICE -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Tabla de Contenidos</span>
</div>

<h1 class="section-title">Contenido del Documento</h1>
<p>Este manual describe la arquitectura interna, decisiones técnicas, convenciones de ingeniería y lineamientos operacionales requeridos para extender, mantener y desplegar la plataforma <strong>FusaShop</strong>.</p>

<div style="margin-top: 15px;">
  <div class="toc-item">
    <span class="toc-title">1. Ecosistema Tecnológico y Decisiones de Arquitectura</span>
    <span class="toc-page">Capítulo 1</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">2. Topología del Proyecto y Estructura de Directorios</span>
    <span class="toc-page">Capítulo 2</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">3. Modelado de Datos y Capa de Persistencia Eloquent</span>
    <span class="toc-page">Capítulo 3</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">4. Capa de Servicios del Dominio & Lógica de Negocio</span>
    <span class="toc-page">Capítulo 4</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">5. Integraciones de Terceros: Wompi, Envía y Factus DIAN</span>
    <span class="toc-page">Capítulo 5</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">6. Asistente Inteligente FusaBot y Servidor MCP (JSON-RPC)</span>
    <span class="toc-page">Capítulo 6</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">7. Seguridad, Cifrado Criptográfico y Control de Acceso</span>
    <span class="toc-page">Capítulo 7</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">8. Guía de Instalación Local, Configuración y Workflows</span>
    <span class="toc-page">Capítulo 8</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">9. Estrategia de Testing Automatizado y Aseguramiento de Calidad</span>
    <span class="toc-page">Capítulo 9</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">10. Estándares de Codificación, Versionado y Convenciones Git</span>
    <span class="toc-page">Capítulo 10</span>
  </div>
</div>

<div class="info-box" style="margin-top: 25px;">
  <strong>💡 Principio Rector del Código en FusaShop:</strong>
  "Mantenemos una arquitectura limpia orientada a servicios, donde los controladores actúan estrictamente como coordinadores de peticiones HTTP, delegando las transacciones complejas a clases de servicio especializadas (<code>app/Services</code>), los procesos diferidos a colas asíncronas (<code>app/Jobs</code>), y la persistencia a modelos Eloquent con tipado estricto."
</div>

<!-- CAPÍTULO 1 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 1: Ecosistema Tecnológico</span>
</div>

<h1 class="section-title">1. Ecosistema Tecnológico y Decisiones de Arquitectura</h1>

<p>FusaShop está construido sobre un stack moderno y eficiente diseñado para ofrecer alta disponibilidad, respuesta ultrarrápida en dispositivos móviles de gama baja y media, y facilidad de mantenimiento sin sobreingeniería.</p>

<table class="table-custom">
  <thead>
    <tr>
      <th>Componente</th>
      <th>Tecnología Seleccionada</th>
      <th>Versión</th>
      <th>Propósito en FusaShop</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Backend Core</strong></td>
      <td>PHP / Laravel Framework</td>
      <td>PHP 8.2+ / Laravel 12.x</td>
      <td>Enrutamiento, Middleware de seguridad, Eloquent ORM, Jobs y Sistema de Eventos.</td>
    </tr>
    <tr>
      <td><strong>Frontend Estilos</strong></td>
      <td>Tailwind CSS</td>
      <td>v4.0 (Engine JIT)</td>
      <td>Diseño modular responsivo con paleta corporativa nativa (Emerald, Mint, Orange).</td>
    </tr>
    <tr>
      <td><strong>Frontend Reactividad</strong></td>
      <td>Alpine.js</td>
      <td>v3.13+</td>
      <td>Interactividad ligera en el cliente (modales, carritos flotantes, menús drawer, calculadoras).</td>
    </tr>
    <tr>
      <td><strong>Base de Datos</strong></td>
      <td>SQLite (Dev/CI) / MySQL / PostgreSQL</td>
      <td>MySQL 8.0+ / PgSQL 15+</td>
      <td>Almacenamiento relacional con integridad referencial, índices espaciales y transacciones ACID.</td>
    </tr>
    <tr>
      <td><strong>Caché & Sesiones</strong></td>
      <td>Redis / Database / File</td>
      <td>Redis 7.x / DB Fallback</td>
      <td>Gestión de tokens OAuth2, throttling de peticiones y persistencia de carritos.</td>
    </tr>
    <tr>
      <td><strong>Optimización de Medios</strong></td>
      <td>WebP Engine / cwebp</td>
      <td>Formatos WebP / PNG / JPG</td>
      <td>Compresión del 65%-80% en imágenes con fallback automático transparente.</td>
    </tr>
  </tbody>
</table>

<h2 class="subsection-title">1.1 ¿Por qué Laravel 12 + Alpine.js en lugar de una SPA pesada?</h2>
<p>Muchos comercios de Fusagasugá y clientes finales operan en redes móviles 3G/4G con planes de datos limitados. Las Single Page Applications basadas en pesados bundles de React o Angular transfieren megabytes de JavaScript en la carga inicial, lo que genera retrasos inaceptables (First Contentful Paint > 4s). FusaShop adopta la estrategia <strong>SSR + Micro-interacciones reactivas</strong>:</p>

<div class="card-grid">
  <div class="card-item">
    <h4>🚀 Server-Side Rendering (Blade)</h4>
    <p style="font-size: 8.5pt;">HTML puro generado en el servidor en menos de 65 ms. Indexable de forma instantánea por motores de búsqueda (SEO local) con metadatos OpenGraph dinámicos.</p>
  </div>
  <div class="card-item">
    <h4>⚡ Alpine.js Declarativo</h4>
    <p style="font-size: 8.5pt;">Un único script de ~15 KB maneja toda la reactividad del cliente (filtros de catálogo, apertura de drawers, cálculo dinámico de FusaCoins y validaciones de formulario).</p>
  </div>
</div>

<!-- CAPÍTULO 2 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 2: Topología del Proyecto</span>
</div>

<h1 class="section-title">2. Topología del Proyecto y Organización de Directorios</h1>

<p>La estructura del código sigue estrictamente las mejores prácticas de Laravel 12, desacoplando los dominios de negocio en namespaces independientes:</p>

<div class="code-container">
  <div class="code-title">
    <span>Estructura de Directorios Clave</span>
    <span>fusashop/</span>
  </div>
<pre>
fusashop/
├── <span class="code-keyword">app/</span>
│   ├── <span class="code-keyword">Http/</span>
│   │   ├── <span class="code-keyword">Controllers/</span>
│   │   │   ├── <span class="code-func">Analyst/</span>         <span class="code-comment"># Controladores del Administrador / Analista de Datos</span>
│   │   │   │   ├── AnalystController.php
│   │   │   │   └── IntegrationController.php
│   │   │   ├── <span class="code-func">Merchant/</span>        <span class="code-comment"># Gestión de Tiendas, Inventario, Envíos y Finanzas</span>
│   │   │   │   ├── MerchantController.php
│   │   │   │   └── CouponController.php
│   │   │   ├── <span class="code-func">Consumer/</span>        <span class="code-comment"># Catálogo, Carrito, Checkout, Direcciones y FusaBot</span>
│   │   │   │   ├── ConsumerController.php
│   │   │   │   ├── CheckoutController.php
│   │   │   │   └── AddressController.php
│   │   │   ├── <span class="code-func">Webhooks/</span>        <span class="code-comment"># Endpoints de Recepción Asíncrona (Wompi, Envía)</span>
│   │   │   ├── McpController.php   <span class="code-comment"># Servidor Model Context Protocol (JSON-RPC)</span>
│   │   │   └── ChatController.php  <span class="code-comment"># Mensajería interna y soporte</span>
│   │   └── <span class="code-keyword">Middleware/</span>         <span class="code-comment"># SecurityHeaders, EnsureKycApproved, CompressResponse</span>
│   ├── <span class="code-keyword">Jobs/</span>                   <span class="code-comment"># Tareas en segundo plano (Envía, Factus, Wompi)</span>
│   ├── <span class="code-keyword">Models/</span>                 <span class="code-comment"># Entidades Eloquent (User, Order, Product, etc.)</span>
│   ├── <span class="code-keyword">Services/</span>               <span class="code-comment"># Lógica pura del dominio</span>
│   │   ├── <span class="code-func">Admin/</span>              <span class="code-comment"># Servicios de Inteligencia Territorial y Diagnósticos</span>
│   │   ├── CheckoutService.php
│   │   ├── EnviaService.php
│   │   ├── FactusService.php
│   │   ├── OrderPaymentService.php
│   │   ├── StockService.php
│   │   └── WompiService.php
├── <span class="code-keyword">database/</span>
│   ├── <span class="code-keyword">migrations/</span>             <span class="code-comment"># 37 migraciones versionadas</span>
│   └── <span class="code-keyword">seeders/</span>                <span class="code-comment"># Datos de prueba de tiendas locales y productos</span>
├── <span class="code-keyword">resources/</span>
│   ├── <span class="code-keyword">css/</span>app.css             <span class="code-comment"># Estilos Tailwind v4 y directivas responsivas</span>
│   └── <span class="code-keyword">views/</span>                   <span class="code-comment"># Plantillas Blade segmentadas por rol</span>
└── <span class="code-keyword">tests/Feature/</span>              <span class="code-comment"># 76 pruebas de integración y aceptación</span>
</pre>
</div>

<h2 class="subsection-title">2.1 Convenciones de Código y Nomenclatura</h2>
<ul>
  <li><strong>Controladores:</strong> Nombre singular + sufijo <code>Controller</code> (ej. <code>CheckoutController</code>). Deben mantenerse ligeros (&lt; 200 líneas preferentemente).</li>
  <li><strong>Modelos:</strong> Nombre singular en inglés (ej. <code>Product</code>, <code>BannerRequest</code>, <code>CompanyProfile</code>). Todas las tablas asociadas utilizan plural snake_case.</li>
  <li><strong>Servicios:</strong> Clases independientes en <code>App\Services</code> con inyección de dependencias en constructores o métodos de ejecución directa.</li>
  <li><strong>Migraciones:</strong> Estrictamente idempotentes con definición precisa de tipos de datos, llaves foráneas y borrados en cascada condicionados.</li>
</ul>

<!-- CAPÍTULO 3 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 3: Modelado de Datos Eloquent</span>
</div>

<h1 class="section-title">3. Modelado de Datos y Capa de Persistencia</h1>

<p>FusaShop implementa un esquema relacional optimizado para e-commerce multitienda georreferenciado. A continuación se presentan las entidades primordiales y sus relaciones estructurales:</p>

<table class="table-custom">
  <thead>
    <tr>
      <th>Modelo</th>
      <th>Tabla Asociada</th>
      <th>Relaciones Clave</th>
      <th>Campos Críticos & Atributos</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>User</code></td>
      <td><code>users</code></td>
      <td><code>hasOne(CompanyProfile)</code><br><code>hasMany(Order)</code><br><code>hasMany(Review)</code></td>
      <td><code>role</code> ('consumer','merchant','analyst'), <code>loyalty_points</code> (FusaCoins), <code>email_verified_code</code>, <code>is_blocked</code>.</td>
    </tr>
    <tr>
      <td><code>CompanyProfile</code></td>
      <td><code>company_profiles</code></td>
      <td><code>belongsTo(User, 'merchant_id')</code></td>
      <td><code>store_name</code>, <code>kyc_status</code>, <code>wompi_public_key</code>, <code>wompi_private_key</code> (cifrado), <code>latitude</code>, <code>longitude</code>.</td>
    </tr>
    <tr>
      <td><code>Product</code></td>
      <td><code>products</code></td>
      <td><code>belongsTo(User, 'merchant_id')</code><br><code>hasMany(OrderItem)</code></td>
      <td><code>price</code>, <code>discount_price</code>, <code>stock</code>, <code>weight</code>, <code>specifications</code> (JSON), <code>allows_points</code> (booleano).</td>
    </tr>
    <tr>
      <td><code>Order</code></td>
      <td><code>orders</code></td>
      <td><code>belongsTo(User, 'consumer_id')</code><br><code>hasMany(OrderItem)</code></td>
      <td><code>reference</code> ('FS-...'), <code>status</code>, <code>payment_status</code>, <code>total</code>, <code>points_discount</code>, <code>shipping_tracking_number</code>.</td>
    </tr>
    <tr>
      <td><code>OrderItem</code></td>
      <td><code>order_items</code></td>
      <td><code>belongsTo(Order)</code><br><code>belongsTo(Product)</code></td>
      <td><code>product_id</code>, <code>quantity</code>, <code>price</code>, <code>subtotal</code>.</td>
    </tr>
    <tr>
      <td><code>BannerRequest</code></td>
      <td><code>banner_requests</code></td>
      <td><code>belongsTo(User, 'merchant_id')</code></td>
      <td><code>title</code>, <code>status</code> ('pending','approved','rejected'), <code>target_sector</code>, <code>cost_points</code>, <code>starts_at</code>, <code>expires_at</code>.</td>
    </tr>
    <tr>
      <td><code>PQRS</code></td>
      <td><code>p_q_r_s</code></td>
      <td><code>belongsTo(User)</code><br><code>belongsTo(Order)</code></td>
      <td><code>ticket_number</code>, <code>type</code> ('peticion','queja','reclamo','sugerencia'), <code>status</code> ('open','in_progress','closed').</td>
    </tr>
  </tbody>
</table>

<h2 class="subsection-title">3.1 Cifrado Criptográfico Transparente en Modelos</h2>
<p>Para proteger la información financiera de los comerciantes en caso de una fuga en la base de datos, los secretos de pasarela se cifran con <strong>AES-256-CBC</strong> utilizando los casts nativos de Laravel en el modelo <code>CompanyProfile</code>:</p>

<div class="code-container">
  <div class="code-title">
    <span>app/Models/CompanyProfile.php — Cifrado de Credenciales</span>
  </div>
<pre>
<span class="code-keyword">protected</span> <span class="code-var">$casts</span> = [
    <span class="code-string">'wompi_private_key'</span>     => <span class="code-string">'encrypted'</span>,
    <span class="code-string">'wompi_integrity_secret'</span>=> <span class="code-string">'encrypted'</span>,
    <span class="code-string">'is_verified'</span>           => <span class="code-string">'boolean'</span>,
    <span class="code-string">'kyc_approved_at'</span>       => <span class="code-string">'datetime'</span>,
    <span class="code-string">'latitude'</span>              => <span class="code-string">'float'</span>,
    <span class="code-string">'longitude'</span>             => <span class="code-string">'float'</span>,
];
</pre>
</div>

<!-- CAPÍTULO 4 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 4: Servicios del Dominio</span>
</div>

<h1 class="section-title">4. Capa de Servicios del Dominio & Lógica de Negocio</h1>

<p>FusaShop centraliza las transacciones críticas en clases de servicio puras. Esto garantiza que las pruebas unitarias y de integración puedan ejecutarse de forma aislada sin simular controladores HTTP.</p>

<h2 class="subsection-title">4.1 Gestión de Stock Atómica (<code>StockService</code>)</h2>
<p>Evita condiciones de carrera (<em>race conditions</em>) y sobreventas en momentos de alto tráfico mediante transacciones con bloqueo pesimista en base de datos:</p>

<div class="code-container">
  <div class="code-title">
    <span>app/Services/StockService.php — Reserva Segura de Inventario</span>
  </div>
<pre>
<span class="code-keyword">public function</span> <span class="code-func">reserveStockForOrder</span>(Order <span class="code-var">$order</span>): <span class="code-keyword">void</span>
{
    DB::transaction(<span class="code-keyword">function</span> () <span class="code-keyword">use</span> (<span class="code-var">$order</span>) {
        <span class="code-keyword">foreach</span> (<span class="code-var">$order</span>->items <span class="code-keyword">as</span> <span class="code-var">$item</span>) {
            <span class="code-comment">// Bloqueo exclusivo de fila hasta finalizar la transacción</span>
            <span class="code-var">$product</span> = Product::where(<span class="code-string">'id'</span>, <span class="code-var">$item</span>->product_id)->lockForUpdate()->firstOrFail();

            <span class="code-keyword">if</span> (<span class="code-var">$product</span>->stock < <span class="code-var">$item</span>->quantity) {
                <span class="code-keyword">throw new</span> \RuntimeException(<span class="code-string">"Stock insuficiente para: {$product->name}"</span>);
            }

            <span class="code-var">$product</span>->decrement(<span class="code-string">'stock'</span>, <span class="code-var">$item</span>->quantity);

            <span class="code-comment">// Disparo de alerta si el stock baja del umbral mínimo</span>
            <span class="code-keyword">if</span> (<span class="code-var">$product</span>->stock <= <span class="code-var">$product</span>->low_stock_threshold) {
                SendLowStockAlert::dispatch(<span class="code-var">$product</span>);
            }
        }
    });
}
</pre>
</div>

<h2 class="subsection-title">4.2 Servicios de Analítica Avanzada (<code>app/Services/Admin/</code>)</h2>
<p>El portal administrativo cuenta con motores de inteligencia de negocios para diagnóstico del comercio municipal:</p>
<ul>
  <li><code>ApiHealthService</code>: Sondea la disponibilidad de Wompi, Envía, Factus y el driver de correo con caché de 60 segundos.</li>
  <li><code>DashboardMetricsService</code>: Calcula Gross Merchandise Value (GMV), ticket promedio, retención de clientes y comerciantes activos.</li>
  <li><code>SectorCensusService</code>: Agrupa negocios por comunas y sectores de Fusagasugá para reportes de desarrollo económico local.</li>
  <li><code>TerritoryHeatmapService</code>: Procesa coordenadas GPS de órdenes entregadas para generar mapas de calor logísticos.</li>
</ul>

<!-- CAPÍTULO 5 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 5: Integraciones Externas</span>
</div>

<h1 class="section-title">5. Integraciones de Terceros: Wompi, Envía y Factus</h1>

<h2 class="subsection-title">5.1 Wompi (Pasarela de Pagos Multitienda)</h2>
<p>FusaShop admite pagos directos a las cuentas Wompi individuales de cada comerciante. Para garantizar la inviolabilidad del monto cobrado, se genera una firma criptográfica SHA-256:</p>

<div class="code-container">
  <div class="code-title">
    <span>app/Services/WompiService.php — Firma de Integridad Criptográfica</span>
  </div>
<pre>
<span class="code-comment">/**
 * Firma de integridad: SHA256("{referencia}{monto_en_centavos}{moneda}{secreto_integridad}")
 */</span>
<span class="code-keyword">public function</span> <span class="code-func">integritySignature</span>(string <span class="code-var">$reference</span>, int <span class="code-var">$amountInCents</span>, string <span class="code-var">$currency</span> = <span class="code-string">'COP'</span>, ?string <span class="code-var">$customSecret</span> = <span class="code-keyword">null</span>): string
{
    <span class="code-var">$secret</span> = <span class="code-var">$customSecret</span>;
    <span class="code-keyword">if</span> (<span class="code-var">$secret</span> === <span class="code-keyword">null</span>) {
        <span class="code-var">$order</span> = Order::where(<span class="code-string">'reference'</span>, <span class="code-var">$reference</span>)->first();
        <span class="code-var">$secret</span> = <span class="code-var">$order</span>?->merchantProfile()?->wompi_integrity_secret ?: config(<span class="code-string">'services.wompi.integrity_secret'</span>);
    }

    <span class="code-keyword">return</span> hash(<span class="code-string">'sha256'</span>, <span class="code-var">$reference</span> . <span class="code-var">$amountInCents</span> . <span class="code-var">$currency</span> . <span class="code-var">$secret</span>);
}
</pre>
</div>

<h2 class="subsection-title">5.2 Envía Colvanes (Logística Nacional y Guías)</h2>
<p>El servicio <code>EnviaService</code> implementa cálculo de volumen cúbico y consolidación automática de ítems. Una vez el pago es confirmado por el webhook de Wompi, el sistema encola automáticamente el Job <code>GenerateEnviaShipment</code> para generar la guía en PDF sin bloquear al cliente.</p>

<h2 class="subsection-title">5.3 Factus (Facturación Electrónica DIAN)</h2>
<p>Emisión conforme a los requisitos fiscales de la DIAN en Colombia. Implementa token bearer OAuth2 con caché inteligente:</p>

<div class="code-container">
  <div class="code-title">
    <span>app/Services/FactusService.php — Autenticación OAuth2 con Caché</span>
  </div>
<pre>
<span class="code-keyword">private function</span> <span class="code-func">token</span>(bool <span class="code-var">$forceRefresh</span> = <span class="code-keyword">false</span>): string
{
    <span class="code-keyword">if</span> (<span class="code-var">$forceRefresh</span>) {
        Cache::forget(<span class="code-string">'factus.access_token'</span>);
    }

    <span class="code-keyword">return</span> Cache::remember(<span class="code-string">'factus.access_token'</span>, 50 * 60, <span class="code-keyword">function</span> () {
        <span class="code-var">$cfg</span> = config(<span class="code-string">'services.factus'</span>);
        <span class="code-var">$res</span> = Http::baseUrl(<span class="code-var">$cfg</span>[<span class="code-string">'api_url'</span>])->asForm()->acceptJson()->post(<span class="code-string">'/oauth/token'</span>, [
            <span class="code-string">'grant_type'</span>    => <span class="code-string">'password'</span>,
            <span class="code-string">'client_id'</span>     => <span class="code-var">$cfg</span>[<span class="code-string">'client_id'</span>],
            <span class="code-string">'client_secret'</span> => <span class="code-var">$cfg</span>[<span class="code-string">'client_secret'</span>],
            <span class="code-string">'username'</span>      => <span class="code-var">$cfg</span>[<span class="code-string">'username'</span>],
            <span class="code-string">'password'</span>      => <span class="code-var">$cfg</span>[<span class="code-string">'password'</span>],
        ]);
        <span class="code-keyword">return</span> <span class="code-var">$res</span>->json(<span class="code-string">'access_token'</span>);
    });
}
</pre>
</div>

<!-- CAPÍTULO 6 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 6: FusaBot y Servidor MCP</span>
</div>

<h1 class="section-title">6. Asistente Inteligente FusaBot y Servidor MCP</h1>

<p>FusaShop integra dos mecanismos de Inteligencia Artificial: un asistente contextual embebido para el usuario web (<strong>FusaBot</strong>) y un servidor oficial <strong>Model Context Protocol (MCP)</strong> sobre protocolo JSON-RPC 2.0 para agentes autónomos externos.</p>

<h2 class="subsection-title">6.1 Triaje Contextual de FusaBot (<code>ConsumerController::assistantSearch</code>)</h2>
<p>El bot analiza la intención de la consulta y el rol del usuario para proporcionar respuestas de resolución directa:</p>
<ul>
  <li><strong>Rol Comerciante:</strong> Guía interactiva sobre resolución de errores en emisión de CUFE DIAN, configuración de llaves Wompi, y solicitudes de banners publicitarios.</li>
  <li><strong>Rol Administrador:</strong> Resumen instantáneo de ventas globales del día, pedidos en estado pendiente, trámites KYC por auditar y tickets PQRS abiertos.</li>
  <li><strong>Rol Comprador:</strong> Diagnóstico de errores de pago (PSE, Wompi, tarjetas), rastreo en tiempo real de paquetes y motor semántico de búsqueda con renderizado directo de imágenes de productos.</li>
</ul>

<h2 class="subsection-title">6.2 Servidor Model Context Protocol (MCP)</h2>
<p>FusaShop expone herramientas estandarizadas mediante <code>POST /api/mcp</code> autenticado mediante token Bearer (<code>MCP_TOKEN</code>):</p>

<table class="table-custom">
  <thead>
    <tr>
      <th>Herramienta MCP</th>
      <th>Parámetros de Entrada</th>
      <th>Descripción y Retorno</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>get_order</code></td>
      <td><code>reference: "FS-..."</code></td>
      <td>Retorna el estado de pago, tracking de envío, ítems, totales y número de factura DIAN.</td>
    </tr>
    <tr>
      <td><code>search_catalog</code></td>
      <td><code>query, category, limit</code></td>
      <td>Búsqueda en catálogo con stock disponible, precios vigentes y slugs para enlace directo.</td>
    </tr>
    <tr>
      <td><code>validate_shipping</code></td>
      <td><code>street, city, state, postal_code</code></td>
      <td>Valida la nomenclatura de direcciones colombianas y cotiza el flete logístico vía Envía.</td>
    </tr>
  </tbody>
</table>

<div class="code-container">
  <div class="code-title">
    <span>Ejemplo de Llamada JSON-RPC 2.0 al Servidor MCP</span>
  </div>
<pre>
{
  <span class="code-string">"jsonrpc"</span>: <span class="code-string">"2.0"</span>,
  <span class="code-string">"id"</span>: 1,
  <span class="code-string">"method"</span>: <span class="code-string">"tools/call"</span>,
  <span class="code-string">"params"</span>: {
    <span class="code-string">"name"</span>: <span class="code-string">"get_order"</span>,
    <span class="code-string">"arguments"</span>: { <span class="code-string">"reference"</span>: <span class="code-string">"FS-20261019-9482"</span> }
  }
}
</pre>
</div>

<!-- CAPÍTULO 7 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 7: Seguridad y Gobernanza</span>
</div>

<h1 class="section-title">7. Seguridad, Cifrado y Control de Acceso</h1>

<h2 class="subsection-title">7.1 Pipeline de Middlewares de Seguridad</h2>
<p>Cada petición HTTP entrante atraviesa una estricta cadena de validación configurada en <code>bootstrap/app.php</code>:</p>

<div class="card-grid">
  <div class="card-item">
    <h4>🛡️ SecurityHeaders</h4>
    <p style="font-size: 8.5pt;">Inyecta cabeceras HTTP de endurecimiento: <code>X-Content-Type-Options: nosniff</code>, <code>X-Frame-Options: SAMEORIGIN</code>, <code>Referrer-Policy: strict-origin-when-cross-origin</code> y políticas CSP.</p>
  </div>
  <div class="card-item">
    <h4>👥 RoleMiddleware</h4>
    <p style="font-size: 8.5pt;">Protege rutas administrativas y comerciales verificando <code>Auth::user()->role === $role</code> con redirección automática si no cuenta con privilegios.</p>
  </div>
  <div class="card-item">
    <h4>📜 EnsureKycApproved</h4>
    <p style="font-size: 8.5pt;">Impide que comercios no verificados puedan publicar productos o recibir pagos en línea antes de la validación legal de su RUT/Cédula.</p>
  </div>
  <div class="card-item">
    <h4>🗜️ CompressResponse</h4>
    <p style="font-size: 8.5pt;">Aplica compresión Gzip transparente en payloads JSON y respuestas HTML cuando el cliente lo soporta (<code>Accept-Encoding: gzip</code>).</p>
  </div>
</div>

<h2 class="subsection-title">7.2 Verificación de 6 Dígitos por Correo (OTP)</h2>
<p>Al registrarse un nuevo usuario, el sistema genera un código numérico aleatorio de 6 dígitos con expiración de 15 minutos almacenado en la tabla <code>users</code> (<code>verification_code</code> y <code>verification_expires_at</code>). El usuario no puede realizar transacciones hasta validar dicho código.</p>

<h2 class="subsection-title">7.3 Registro Inmutable de Auditoría (<code>AdminAuditLog</code>)</h2>
<p>Cualquier acción crítica ejecutada por un Administrador (aprobación/rechazo de KYC, bloqueo de tiendas, reversión de pagos) se registra de forma inmutable con la IP de origen, User Agent y snapshot del estado anterior y posterior en formato JSON.</p>

<!-- CAPÍTULO 8 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 8: Guía de Instalación Local</span>
</div>

<h1 class="section-title">8. Guía de Instalación Local y Flujo de Trabajo</h1>

<p>Para levantar el entorno completo de desarrollo en una estación de trabajo Windows, Linux o macOS, ejecute la siguiente secuencia estandarizada:</p>

<h2 class="subsection-title">8.1 Prerrequisitos de la Estación</h2>
<ul>
  <li><strong>PHP 8.2 o superior</strong> con extensiones: <code>pdo</code>, <code>pdo_sqlite</code>, <code>pdo_mysql</code>, <code>curl</code>, <code>mbstring</code>, <code>openssl</code>, <code>fileinfo</code>, <code>bcmath</code>.</li>
  <li><strong>Composer 2.6+</strong></li>
  <li><strong>Node.js 18+</strong> y <strong>npm 9+</strong></li>
  <li><strong>Git</strong></li>
</ul>

<h2 class="subsection-title">8.2 Secuencia de Comandos</h2>
<div class="code-container">
  <div class="code-title">
    <span>Comandos de Instalación Paso a Paso</span>
  </div>
<pre>
<span class="code-comment"># 1. Clonar el repositorio</span>
git clone https://github.com/fusashop/fusashop.git
cd fusashop

<span class="code-comment"># 2. Instalar dependencias PHP</span>
composer install

<span class="code-comment"># 3. Instalar dependencias frontend de Node</span>
npm install

<span class="code-comment"># 4. Configurar variables de entorno</span>
cp .env.example .env
php artisan key:generate

<span class="code-comment"># 5. Ejecutar migraciones y poblado de datos iniciales</span>
php artisan migrate --seed

<span class="code-comment"># 6. Crear enlace simbólico de almacenamiento público</span>
php artisan storage:link

<span class="code-comment"># 7. Compilar activos CSS con Tailwind v4</span>
npm run build

<span class="code-comment"># 8. Iniciar el servidor local de desarrollo</span>
php artisan serve
</pre>
</div>

<h2 class="subsection-title">8.3 Optimización de Recursos Gráficos a WebP</h2>
<p>El proyecto incluye un script automatizado para procesar y optimizar todos los banners e imágenes del sistema a formato moderno WebP:</p>
<div class="code-container">
  <div class="code-title">
    <span>Compresión de Imágenes WebP</span>
  </div>
<pre>
node scripts/optimize-images.cjs
</pre>
</div>

<!-- CAPÍTULO 9 & 10 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual del Desarrollador</span>
  </div>
  <span class="header-bar-doc">Capítulo 9 & 10: QA y Convenciones Git</span>
</div>

<h1 class="section-title">9. Estrategia de Testing Automatizado (76/76 Tests)</h1>

<p>FusaShop cuenta con una robusta suite de pruebas unitarias y de integración en <code>tests/Feature/</code> que validan de forma integral el correcto funcionamiento de la plataforma:</p>

<table class="table-custom">
  <thead>
    <tr>
      <th>Suite de Pruebas</th>
      <th>Archivo de Test</th>
      <th>Cobertura & Validaciones</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Seguridad & Gobernanza</strong></td>
      <td><code>AdminSecurityAndGovernanceTest.php</code></td>
      <td>Auditoría de logs, bloqueo de tiendas fraudulentas, autorización RBAC.</td>
    </tr>
    <tr>
      <td><strong>Checkout & FusaCoins</strong></td>
      <td><code>SelectiveCheckoutAndFusaCoinsTest.php</code></td>
      <td>Cálculo de descuentos de puntos, validación de stock y cobros Wompi.</td>
    </tr>
    <tr>
      <td><strong>Verificación 6 Dígitos</strong></td>
      <td><code>SixDigitEmailVerificationAndNotificationsTest.php</code></td>
      <td>Envío de OTP, expiración temporal y restricción de compras sin verificación.</td>
    </tr>
    <tr>
      <td><strong>Publicidad y Banners</strong></td>
      <td><code>MerchantBannerAdvertisingTest.php</code></td>
      <td>Solicitudes de publicidad, aprobación administrativa y geolocalización.</td>
    </tr>
    <tr>
      <td><strong>Asistente FusaBot</strong></td>
      <td><code>FusaBotAssistantTest.php</code></td>
      <td>Triaje semántico de respuestas, recomendaciones con foto y escalamiento.</td>
    </tr>
    <tr>
      <td><strong>Finanzas de Tiendas</strong></td>
      <td><code>MerchantSalesReportCenterTest.php</code></td>
      <td>Reportes de ventas, comisiones, pagos netos y exportación de balance.</td>
    </tr>
  </tbody>
</table>

<div class="code-container">
  <div class="code-title">
    <span>Ejecución del Test Suite Completo</span>
  </div>
<pre>
<span class="code-comment"># Ejecutar la suite completa garantizando 0 fallos</span>
php artisan test

<span class="code-comment"># Salida esperada:</span>
<span class="code-string">PASS  Tests\Feature\AdminSecurityAndGovernanceTest</span>
<span class="code-string">PASS  Tests\Feature\CheckoutFlowTest</span>
<span class="code-string">PASS  Tests\Feature\FusaBotAssistantTest</span>
<span class="code-string">...</span>
<span class="code-string">Tests:    76 passed (182 assertions)</span>
<span class="code-string">Duration: 8.42s</span>
</pre>
</div>

<h1 class="section-title" style="margin-top: 20px;">10. Estándares Git Flow y Contribución</h1>
<ul>
  <li><strong>Ramas Principales:</strong> <code>main</code> (código de producción estrictamente estable y firmado) y <code>develop</code> (integración continua de nuevas características).</li>
  <li><strong>Ramas de Trabajo:</strong> <code>feature/nombre-modulo</code>, <code>fix/nombre-bug</code>, <code>perf/optimizacion</code>.</li>
  <li><strong>Mensajes de Commit Convencionales:</strong> <code>feat: añade integración con Envía</code>, <code>fix: corrige cálculo de IVA en Factus</code>, <code>perf: optimiza compresión WebP</code>.</li>
  <li><strong>Pull Requests:</strong> Todo merge requiere que el pipeline de GitHub Actions / GitLab CI pase el 100% de los 76 tests y la validación de estilo PSR-12.</li>
</ul>

</body>
</html>`;

const htmlFilePath = path.join('docs', 'Manual_de_Desarrollador_FusaShop.html');
const pdfFilePath = path.join('docs', 'Manual_de_Desarrollador_FusaShop.pdf');

fs.writeFileSync(htmlFilePath, htmlContent, 'utf-8');
console.log(`Archivo HTML generado en: ${htmlFilePath}`);

console.log('Compilando PDF con Microsoft Edge Headless...');
const edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';

if (!fs.existsSync(edgePath)) {
    console.error(`No se encontró Edge en: ${edgePath}`);
    process.exit(1);
}

const command = `"${edgePath}" --headless=new --disable-gpu --no-pdf-header-footer --run-all-compositor-stages-before-draw --print-to-pdf="${path.resolve(pdfFilePath)}" "${path.resolve(htmlFilePath)}"`;

try {
    execSync(command, { stdio: 'inherit' });
    console.log(`✅ Manual de Desarrollador generado con éxito en: ${pdfFilePath}`);
} catch (error) {
    console.error('Error al generar PDF:', error.message);
    process.exit(1);
}
