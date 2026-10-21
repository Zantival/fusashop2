const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('Generando Manual Técnico de FusaShop...');

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
<title>Manual Técnico de Sistemas — FusaShop</title>
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
    background: linear-gradient(170deg, #ffffff 60%, #f0fdf4 100%);
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

  /* Diagramas Arquitectónicos */
  .diagram-layer {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 8px;
    position: relative;
  }

  .diagram-layer-title {
    font-size: 8pt;
    font-weight: 800;
    text-transform: uppercase;
    color: #006c47;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
  }

  .diagram-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  .chip {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 7.8pt;
    font-weight: 600;
    color: #334155;
    font-family: 'Fira Code', monospace;
  }

  .chip-accent {
    background: #ecfdf5;
    border-color: #10b981;
    color: #047857;
  }

  .chip-orange {
    background: #fff7ed;
    border-color: #f97316;
    color: #c2410c;
  }

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
    <div class="cover-badge">DOCUMENTO DE ARQUITECTURA & ESPECIFICACIÓN TÉCNICA</div>
    ${logoBase64 ? `<img src="${logoBase64}" alt="FusaShop" style="height: 48px; object-fit: contain;">` : '<h2 style="color:#006c47;">FusaShop</h2>'}
  </div>

  <div>
    <div style="font-size: 11pt; font-weight: 700; color: #f97316; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;">
      Referencia Integral de Sistemas & Operaciones
    </div>
    <h1 class="cover-title">MANUAL TÉCNICO DE SISTEMAS</h1>
    <div class="cover-subtitle">
      Arquitectura Multicapa, Diccionario de Datos Relacional, Máquinas de Estados Transaccionales, 
      Mecanismos Criptográficos de Seguridad, Optimización de Alto Rendimiento e Infraestructura en Producción.
    </div>

    <div class="cover-meta">
      <div class="meta-item">
        <div class="meta-label">Versión de Infraestructura</div>
        <div class="meta-value">FusaShop Core v2.4-Production</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Estándar de Seguridad</div>
        <div class="meta-value">AES-256-CBC • Bcrypt • OWASP Top 10</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Auditoría & Trazabilidad</div>
        <div class="meta-value">AdminAuditLogs • IntegrationLogs HMAC</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Garantía de Rendimiento</div>
        <div class="meta-value">WebP Pipeline • Gzip • Zero N+1 Queries</div>
      </div>
    </div>
  </div>

  <div class="cover-footer">
    <span>© 2026 FusaShop — Alcaldía de Fusagasugá & Secretaría de Desarrollo Económico</span>
    <span>Documento Técnico Oficial • Nivel de Sensibilidad: Confidencial</span>
  </div>
</div>

<!-- ÍNDICE -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual Técnico de Sistemas</span>
  </div>
  <span class="header-bar-doc">Tabla de Contenidos</span>
</div>

<h1 class="section-title">Contenido del Manual Técnico</h1>
<p>Este documento es la referencia técnica definitiva para Administradores de Sistemas, Oficiales de Seguridad de la Información (CISO), Ingenieros DevOps y Auditores de TI encargados de la supervisión, mantenimiento y escalabilidad de la infraestructura de <strong>FusaShop</strong>.</p>

<div style="margin-top: 15px;">
  <div class="toc-item">
    <span class="toc-title">1. Arquitectura de Software y Topología Multicapa</span>
    <span class="toc-page">Capítulo 1</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">2. Diccionario de Datos y Modelo Entidad-Relación (ERD)</span>
    <span class="toc-page">Capítulo 2</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">3. Máquinas de Estados y Reglas de Transición Transaccional</span>
    <span class="toc-page">Capítulo 3</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">4. Seguridad de la Información, Criptografía y Hardening</span>
    <span class="toc-page">Capítulo 4</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">5. Rendimiento, Optimización de Recursos y Resiliencia</span>
    <span class="toc-page">Capítulo 5</span>
  </div>
  <div class="toc-item">
    <span class="toc-title">6. Despliegue en Producción, Monitoreo y Mantenimiento</span>
    <span class="toc-page">Capítulo 6</span>
  </div>
</div>

<div class="tech-alert" style="margin-top: 25px;">
  <strong>🔒 Cumplimiento Normativo y Protección de Datos:</strong>
  "La plataforma FusaShop cumple con los lineamientos de la Ley Estatutaria 1581 de 2012 de Protección de Datos Personales de Colombia, los estándares de seguridad de pagos PCI-DSS para el aislamiento de credenciales Wompi, y la Resolución DIAN 000042 para facturación electrónica mediante el protocolo Factus."
</div>

<!-- CAPÍTULO 1 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual Técnico de Sistemas</span>
  </div>
  <span class="header-bar-doc">Capítulo 1: Arquitectura de Software</span>
</div>

<h1 class="section-title">1. Arquitectura de Software y Modelo Multicapa</h1>

<p>FusaShop implementa una arquitectura modular desacoplada en seis capas de abstracción bien definidas, lo que garantiza alta cohesión, bajo acoplamiento y capacidad de escalar horizontalmente:</p>

<div style="margin: 15px 0;">
  <!-- Capa 1 -->
  <div class="diagram-layer" style="border-left: 5px solid #006c47;">
    <div class="diagram-layer-title">Capa 1: Edge & Presentación (Client Layer)</div>
    <div class="diagram-chips">
      <span class="chip chip-accent">Blade Templates (SSR)</span>
      <span class="chip chip-accent">Tailwind CSS v4 (Compilación JIT Standalone)</span>
      <span class="chip chip-accent">Alpine.js v3 (Micro-reactividad)</span>
      <span class="chip">WebP Content Negotiation</span>
      <span class="chip">Native Lazy Loading</span>
    </div>
  </div>

  <!-- Capa 2 -->
  <div class="diagram-layer" style="border-left: 5px solid #047857;">
    <div class="diagram-layer-title">Capa 2: Control, Enrutamiento & Middlewares</div>
    <div class="diagram-chips">
      <span class="chip">SecurityHeaders (CSP / HSTS / X-Frame)</span>
      <span class="chip">RoleMiddleware (RBAC)</span>
      <span class="chip">EnsureKycApproved</span>
      <span class="chip">CompressResponse (Gzip)</span>
      <span class="chip">Controllers Segregados: Consumer / Merchant / Analyst</span>
    </div>
  </div>

  <!-- Capa 3 -->
  <div class="diagram-layer" style="border-left: 5px solid #10b981;">
    <div class="diagram-layer-title">Capa 3: Servicios de Dominio & Inteligencia de Negocio</div>
    <div class="diagram-chips">
      <span class="chip chip-accent">CheckoutService</span>
      <span class="chip chip-accent">OrderPaymentService</span>
      <span class="chip chip-accent">StockService (Pessimistic Locking)</span>
      <span class="chip">ApiHealthService</span>
      <span class="chip">DashboardMetricsService</span>
      <span class="chip">TerritoryHeatmapService</span>
    </div>
  </div>

  <!-- Capa 4 -->
  <div class="diagram-layer" style="border-left: 5px solid #f97316;">
    <div class="diagram-layer-title">Capa 4: Cola de Trabajos Asíncronos (Queue / Worker Layer)</div>
    <div class="diagram-chips">
      <span class="chip chip-orange">ProcessWompiEvent</span>
      <span class="chip chip-orange">GenerateEnviaShipment</span>
      <span class="chip chip-orange">IssueFactusInvoice</span>
      <span class="chip">SendLowStockAlert</span>
      <span class="chip">SendOrderApprovedEmail</span>
    </div>
  </div>

  <!-- Capa 5 -->
  <div class="diagram-layer" style="border-left: 5px solid #6366f1;">
    <div class="diagram-layer-title">Capa 5: Persistencia, Cifrado & Almacenamiento</div>
    <div class="diagram-chips">
      <span class="chip">Eloquent ORM</span>
      <span class="chip">Casts Criptográficos (AES-256)</span>
      <span class="chip">MySQL 8.0+ / PostgreSQL 15+</span>
      <span class="chip">Redis Cache & Locks</span>
      <span class="chip">Storage Disk (WebP Assets)</span>
    </div>
  </div>

  <!-- Capa 6 -->
  <div class="diagram-layer" style="border-left: 5px solid #0284c7;">
    <div class="diagram-layer-title">Capa 6: Integraciones Externas & API Gateway</div>
    <div class="diagram-chips">
      <span class="chip">Wompi Web Checkout & Webhooks</span>
      <span class="chip">Envía Colvanes Logistics REST API</span>
      <span class="chip">Factus DIAN Electronic Invoicing</span>
      <span class="chip">MCP JSON-RPC 2.0 Server</span>
    </div>
  </div>
</div>

<h2 class="subsection-title">1.1 Características Clave de la Arquitectura</h2>
<ul>
  <li><strong>Desacoplamiento Transaccional:</strong> Las tareas lentas (generación de PDFs con guías de envío, llamadas a la DIAN, envío de correos) nunca ocurren durante el ciclo síncrono HTTP del comprador. Se despachan de inmediato como <code>Jobs</code> a la cola gestionada por el worker.</li>
  <li><strong>Idempotencia en Webhooks:</strong> Las notificaciones asíncronas de Wompi o Envía se verifican contra firmas HMAC SHA-256 y se registran en <code>integration_logs</code> antes de ejecutar cualquier mutación de saldo o estado.</li>
</ul>

<!-- CAPÍTULO 2 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual Técnico de Sistemas</span>
  </div>
  <span class="header-bar-doc">Capítulo 2: Diccionario de Datos</span>
</div>

<h1 class="section-title">2. Diccionario de Datos y Modelo Relacional (ERD)</h1>

<p>La base de datos comprende 37 migraciones versionadas. A continuación se detallan las especificaciones técnicas de las tablas y campos más representativos del esquema:</p>

<h2 class="subsection-title">2.1 Tabla <code>users</code> (Usuarios y Autenticación)</h2>
<table class="table-custom">
  <thead>
    <tr>
      <th>Campo</th>
      <th>Tipo de Dato</th>
      <th>Restricciones</th>
      <th>Descripción Técnica</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>id</code></td>
      <td>BIGINT UNSIGNED</td>
      <td>PK, Auto Increment</td>
      <td>Identificador único del usuario en el sistema.</td>
    </tr>
    <tr>
      <td><code>email</code></td>
      <td>VARCHAR(255)</td>
      <td>Unique, Index</td>
      <td>Correo electrónico principal utilizado para login y notificaciones.</td>
    </tr>
    <tr>
      <td><code>password</code></td>
      <td>VARCHAR(255)</td>
      <td>Not Null</td>
      <td>Hash criptográfico de contraseña generado con algoritmo Bcrypt (cost 12).</td>
    </tr>
    <tr>
      <td><code>role</code></td>
      <td>ENUM</td>
      <td>Default 'consumer'</td>
      <td>Roles del sistema: <code>consumer</code>, <code>merchant</code>, <code>analyst</code>.</td>
    </tr>
    <tr>
      <td><code>loyalty_points</code></td>
      <td>INTEGER UNSIGNED</td>
      <td>Default 0</td>
      <td>Saldo activo de FusaCoins acumulados por compras y fidelización.</td>
    </tr>
    <tr>
      <td><code>verification_code</code></td>
      <td>VARCHAR(6)</td>
      <td>Nullable</td>
      <td>Código OTP de 6 dígitos enviado por correo para validación inicial.</td>
    </tr>
    <tr>
      <td><code>is_blocked</code></td>
      <td>BOOLEAN</td>
      <td>Default false</td>
      <td>Flag de suspensión preventiva por auditoría administrativa.</td>
    </tr>
  </tbody>
</table>

<h2 class="subsection-title">2.2 Tabla <code>company_profiles</code> (Comercios & Parámetros Fiscales)</h2>
<table class="table-custom">
  <thead>
    <tr>
      <th>Campo</th>
      <th>Tipo de Dato</th>
      <th>Restricciones</th>
      <th>Descripción Técnica</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>id</code></td>
      <td>BIGINT UNSIGNED</td>
      <td>PK, Auto Increment</td>
      <td>Identificador del perfil comercial.</td>
    </tr>
    <tr>
      <td><code>merchant_id</code></td>
      <td>BIGINT UNSIGNED</td>
      <td>FK -> users.id</td>
      <td>Relación uno a uno con el usuario propietario de la tienda.</td>
    </tr>
    <tr>
      <td><code>store_name</code></td>
      <td>VARCHAR(255)</td>
      <td>Not Null</td>
      <td>Razón social o nombre público comercial del establecimiento.</td>
    </tr>
    <tr>
      <td><code>kyc_status</code></td>
      <td>ENUM</td>
      <td>Default 'pending'</td>
      <td>Estado de validación legal: <code>pending</code>, <code>approved</code>, <code>rejected</code>.</td>
    </tr>
    <tr>
      <td><code>wompi_private_key</code></td>
      <td>TEXT</td>
      <td>Nullable, Cifrado</td>
      <td>Llave secreta de Wompi cifrada en base de datos con AES-256-CBC.</td>
    </tr>
    <tr>
      <td><code>wompi_integrity_secret</code></td>
      <td>TEXT</td>
      <td>Nullable, Cifrado</td>
      <td>Secreto de integridad SHA256 para validación de montos.</td>
    </tr>
    <tr>
      <td><code>latitude</code> / <code>longitude</code></td>
      <td>DECIMAL(10,8)</td>
      <td>Nullable</td>
      <td>Coordenadas GPS georreferenciadas para el mapa territorial.</td>
    </tr>
  </tbody>
</table>

<!-- CONTINUACIÓN DICCIONARIO DE DATOS -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual Técnico de Sistemas</span>
  </div>
  <span class="header-bar-doc">Capítulo 2: Diccionario de Datos (Cont.)</span>
</div>

<h2 class="subsection-title">2.3 Tabla <code>orders</code> (Órdenes y Transacciones)</h2>
<table class="table-custom">
  <thead>
    <tr>
      <th>Campo</th>
      <th>Tipo de Dato</th>
      <th>Restricciones</th>
      <th>Descripción Técnica</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>id</code></td>
      <td>BIGINT UNSIGNED</td>
      <td>PK, Auto Increment</td>
      <td>Identificador de la orden.</td>
    </tr>
    <tr>
      <td><code>reference</code></td>
      <td>VARCHAR(50)</td>
      <td>Unique, Index</td>
      <td>Referencia alfanumérica única en formato <code>FS-YYYYMMDD-XXXX</code>.</td>
    </tr>
    <tr>
      <td><code>status</code></td>
      <td>ENUM</td>
      <td>Default 'pending'</td>
      <td>Estados: <code>pending</code>, <code>approved</code>, <code>processing</code>, <code>shipped</code>, <code>delivered</code>, <code>cancelled</code>.</td>
    </tr>
    <tr>
      <td><code>payment_status</code></td>
      <td>ENUM</td>
      <td>Default 'pending'</td>
      <td>Estado de pasarela: <code>pending</code>, <code>paid</code>, <code>declined</code>, <code>voided</code>, <code>refunded</code>.</td>
    </tr>
    <tr>
      <td><code>total</code></td>
      <td>DECIMAL(12,2)</td>
      <td>Not Null</td>
      <td>Monto total a cobrar en pesos colombianos (COP).</td>
    </tr>
    <tr>
      <td><code>points_discount</code></td>
      <td>DECIMAL(10,2)</td>
      <td>Default 0.00</td>
      <td>Descuento económico aplicado por redención de FusaCoins.</td>
    </tr>
    <tr>
      <td><code>shipping_tracking_number</code></td>
      <td>VARCHAR(100)</td>
      <td>Nullable</td>
      <td>Número de guía expedido por la API de Envía Colvanes.</td>
    </tr>
    <tr>
      <td><code>invoice_number</code></td>
      <td>VARCHAR(100)</td>
      <td>Nullable</td>
      <td>Código Único de Factura Electrónica (CUFE) expedido por Factus / DIAN.</td>
    </tr>
  </tbody>
</table>

<h2 class="subsection-title">2.4 Tabla <code>admin_audit_logs</code> (Auditoría Forense)</h2>
<p>Proporciona trazabilidad completa e inmutable sobre cualquier mutación crítica realizada desde el portal administrativo:</p>

<table class="table-custom">
  <thead>
    <tr>
      <th>Campo</th>
      <th>Tipo de Dato</th>
      <th>Descripción Técnica</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>id</code></td>
      <td>BIGINT UNSIGNED</td>
      <td>Identificador del registro de auditoría.</td>
    </tr>
    <tr>
      <td><code>admin_id</code></td>
      <td>BIGINT UNSIGNED</td>
      <td>FK -> users.id (identificador del funcionario ejecutor).</td>
    </tr>
    <tr>
      <td><code>action</code></td>
      <td>VARCHAR(100)</td>
      <td>Acción efectuada (ej: <code>merchant.kyc.approved</code>, <code>merchant.blocked</code>).</td>
    </tr>
    <tr>
      <td><code>entity_type</code></td>
      <td>VARCHAR(100)</td>
      <td>Clase Eloquent afectada (ej: <code>App\Models\CompanyProfile</code>).</td>
    </tr>
    <tr>
      <td><code>old_values</code> / <code>new_values</code></td>
      <td>JSON</td>
      <td>Instantánea comparativa de los valores modificados para trazabilidad legal.</td>
    </tr>
    <tr>
      <td><code>ip_address</code></td>
      <td>VARCHAR(45)</td>
      <td>Dirección IP IPv4/IPv6 de la estación donde se originó el comando.</td>
    </tr>
  </tbody>
</table>

<!-- CAPÍTULO 3 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual Técnico de Sistemas</span>
  </div>
  <span class="header-bar-doc">Capítulo 3: Máquinas de Estados</span>
</div>

<h1 class="section-title">3. Máquinas de Estados y Reglas de Transición</h1>

<p>FusaShop implementa modelos formales de estado finito para gobernar las órdenes y los trámites de verificación de tiendas, evitando inconsistencias operacionales o estados huérfanos.</p>

<h2 class="subsection-title">3.1 Ciclo de Vida de una Orden de Compra</h2>

<div class="info-box">
  <strong>Flujo Secuencial Estándar:</strong><br>
  <code>[PENDING]</code> ➔ Webhook Wompi OK ➔ <code>[APPROVED]</code> ➔ Comercio Prepara Paquete ➔ <code>[PROCESSING]</code> ➔ Despacho Envía ➔ <code>[SHIPPED]</code> ➔ Entrega Confirmada ➔ <code>[DELIVERED]</code>
</div>

<table class="table-custom">
  <thead>
    <tr>
      <th>Estado Actual</th>
      <th>Evento Disparador</th>
      <th>Nuevo Estado</th>
      <th>Efectos Secundarios Ejecutados</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>pending</code></td>
      <td>Webhook Wompi <code>APPROVED</code></td>
      <td><code>approved</code></td>
      <td>Bloqueo de stock confirmado, acreditación de FusaCoins al comprador, encolado de <code>IssueFactusInvoice</code> y <code>GenerateEnviaShipment</code>.</td>
    </tr>
    <tr>
      <td><code>pending</code></td>
      <td>Webhook Wompi <code>DECLINED</code></td>
      <td><code>cancelled</code></td>
      <td>Liberación automática del inventario reservado en <code>StockService</code> y notificación por correo al comprador.</td>
    </tr>
    <tr>
      <td><code>approved</code></td>
      <td>Comercio inicia embalaje</td>
      <td><code>processing</code></td>
      <td>Notificación por correo al comprador con detalles del tiempo de alistamiento.</td>
    </tr>
    <tr>
      <td><code>processing</code></td>
      <td>Comercio entrega paquete a Envía</td>
      <td><code>shipped</code></td>
      <td>Asignación de número de guía y envío de enlace de rastreo en vivo.</td>
    </tr>
    <tr>
      <td><code>shipped</code></td>
      <td>Webhook Envía entrega efectiva</td>
      <td><code>delivered</code></td>
      <td>Habilitación del formulario de calificación (Review) y liberación de fondos.</td>
    </tr>
  </tbody>
</table>

<h2 class="subsection-title">3.2 Control de Concurrencia e Idempotencia</h2>
<p>Para evitar que un cliente sea cobrado dos veces si Wompi reintenta una llamada de webhook debido a latencia de red:</p>
<ol>
  <li>El endpoint <code>/api/webhooks/wompi</code> consulta primero la tabla <code>integration_logs</code> buscando si el <code>event_id</code> ya fue procesado.</li>
  <li>Se ejecuta una cláusula <code>Order::where('id', $orderId)->lockForUpdate()</code> dentro de una transacción de base de datos.</li>
  <li>Si el estado de la orden ya es <code>paid</code> o <code>approved</code>, el controlador retorna inmediatamente un código HTTP <code>200 OK</code> sin repetir la emisión de factura ni de puntos.</li>
</ol>

<!-- CAPÍTULO 4 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual Técnico de Sistemas</span>
  </div>
  <span class="header-bar-doc">Capítulo 4: Seguridad y Criptografía</span>
</div>

<h1 class="section-title">4. Seguridad, Criptografía y Hardening</h1>

<h2 class="subsection-title">4.1 Arquitectura Criptográfica</h2>
<div class="card-grid">
  <div class="card-item">
    <h4>🔐 Cifrado AES-256-CBC en Reposo</h4>
    <p style="font-size: 8.5pt;">Las credenciales privadas de Wompi de los comercios (<code>wompi_private_key</code> y <code>wompi_integrity_secret</code>) se almacenan cifradas en disco con llave maestra de 256 bits (<code>APP_KEY</code>). Nunca se guardan en texto claro.</p>
  </div>
  <div class="card-item">
    <h4>🔑 Hashing Seguro Bcrypt</h4>
    <p style="font-size: 8.5pt;">Todas las contraseñas de usuarios y analistas se procesan con la función hash adaptativa Bcrypt con un costo de trabajo (work factor) de 12 rondas, mitigando ataques de fuerza bruta.</p>
  </div>
</div>

<h2 class="subsection-title">4.2 Hardening de Cabeceras HTTP (<code>SecurityHeaders.php</code>)</h2>
<p>El middleware <code>App\Http\Middleware\SecurityHeaders</code> inyecta en cada respuesta HTTP directivas de protección avanzada contra vectores de ataque comunes (XSS, Clickjacking, MIME Sniffing):</p>

<div class="code-container">
  <div class="code-title">
    <span>Cabeceras HTTP Inyectadas en FusaShop</span>
  </div>
<pre>
<span class="code-comment">// Prevención de Clickjacking</span>
X-Frame-Options: SAMEORIGIN

<span class="code-comment">// Prevención de confusión de tipos MIME</span>
X-Content-Type-Options: nosniff

<span class="code-comment">// Control de fuga de información de navegación</span>
Referrer-Policy: strict-origin-when-cross-origin

<span class="code-comment">// Aislamiento de APIs del navegador no autorizadas</span>
Permissions-Policy: camera=(), microphone=(), geolocation=(self)

<span class="code-comment">// Política de Seguridad de Contenido (CSP)</span>
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;
</pre>
</div>

<h2 class="subsection-title">4.3 Mitigación de Vulnerabilidades OWASP Top 10</h2>
<ul>
  <li><strong>Inyección SQL:</strong> Mitigada al 100% mediante el uso exclusivo de consultas parametrizadas a través de PDO en Eloquent ORM. No se concatenan variables en consultas crudas.</li>
  <li><strong>Cross-Site Scripting (XSS):</strong> El motor de plantillas Blade escapa automáticamente todas las variables renderizadas mediante la sintaxis <code>{{ $variable }}</code> usando <code>htmlspecialchars(..., ENT_QUOTES, 'UTF-8')</code>.</li>
  <li><strong>Cross-Site Request Forgery (CSRF):</strong> Todos los formularios POST, PUT y DELETE requieren la presencia y validación del token <code>_token</code> de sesión. Los webhooks externos utilizan firmas HMAC.</li>
</ul>

<!-- CAPÍTULO 5 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual Técnico de Sistemas</span>
  </div>
  <span class="header-bar-doc">Capítulo 5: Rendimiento y Optimización</span>
</div>

<h1 class="section-title">5. Rendimiento, Optimización y Resiliencia</h1>

<h2 class="subsection-title">5.1 Negociación de Contenido WebP & Compresión Gráfica</h2>
<p>Las imágenes representan más del 70% del ancho de banda en un catálogo de comercio electrónico. FusaShop implementa un pipeline automatizado:</p>

<div class="card-grid">
  <div class="card-item">
    <h4>🖼️ Conversión Batch a WebP</h4>
    <p style="font-size: 8.5pt;">El script <code>optimize-images.cjs</code> comprime sistemáticamente todos los recursos de <code>public/</code> y <code>storage/</code> logrando reducciones de peso promedio del <strong>74.2%</strong> sin pérdida perceptible de calidad.</p>
  </div>
  <div class="card-item">
    <h4>📡 Negociación HTTP Transparente</h4>
    <p style="font-size: 8.5pt;">Las rutas de medios inspeccionan la cabecera <code>Accept: image/webp</code>. Si el cliente la soporta, el servidor entrega la variante WebP optimizada; en navegadores legados, entrega el PNG original de respaldo.</p>
  </div>
</div>

<h2 class="subsection-title">5.2 Eliminación Radical de Consultas N+1</h2>
<p>En pantallas complejas como el panel de reportes analíticos y la auditoría de ventas, se reemplazaron bucles de consultas Eloquent repetitivas por consultas agrupadas SQL con <code>SUM()</code> y <code>GROUP BY</code>:</p>

<div class="code-container">
  <div class="code-title">
    <span>Optimización en AnalystController.php — Reducción de 240 a 2 Consultas</span>
  </div>
<pre>
<span class="code-comment">// Agrupación en una única consulta indexada</span>
<span class="code-var">$paymentTotals</span> = DB::table(<span class="code-string">'orders'</span>)
    ->whereIn(<span class="code-string">'id'</span>, <span class="code-var">$orderIds</span>)
    ->select(<span class="code-string">'merchant_id'</span>, DB::raw(<span class="code-string">'SUM(total) as gross_sales'</span>), DB::raw(<span class="code-string">'COUNT(*) as total_orders'</span>))
    ->groupBy(<span class="code-string">'merchant_id'</span>)
    ->pluck(<span class="code-string">'gross_sales'</span>, <span class="code-string">'merchant_id'</span>);
</pre>
</div>

<h2 class="subsection-title">5.3 Compresión de Transporte en Servidor (Gzip / Deflate)</h2>
<p>Se activaron reglas de compresión en <code>public/.htaccess</code> para servidores Apache (<code>mod_deflate</code>) y un middleware fallback en PHP (<code>CompressResponse</code>):</p>
<ul>
  <li>Payloads HTML reducidos de ~85 KB a ~16 KB (-81%).</li>
  <li>Hojas de estilo CSS minificadas reducidas de ~42 KB a ~8.5 KB (-79%).</li>
  <li>Respuestas JSON de APIs y FusaBot reducidas en más del 85%.</li>
</ul>

<!-- CAPÍTULO 6 -->
<div class="page-break"></div>
<div class="header-bar">
  <div class="header-bar-left">
    ${logoBase64 ? `<img src="${logoBase64}" alt="Logo" style="height: 20px;">` : ''}
    <span class="header-bar-title">FusaShop • Manual Técnico de Sistemas</span>
  </div>
  <span class="header-bar-doc">Capítulo 6: Despliegue en Producción</span>
</div>

<h1 class="section-title">6. Despliegue en Producción, Monitoreo y Mantenimiento</h1>

<h2 class="subsection-title">6.1 Dimensionamiento Mínimo de Servidor</h2>
<table class="table-custom">
  <thead>
    <tr>
      <th>Recurso</th>
      <th>Requisito Mínimo</th>
      <th>Recomendado para Producción</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Sistema Operativo</strong></td>
      <td>Ubuntu Server 22.04 LTS / 24.04 LTS</td>
      <td>Ubuntu Server 24.04 LTS con Kernel optimizado</td>
    </tr>
    <tr>
      <td><strong>CPU</strong></td>
      <td>2 vCPU (2.4 GHz+)</td>
      <td>4 vCPU dedicadas</td>
    </tr>
    <tr>
      <td><strong>Memoria RAM</strong></td>
      <td>2 GB RAM + 2 GB Swap</td>
      <td>8 GB RAM (con Redis in-memory)</td>
    </tr>
    <tr>
      <td><strong>Almacenamiento</strong></td>
      <td>25 GB SSD</td>
      <td>80 GB NVMe con backups automáticos</td>
    </tr>
  </tbody>
</table>

<h2 class="subsection-title">6.2 Configuración de Workers con Supervisor</h2>
<p>Para asegurar que las colas de correos, facturación Factus y guías de Envía se procesen continuamente sin interrupción, se configura el demonio Supervisor en <code>/etc/supervisor/conf.d/fusashop-worker.conf</code>:</p>

<div class="code-container">
  <div class="code-title">
    <span>Configuración de Supervisor en Linux</span>
  </div>
<pre>
[program:fusashop-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/fusashop/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/fusashop/storage/logs/worker.log
stopwaitsecs=3600
</pre>
</div>

<h2 class="subsection-title">6.3 Cron Job para Laravel Scheduler</h2>
<p>En el archivo crontab del usuario del servidor web (<code>crontab -e -u www-data</code>), registrar una única entrada para gestionar tareas recurrentes (limpieza de carritos abandonados, expiración de banners, sondeo de salud de APIs):</p>

<div class="code-container">
  <div class="code-title">
    <span>Llamada Minutal al Scheduler de Laravel</span>
  </div>
<pre>
* * * * * cd /var/www/fusashop && php artisan schedule:run >> /dev/null 2>&1
</pre>
</div>

<h2 class="subsection-title">6.4 Check-List de Pase a Producción (Go-Live)</h2>
<ol>
  <li>Ejecutar <code>php artisan config:cache</code>, <code>php artisan route:cache</code> y <code>php artisan view:cache</code>.</li>
  <li>Establecer <code>APP_ENV=production</code> y <code>APP_DEBUG=false</code> en el archivo <code>.env</code>.</li>
  <li>Configurar certificado SSL/TLS con cifrado fuerte (Let's Encrypt / Certbot con renovación automática).</li>
  <li>Verificar que las llaves de pasarela de los comercios en <code>company_profiles</code> estén cifradas.</li>
  <li>Ejecutar <code>php artisan test</code> para validar que los 76 tests pasan limpiamente antes de abrir tráfico público.</li>
</ol>

</body>
</html>`;

const htmlFilePath = path.join('docs', 'Manual_Tecnico_FusaShop.html');
const pdfFilePath = path.join('docs', 'Manual_Tecnico_FusaShop.pdf');

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
    console.log(`✅ Manual Técnico generado con éxito en: ${pdfFilePath}`);
} catch (error) {
    console.error('Error al generar PDF:', error.message);
    process.exit(1);
}
