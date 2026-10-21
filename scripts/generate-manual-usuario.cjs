const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('Generando Manual de Usuario de FusaShop...');

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
<title>Manual de Usuario — FusaShop</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;600&display=swap');

  @page {
    size: letter;
    margin: 20mm 18mm 20mm 18mm;
    @bottom-right {
      content: counter(page);
    }
  }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    color: #1e293b;
    background: #ffffff;
    font-size: 10.5pt;
    line-height: 1.6;
  }

  /* Portada */
  .cover-page {
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 40px 20px;
    page-break-after: always;
    border: 3px solid #10B981;
    border-radius: 20px;
    background: linear-gradient(175deg, #ffffff 65%, #f0fdf4 100%);
    position: relative;
    overflow: hidden;
  }

  .cover-badge {
    display: inline-block;
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #10b981;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 9pt;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .cover-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .cover-logo {
    height: 60px;
    width: auto;
    object-contain: contain;
  }

  .cover-body {
    margin-top: 40px;
  }

  .cover-title {
    font-size: 32pt;
    font-weight: 800;
    color: #064e3b;
    line-height: 1.15;
    margin-bottom: 12px;
  }

  .cover-title span {
    color: #10B981;
  }

  .cover-subtitle {
    font-size: 14pt;
    font-weight: 600;
    color: #334155;
    line-height: 1.4;
    margin-bottom: 24px;
    max-width: 90%;
  }

  .cover-desc {
    font-size: 10.5pt;
    color: #64748b;
    max-width: 85%;
    line-height: 1.6;
    margin-bottom: 30px;
  }

  .cover-meta-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  }

  .meta-item h4 {
    font-size: 8pt;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 700;
  }

  .meta-item p {
    font-size: 10pt;
    color: #0f172a;
    font-weight: 700;
  }

  .cover-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #e2e8f0;
    padding-top: 16px;
    font-size: 9pt;
    color: #64748b;
  }

  /* Paginación y Saltos */
  .page-break {
    page-break-before: always;
    break-before: page;
  }

  .no-break {
    page-break-inside: avoid;
    break-inside: avoid;
  }

  /* Encabezados y Secciones */
  h1 {
    font-size: 20pt;
    font-weight: 800;
    color: #064e3b;
    border-bottom: 2.5px solid #10B981;
    padding-bottom: 8px;
    margin-top: 24px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  h2 {
    font-size: 14pt;
    font-weight: 700;
    color: #047857;
    margin-top: 20px;
    margin-bottom: 10px;
    border-left: 4px solid #10B981;
    padding-left: 10px;
  }

  h3 {
    font-size: 11.5pt;
    font-weight: 700;
    color: #0f172a;
    margin-top: 14px;
    margin-bottom: 8px;
  }

  p {
    margin-bottom: 10px;
    text-align: justify;
  }

  ul, ol {
    margin-left: 22px;
    margin-bottom: 12px;
  }

  li {
    margin-bottom: 4px;
  }

  strong {
    color: #0f172a;
    font-weight: 700;
  }

  /* Tablas Técnicas */
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 14px 0 18px 0;
    font-size: 9pt;
    page-break-inside: avoid;
  }

  th {
    background-color: #064e3b;
    color: #ffffff;
    font-weight: 700;
    text-align: left;
    padding: 8px 10px;
    border: 1px solid #064e3b;
    font-size: 8.5pt;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  td {
    padding: 7px 10px;
    border: 1px solid #e2e8f0;
    vertical-align: middle;
  }

  tr:nth-child(even) td {
    background-color: #f8fafc;
  }

  /* Cajas de Alerta / Callouts */
  .callout {
    padding: 12px 16px;
    border-radius: 10px;
    margin: 14px 0;
    font-size: 9.5pt;
    display: flex;
    gap: 12px;
    page-break-inside: avoid;
  }

  .callout-info {
    background-color: #ecfdf5;
    border-left: 4px solid #10B981;
    color: #065f46;
  }

  .callout-warning {
    background-color: #fffbeb;
    border-left: 4px solid #f59e0b;
    color: #92400e;
  }

  .callout-tip {
    background-color: #eff6ff;
    border-left: 4px solid #3b82f6;
    color: #1e40af;
  }

  .callout-icon {
    font-size: 16pt;
    line-height: 1;
  }

  /* Diagramas y Flujos */
  .flow-box {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    padding: 14px;
    margin: 14px 0;
    font-family: 'Fira Code', monospace;
    font-size: 8.5pt;
    line-height: 1.45;
    color: #0f172a;
    overflow-x: auto;
    page-break-inside: avoid;
  }

  .badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 8pt;
    font-weight: 700;
  }
  .badge-green { background: #dcfce7; color: #166534; }
  .badge-orange { background: #ffedd5; color: #9a3412; }
  .badge-blue { background: #dbeafe; color: #1e40af; }
  .badge-red { background: #fee2e2; color: #991b1b; }

  /* Encabezado y Pie de página de contenido */
  .header-running {
    position: running(headerRunning);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 5px;
    font-size: 8pt;
    color: #64748b;
    margin-bottom: 20px;
  }

  .footer-running {
    position: running(footerRunning);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #e2e8f0;
    padding-top: 6px;
    font-size: 8pt;
    color: #94a3b8;
  }

  .step-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background: #10B981;
    color: #ffffff;
    border-radius: 50%;
    font-size: 9pt;
    font-weight: 800;
    margin-right: 6px;
  }

  .toc-item {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 6px 0;
    border-bottom: 1px dotted #cbd5e1;
    font-size: 10pt;
  }
  .toc-title { font-weight: 600; color: #1e293b; }
  .toc-page { font-weight: 700; color: #047857; }
</style>
</head>
<body>

<!-- PORTADA -->
<div class="cover-page">
  <div class="cover-header">
    <span class="cover-badge">Documento Oficial FusaShop · Edición Productiva</span>
    ${logoBase64 ? `<img src="${logoBase64}" class="cover-logo" alt="Logo FusaShop">` : '<h2 style="color:#10B981;">FusaShop</h2>'}
  </div>

  <div class="cover-body">
    <h1 class="cover-title">MANUAL DE <span>USUARIO</span> INTEGRAL</h1>
    <h2 class="cover-subtitle">Guía de Operación y Manual de Usuario para Consumidores, Comerciantes y Analistas del Ecosistema Comercial del Sumapaz</h2>
    <p class="cover-desc">
      Manual completo y técnico de uso para la plataforma omnicanal FusaShop. Detalla los procesos de registro, búsqueda inteligente con FusaBot IA, pasarela transaccional Wompi, logística coordinada con Envia.com, facturación electrónica DIAN con Factus y administración comercial en Fusagasugá, Cundinamarca.
    </p>

    <div class="cover-meta-grid">
      <div class="meta-item">
        <h4>Plataforma</h4>
        <p>FusaShop Sumapaz v2.5</p>
      </div>
      <div class="meta-item">
        <h4>Territorio</h4>
        <p>Fusagasugá & Provincia del Sumapaz</p>
      </div>
      <div class="meta-item">
        <h4>Integraciones</h4>
        <p>Wompi · Envia.com · Factus · FusaBot</p>
      </div>
      <div class="meta-item">
        <h4>Audiencia</h4>
        <p>Comprador · Comerciante · Analista</p>
      </div>
      <div class="meta-item">
        <h4>Vigencia</h4>
        <p>Octubre 2026 – 2027</p>
      </div>
      <div class="meta-item">
        <h4>Estado</h4>
        <p>Documento Aprobado y Certificado</p>
      </div>
    </div>
  </div>

  <div class="cover-footer">
    <span>© 2026 FusaShop Inc. Todos los derechos reservados. Fusagasugá, Colombia.</span>
    <span>ID Documental: FSP-MAN-USR-2026-V2</span>
  </div>
</div>

<!-- TABLA DE CONTENIDOS -->
<div class="page-break">
  <h1>Tabla de Contenido</h1>
  <p style="color:#64748b; margin-bottom: 20px;">Estructura temática y navegación rápida del Manual de Usuario de FusaShop.</p>

  <div style="margin-top: 20px; space-y: 8px;">
    <div class="toc-item">
      <span class="toc-title"><strong>1. Introducción y Visión General de FusaShop</strong></span>
      <span class="toc-page">Sección 1</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>1.1 Propósito y Cobertura Territorial en el Sumapaz</span>
      <span class="toc-page">1.1</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>1.2 Arquitectura de Roles: Comprador, Comerciante y Analista</span>
      <span class="toc-page">1.2</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>1.3 Los Tres Pilares de Confianza: Wompi, Envia.com y Factus DIAN</span>
      <span class="toc-page">1.3</span>
    </div>

    <div class="toc-item" style="margin-top: 10px;">
      <span class="toc-title"><strong>2. Módulo del Consumidor / Comprador</strong></span>
      <span class="toc-page">Sección 2</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.1 Registro, Seguridad OTP de 6 Dígitos e Inicio de Sesión</span>
      <span class="toc-page">2.1</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.2 Navegación, Omnibar Inteligente (Voz/Imagen) y Directorio</span>
      <span class="toc-page">2.2</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.3 Carrito con Selección Selectiva (Físicos vs. O2O)</span>
      <span class="toc-page">2.3</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.4 Pasarela de Pagos Wompi (PSE, Tarjetas, Nequi, Corresponsales)</span>
      <span class="toc-page">2.4</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.5 Logística y Rastreo de Envíos con Envia.com</span>
      <span class="toc-page">2.5</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.6 Facturación Electrónica DIAN Automática (Factus)</span>
      <span class="toc-page">2.6</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.7 Sistema de Puntos de Lealtad FusaCoins</span>
      <span class="toc-page">2.7</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.8 Asistente Virtual FusaBot IA para Clientes</span>
      <span class="toc-page">2.8</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>2.9 Garantías, Cancelaciones y Radicación de PQRS</span>
      <span class="toc-page">2.9</span>
    </div>

    <div class="toc-item" style="margin-top: 10px;">
      <span class="toc-title"><strong>3. Módulo del Comerciante (Merchant Center)</strong></span>
      <span class="toc-page">Sección 3</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>3.1 Registro Comercial y Verificación KYC con RUT</span>
      <span class="toc-page">3.1</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>3.2 Publicación y Edición del Catálogo de Productos y Servicios</span>
      <span class="toc-page">3.2</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>3.3 Despacho de Pedidos y Generación de Guías con Envia.com</span>
      <span class="toc-page">3.3</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>3.4 Inventario Rápido y Monitoreo de Stock Crítico</span>
      <span class="toc-page">3.4</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>3.5 Finanzas, Liquidación de Comisiones y Retiros a Cuentas Bancarias</span>
      <span class="toc-page">3.5</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>3.6 Pauta Publicitaria (Banners Globales y Tarjetas de Promoción)</span>
      <span class="toc-page">3.6</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>3.7 Mensajería Directa con Clientes y Chat con Analistas</span>
      <span class="toc-page">3.7</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>3.8 Reportes de Diagnóstico Causal de Ventas</span>
      <span class="toc-page">3.8</span>
    </div>

    <div class="toc-item" style="margin-top: 10px;">
      <span class="toc-title"><strong>4. Módulo del Analista y Administrador (Operations Console)</strong></span>
      <span class="toc-page">Sección 4</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>4.1 Panel de Monitoreo Territorial e Inteligencia del Sumapaz</span>
      <span class="toc-page">4.1</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>4.2 Gestión de Usuarios y Validación KYC de Empresas</span>
      <span class="toc-page">4.2</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>4.3 Auditoría de Integraciones y Monitoreo de Errores de API</span>
      <span class="toc-page">4.3</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>4.4 Bitácora Inmutable de Auditoría (AdminAuditLog)</span>
      <span class="toc-page">4.4</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>4.5 Conciliación de Pagos y Liquidación de Pauta</span>
      <span class="toc-page">4.5</span>
    </div>
    <div class="toc-item" style="padding-left: 15px;">
      <span>4.6 Gestión de PQRS y Control de SLA</span>
      <span class="toc-page">4.6</span>
    </div>

    <div class="toc-item" style="margin-top: 10px;">
      <span class="toc-title"><strong>5. Preguntas Frecuentes (FAQ) y Soporte Técnico</strong></span>
      <span class="toc-page">Sección 5</span>
    </div>
  </div>
</div>

<!-- SECCIÓN 1: INTRODUCCIÓN -->
<div class="page-break">
  <h1>1. Introducción y Visión General de FusaShop</h1>

  <h2>1.1 Propósito y Cobertura Territorial en el Sumapaz</h2>
  <p>
    <strong>FusaShop</strong> es el ecosistema de comercio electrónico, turismo y encadenamiento productivo oficial de la provincia del Sumapaz, conectando a los más de 140.000 habitantes de Fusagasugá con los municipios vecinos de Chinauta, Silvania, Arbeláez y Pasca. Su propósito es erradicar la intermediación abusiva y brindarle a los micro, pequeños y medianos comerciantes locales (productores agrícolas, orquidearios, tiendas de moda, artesanos, restaurantes y glampings) una plataforma tecnológica de nivel mundial con pagos bancarizados, envíos puerta a puerta y facturación electrónica válida ante la DIAN.
  </p>

  <h2>1.2 Arquitectura de Roles: Comprador, Comerciante y Analista</h2>
  <p>
    La plataforma opera bajo un modelo de control de acceso basado en roles (RBAC), asignando interfaces especializadas según el perfil del usuario:
  </p>

  <table>
    <thead>
      <tr>
        <th style="width: 20%;">Rol del Sistema</th>
        <th style="width: 35%;">Propósito Principal</th>
        <th style="width: 45%;">Capacidades Clave</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Consumidor</strong><br><span class="badge badge-blue">consumer</span></td>
        <td>Comprador final residente o turista del Sumapaz.</td>
        <td>Navegación de catálogos, pagos Wompi, seguimiento de guías Envia, acumulación de FusaCoins, radicación de PQRS y chat asistido.</td>
      </tr>
      <tr>
        <td><strong>Comerciante</strong><br><span class="badge badge-green">merchant</span></td>
        <td>Empresa o productor local verificado mediante KYC.</td>
        <td>Administración de catálogo, inventario rápido, despacho logístico con Envia.com, configuración de cuentas de retiro y finanzas.</td>
      </tr>
      <tr>
        <td><strong>Analista / Admin</strong><br><span class="badge badge-orange">analyst</span></td>
        <td>Equipo administrativo y operativo central de FusaShop.</td>
        <td>Validación de empresas (KYC), auditoría de logs, conciliación bancaria, soporte de PQRS y monitoreo de integraciones externas.</td>
      </tr>
    </tbody>
  </table>

  <h2>1.3 Los Tres Pilares de Confianza Tecnológica</h2>
  <p>
    FusaShop resuelve los mayores obstáculos del comercio digital en ciudades intermedias mediante tres integraciones automáticas certificadas:
  </p>
  <ul>
    <li><strong>Wompi (Bancolombia):</strong> Pasarela que procesa pagos por PSE, tarjetas débito/crédito, Nequi y corresponsales bancarios bajo estándares PCI-DSS con firmas de integridad criptográfica SHA-256.</li>
    <li><strong>Envia.com:</strong> Motor de logística que cotiza en tiempo real entre múltiples transportadoras autorizadas en Colombia (Envia, Servientrega, Coordinadora, Interrapidísimo), generando la guía con código de barras y trazabilidad satelital.</li>
    <li><strong>Factus DIAN:</strong> Proveedor tecnológico de facturación electrónica que emite en milisegundos la factura con Código Único de Factura Electrónica (CUFE), generando el archivo PDF visual y el XML legal firmado digitalmente.</li>
  </ul>

  <div class="callout callout-info">
    <div class="callout-icon">💡</div>
    <div>
      <strong>Integración Transparente:</strong> Ningún usuario o comerciante necesita tramitar acuerdos separados con transportadoras o proveedores de facturación. FusaShop centraliza la infraestructura tecnológica para que el comerciante solo se preocupe por empacar su producto.
    </div>
  </div>
</div>

<!-- SECCIÓN 2: MÓDULO DEL CONSUMIDOR -->
<div class="page-break">
  <h1>2. Módulo del Consumidor / Comprador</h1>

  <h2>2.1 Registro, Seguridad OTP de 6 Dígitos e Inicio de Sesión</h2>
  <p>
    Para garantizar compras legítimas y evitar cuentas fraudulentas, FusaShop implementa un sistema de autenticación de dos factores al registrarse:
  </p>
  <ol>
    <li><span class="step-number">1</span> Ingrese a la plataforma y pulse en <strong>Registrarme</strong> en la barra superior.</li>
    <li><span class="step-number">2</span> Diligencie su nombre completo, correo electrónico, número de teléfono móvil y contraseña segura.</li>
    <li><span class="step-number">3</span> La plataforma enviará inmediatamente un <strong>Código de Verificación de 6 dígitos</strong> a su bandeja de correo electrónico.</li>
    <li><span class="step-number">4</span> Ingrese el código en la pantalla de verificación. El código cuenta con una validez de 15 minutos. Una vez validado, su cuenta queda completamente activa.</li>
    <li><span class="step-number">5</span> También puede iniciar sesión con un solo clic utilizando su cuenta de <strong>Google</strong>.</li>
  </ol>

  <h2>2.2 Navegación, Omnibar Inteligente (Voz/Imagen) y Directorio</h2>
  <p>
    La plataforma ofrece múltiples mecanismos de búsqueda adaptados tanto a usuarios experimentados como a personas mayores:
  </p>
  <ul>
    <li><strong>Buscador Inteligente Omnibar:</strong> Ubicado en la barra superior. Al escribir dos caracteres, despliega sugerencias predictivas automáticas con precio, foto y categoría sin recargar la página.</li>
    <li><strong>Búsqueda por Voz:</strong> Al pulsar el icono de micrófono, el sistema captura el audio en español colombiano y busca términos locales ("viveros de orquídeas en Chinauta", "camisetas campesinas").</li>
    <li><strong>Búsqueda Visual por Imagen:</strong> Permite subir una foto desde la cámara del celular para encontrar productos visualmente afines en las tiendas registradas.</li>
    <li><strong>Mega Menú de 14 Categorías:</strong> Acceso rápido a Tecnología, Moda, Viveros, Turismo, Restaurantes, Mayoristas B2B, Hogar, Mascotas, Café de Origen, Fincas de Recreo y Artesanías.</li>
    <li><strong>Directorio Comercial del Sumapaz:</strong> Vista de todas las marcas y negocios locales avalados con su dirección física, teléfono y catálogo completo.</li>
  </ul>

  <h2>2.3 Carrito con Selección Selectiva (Físicos vs. O2O)</h2>
  <p>
    El carrito de FusaShop cuenta con un algoritmo de <strong>Split Inteligente</strong> que diferencia automáticamente dos tipos de ítems:
  </p>
  <table>
    <thead>
      <tr>
        <th>Tipo de Ítem</th>
        <th>Ejemplos Comunes</th>
        <th>Manejo Logístico</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Productos Físicos</strong></td>
        <td>Ropa, electrodomésticos, café tostado, plantas.</td>
        <td>Requiere dirección de entrega. Cotiza y despacha automáticamente guía con Envia.com.</td>
      </tr>
      <tr>
        <td><strong>Servicios O2O (Online to Offline)</strong></td>
        <td>Pasadías en Glamping, reserva de mesa, rutas ecológicas.</td>
        <td>No cobra flete de envío. Genera un <strong>Voucher con Código QR</strong> instantáneo para redimir presencialmente en el negocio.</td>
      </tr>
    </tbody>
  </table>
  <p>
    Además, la función de <strong>Checkbox Selectivo</strong> le permite elegir cuáles productos desea pagar hoy y cuáles dejar guardados en el carrito para futuras compras.
  </p>

  <h2>2.4 Pasarela de Pagos Wompi</h2>
  <p>
    Al pulsar <strong>Proceder al Pago</strong> en el checkout, el sistema calcula el subtotal, el flete logístico y los descuentos aplicables. El comprador es dirigido a la pasarela segura Wompi con las siguientes opciones:
  </p>
  <ul>
    <li><strong>PSE (Cuentas de Ahorros / Corrientes):</strong> Débito inmediato en cualquier entidad bancaria de Colombia.</li>
    <li><strong>Billeteras Digitales (Nequi / Daviplata):</strong> Notificación push o código de aprobación instantáneo.</li>
    <li><strong>Tarjetas de Crédito / Débito:</strong> Visa, Mastercard, American Express con validación de seguridad 3DS.</li>
    <li><strong>Corresponsales Bancarios:</strong> Generación de código para pago en efectivo en puntos Bancolombia.</li>
  </ul>

  <div class="callout callout-warning">
    <div class="callout-icon">⚠️</div>
    <div>
      <strong>Confirmación de Pagos PSE:</strong> Los pagos mediante PSE pueden tardar de 1 a 5 minutos en recibir la confirmación del banco. No cierre la ventana ni intente realizar un segundo pago hasta verificar el estado en la sección <em>Mis Pedidos</em>.
    </div>
  </div>
</div>

<!-- SECCIÓN 2 CONTINUACIÓN -->
<div class="page-break">
  <h2>2.5 Logística y Rastreo de Envíos con Envia.com</h2>
  <p>
    Una vez aprobado el pago, el pedido entra en estado <span class="badge badge-green">processing</span>. El comerciante genera la guía y el paquete es recolectado. En la sección <strong>Mis Pedidos</strong>, el cliente puede:
  </p>
  <ul>
    <li>Ver la línea de tiempo del estado: <em>Pendiente → En Preparación → Despachado → Entregado</em>.</li>
    <li>Consultar el número de guía asignado por la transportadora.</li>
    <li>Hacer clic en <strong>Rastrear Guía</strong> para ver los movimientos satelitales en tiempo real directamente desde el portal de Envia.com.</li>
  </ul>

  <h2>2.6 Facturación Electrónica DIAN Automática (Factus)</h2>
  <p>
    FusaShop cumple con la legislación tributaria colombiana. Por cada compra completada, el sistema genera la factura electrónica a través de Factus:
  </p>
  <ul>
    <li>La factura incluye el <strong>CUFE</strong> (Código Único de Facturación Electrónica) validado en la plataforma de la DIAN.</li>
    <li>El cliente puede descargar en cualquier momento el archivo <strong>PDF oficial</strong> con formato tributario legal y el archivo <strong>XML UBL 2.1</strong> directamente desde el detalle de la orden.</li>
    <li>Se envía una copia digital automática a los correos del comprador y del comerciante.</li>
  </ul>

  <h2>2.7 Sistema de Puntos de Lealtad FusaCoins</h2>
  <p>
    Para incentivar la economía local, FusaShop premia la recurrencia con su moneda digital de fidelización:
  </p>
  <table>
    <thead>
      <tr>
        <th>Regla del Programa</th>
        <th>Mecánica de Operación</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Acumulación</strong></td>
        <td>Por cada $100 COP en compras efectivas, el cliente acumula <strong>1 FusaCoin</strong> en su billetera digital.</td>
      </tr>
      <tr>
        <td><strong>Valor de Conversión</strong></td>
        <td><strong>1 FusaCoin = $1 COP</strong> de descuento directo en compras futuras.</td>
      </tr>
      <tr>
        <td><strong>Límite de Redención</strong></td>
        <td>Se puede redimir hasta un máximo del <strong>30% del valor total</strong> de los productos permitidos en una sola orden.</td>
      </tr>
      <tr>
        <td><strong>Reversión por Cancelación</strong></td>
        <td>Si un pedido pagado con FusaCoins se cancela, los puntos se reintegran de forma inmediata a la billetera.</td>
      </tr>
    </tbody>
  </table>

  <h2>2.8 Asistente Virtual FusaBot IA</h2>
  <p>
    Ubicado en la esquina inferior derecha o accesible desde la barra superior. FusaBot es un agente inteligente entrenado con la base de conocimientos de Fusagasugá:
  </p>
  <ul>
    <li>Recomienda productos y comercios específicos según la intención de búsqueda.</li>
    <li>Resuelve preguntas frecuentes sobre políticas de despacho, métodos de pago y redención de FusaCoins.</li>
    <li>Detecta incidentes graves (cobros duplicados, retrasos mayores a 5 días) y enlaza directamente con el chat en vivo de un Administrador y el formulario oficial de PQRS.</li>
  </ul>

  <h2>2.9 Garantías, Cancelaciones y Radicación de PQRS</h2>
  <p>
    El comprador cuenta con protección y trazabilidad institucional ante cualquier inconveniente:
  </p>
  <ul>
    <li><strong>Cancelación Autónoma:</strong> El comprador puede cancelar su orden con un solo clic si el pedido aún se encuentra en estado <span class="badge badge-orange">pending</span> (antes de despacho). El inventario y los puntos se restituyen al instante.</li>
    <li><strong>PQRS (Peticiones, Quejas, Reclamos y Sugerencias):</strong> Formulario estructurado para radicar quejas formales. Asigna un número de radicado con respuesta obligatoria antes de 48 horas hábiles por parte del equipo analista.</li>
  </ul>
</div>

<!-- SECCIÓN 3: MÓDULO DEL COMERCIANTE -->
<div class="page-break">
  <h1>3. Módulo del Comerciante (Merchant Center)</h1>

  <h2>3.1 Onboarding Comercial y Verificación KYC con RUT</h2>
  <p>
    Para vender en FusaShop, todo comerciante debe superar el protocolo de verificación de identidad y legalidad comercial (KYC - <em>Know Your Customer</em>):
  </p>
  <div class="flow-box">
[Registro Inicial como Comerciante] 
        ↓
[Diligenciamiento de Perfil Comercial: Razón Social, NIT/Cédula, Dirección en Sumapaz, Teléfono]
        ↓
[Carga de Documentación Tributaria: RUT en PDF o Foto Legible]
        ↓
[Revisión y Validación por Analista de Operaciones de FusaShop]
        ↓
[KYC Aprobado] → Concesión de Insignia de Verificación y Habilitación de Catálogo
  </div>

  <h2>3.2 Administración del Catálogo de Productos y Servicios</h2>
  <p>
    Desde la pestaña <strong>Catálogo de Productos</strong>, el comerciante puede publicar y editar su oferta comercial:
  </p>
  <ul>
    <li><strong>Información Básica:</strong> Título del producto, categoría principal (de las 14 opciones regionales), descripción enriquecida y precio en pesos colombianos ($ COP).</li>
    <li><strong>Gestión de Inventario:</strong> Control de existencias físicas disponibles. Al llegar a cero, el producto se marca como agotado impidiendo sobreventas.</li>
    <li><strong>Descuentos Promocionales (% OFF):</strong> Posibilidad de configurar porcentajes de descuento que se destacan en la vitrina con etiquetas visuales de oferta.</li>
    <li><strong>Adopción de FusaCoins:</strong> Casilla para definir si el producto acepta redención de puntos de lealtad.</li>
    <li><strong>Fotografías de Producto:</strong> Carga de imágenes en alta resolución con conversión automática a formato WebP para máxima velocidad de carga.</li>
  </ul>

  <h2>3.3 Despacho de Pedidos y Generación de Guías con Envia.com</h2>
  <p>
    Cuando un cliente realiza un pago exitoso, el comerciante recibe una notificación sonora y visual en su panel:
  </p>
  <ol>
    <li><span class="step-number">1</span> Ingrese a la sección <strong>Gestión de Pedidos</strong> y ubique la orden en estado <em>En Preparación</em>.</li>
    <li><span class="step-number">2</span> Revise la lista de productos y proceda al embalaje según los estándares de la transportadora.</li>
    <li><span class="step-number">3</span> Pulse el botón <strong>Generar Guía Envia.com</strong>. La plataforma calcula peso, volumen y genera el PDF oficial de la guía.</li>
    <li><span class="step-number">4</span> Imprima el rótulo adhesivo y péguelo en el paquete. La transportadora pasará a recolectar en la dirección registrada de su tienda.</li>
  </ol>

  <h2>3.4 Control de Inventario Rápido</h2>
  <p>
    La herramienta de <strong>Inventario Rápido</strong> permite al comerciante actualizar las existencias de todos sus productos en una sola pantalla tabular responsiva, evitando tener que abrir cada ficha de producto individualmente.
  </p>
</div>

<!-- SECCIÓN 3 CONTINUACIÓN -->
<div class="page-break">
  <h2>3.5 Finanzas Comerciales, Comisiones y Retiro de Fondos</h2>
  <p>
    FusaShop mantiene absoluta transparencia en la contabilidad y dispersión de fondos para cada empresa aliada:
  </p>
  <table>
    <thead>
      <tr>
        <th>Concepto Financiero</th>
        <th>Descripción y Porcentaje Aplicable</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Ventas Brutas</strong></td>
        <td>Total facturado y pagado por los clientes en productos de su tienda.</td>
      </tr>
      <tr>
        <td><strong>Comisión de Plataforma</strong></td>
        <td>Porcentaje pactado de intermediación tecnológica (habitualmente 8% - 10%) configurado en el sistema.</td>
      </tr>
      <tr>
        <td><strong>Descuentos Asumidos</strong></td>
        <td>Promociones directas de la tienda o participación en FusaCoins.</td>
      </tr>
      <tr>
        <td><strong>Gasto en Pauta</strong></td>
        <td>Inversión aprobada en banners publicitarios y tarjetas promocionales.</td>
      </tr>
      <tr>
        <td><strong>Ingreso Neto Retirable</strong></td>
        <td>Saldo neto a favor disponible para transferencia a la cuenta bancaria del comerciante.</td>
      </tr>
    </tbody>
  </table>

  <h3>Configuración del Método de Cobro:</h3>
  <p>
    El comerciante debe registrar su cuenta bancaria en el apartado <em>Configuración de Pagos</em>, seleccionando:
  </p>
  <ul>
    <li>Tipo de cuenta: Ahorros o Corriente.</li>
    <li>Entidad bancaria: Bancolombia, Davivienda, Banco de Bogotá, Nequi o Daviplata.</li>
    <li>Número de cuenta y titular certificado con certificación bancaria.</li>
  </ul>

  <h2>3.6 Pauta Publicitaria (Banners y Tarjetas Promocionales)</h2>
  <p>
    Para multiplicar sus ventas, los comercios verificados pueden solicitar espacios publicitarios destacados en la vitrina de inicio:
  </p>
  <ul>
    <li><strong>Banner Global en Home:</strong> Espacio visual de alto impacto rotativo en la parte superior e inferior de la página principal.</li>
    <li><strong>Tarjeta Promocional:</strong> Ficha visual destacada en la vitrina de promociones especiales vinculada a productos en oferta.</li>
    <li><strong>Procedimiento:</strong> El comercio solicita el espacio, carga la pieza gráfica, efectúa el pago de la tarifa fija establecida y el Analista de FusaShop aprueba la publicación en menos de 2 horas.</li>
  </ul>

  <h2>3.7 Mensajería Directa con Clientes y Chat con Analistas</h2>
  <p>
    El Merchant Center integra dos canales de comunicación en tiempo real:
  </p>
  <ul>
    <li><strong>Mensajes de Clientes:</strong> Canal de preguntas sobre especificaciones de productos, tallas, disponibilidad y coordinación de reservas.</li>
    <li><strong>Soporte Interno con Analista:</strong> Chat prioritario con los administradores de FusaShop para resolver incidentes de guías o facturas.</li>
  </ul>

  <h2>3.8 Centro de Reportes y Diagnóstico Causal de Ventas</h2>
  <p>
    La plataforma provee a cada negocio un informe inteligente de rendimiento que evalúa:
  </p>
  <ul>
    <li>Top de productos más vendidos y productos estancados sin rotación.</li>
    <li>Diagnóstico causal automático con recomendaciones para mejorar descripciones y fotografías.</li>
    <li>Botón de <strong>Exportar Reporte a CSV</strong> para integración con sistemas contables propios.</li>
  </ul>
</div>

<!-- SECCIÓN 4: MÓDULO DEL ANALISTA / ADMINISTRADOR -->
<div class="page-break">
  <h1>4. Módulo del Analista y Administrador (Operations Console)</h1>

  <h2>4.1 Panel Ejecutivo y Monitoreo Territorial</h2>
  <p>
    La <strong>Operations Console</strong> es el centro neurálgico de administración de FusaShop, exclusivo para usuarios con rol <span class="badge badge-orange">analyst</span>. Ofrece:
  </p>
  <ul>
    <li>Métricas en tiempo real: Volumen total de transacciones (GMV), pedidos completados, comercios activos y saldo de comisiones acumuladas.</li>
    <li>Distribución geográfica de ventas en Fusagasugá por comunas y veredas circunvecinas.</li>
    <li>Alertas tempranas de pedidos que superan las 48 horas sin despacho para intervención preventiva.</li>
  </ul>

  <h2>4.2 Gestión de Usuarios y Validación KYC</h2>
  <p>
    El analista es el custodio de la confianza en la plataforma. Desde el submódulo de usuarios puede:
  </p>
  <ul>
    <li>Revisar la bandeja de <strong>Comercios Pendientes por KYC</strong>, validando la autenticidad del RUT y la concordancia de la dirección física.</li>
    <li>Aprobar comercios otorgando la <em>Insignia Oficial de Verificación</em> o rechazar solicitudes con retroalimentación detallada.</li>
    <li>Suspender o bloquear temporalmente cuentas que incumplan las normas comunitarias o presenten quejas reiteradas.</li>
  </ul>

  <h2>4.3 Auditoría de Integraciones y Monitoreo de APIs</h2>
  <p>
    La vista de <strong>Auditoría de Integraciones</strong> registra cada llamada externa hacia Wompi, Factus DIAN, Envia.com y el servidor de correo:
  </p>
  <table>
    <thead>
      <tr>
        <th>Servicio</th>
        <th>Evento Monitoreado</th>
        <th>Acción ante Fallo</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Wompi</strong></td>
        <td>Webhooks de aprobación, firma SHA-256.</td>
        <td>Reintento manual de conciliación si el webhook original se perdió.</td>
      </tr>
      <tr>
        <td><strong>Factus DIAN</strong></td>
        <td>Emisión de factura y recepción de CUFE.</td>
        <td>Botón de re-emisión automática si el servicio de la DIAN estuvo caído.</td>
      </tr>
      <tr>
        <td><strong>Envia.com</strong></td>
        <td>Generación de guías y webhooks de rastreo.</td>
        <td>Regeneración de orden logística y sincronización de estado.</td>
      </tr>
    </tbody>
  </table>

  <h2>4.4 Bitácora Inmutable de Auditoría (AdminAuditLog)</h2>
  <p>
    Cada acción crítica ejecutada por un analista (aprobar un comercio, editar un saldo, cambiar configuraciones del sistema, anular una transacción) se registra de forma inmutable en la base de datos con:
  </p>
  <ul>
    <li>Identificador del administrador responsable.</li>
    <li>Dirección IP de conexión y User-Agent.</li>
    <li>Entidad y registro específico afectado.</li>
    <li>Motivo o justificación administrativa obligatoria.</li>
  </ul>
</div>

<!-- SECCIÓN 5: FAQ Y SOPORTE -->
<div class="page-break">
  <h1>5. Preguntas Frecuentes (FAQ) y Soporte Técnico</h1>

  <h2>5.1 Para Compradores</h2>
  <p><strong>¿Qué hago si mi dinero fue debitado pero mi pedido sigue en estado pendiente?</strong><br>
  En transacciones PSE, el banco puede demorar hasta 5 minutos en comunicar la aprobación. Si transcurrido ese tiempo el estado no cambia, abra el chat con FusaBot y escriba "cobro duplicado" para que un analista concilie la transacción de inmediato con la referencia bancaria.</p>

  <p><strong>¿Puedo devolver un producto si no cumple mis expectativas?</strong><br>
  Sí. La ley colombiana garantiza el derecho de retracto en compras no presenciales durante los primeros 5 días hábiles posteriores a la entrega. Puede iniciar la solicitud radicando una PQRS desde su perfil.</p>

  <h2>5.2 Para Comerciantes</h2>
  <p><strong>¿Cuánto tarda la aprobación de mi cuenta para empezar a vender?</strong><br>
  El equipo de analistas de FusaShop revisa las solicitudes de validación KYC en un plazo máximo de 24 horas hábiles a partir de la carga del RUT legible.</p>

  <p><strong>¿Cómo y cuándo recibo el dinero de mis ventas?</strong><br>
  Los fondos netos de los pedidos entregados a satisfacción se acumulan en su saldo a favor y se transfieren a su cuenta bancaria registrada en los cortes semanales (los días viernes), descontando la comisión tecnológica.</p>

  <h2>5.3 Canales Oficiales de Soporte</h2>
  <div class="no-break" style="margin-top: 15px; padding: 16px; background: #f0fdf4; border: 1.5px solid #10B981; border-radius: 12px;">
    <h3 style="color: #064e3b; margin-top: 0;">Mesa de Ayuda y Soporte Institucional FusaShop</h3>
    <ul style="list-style: none; margin-left: 0; space-y: 6px; font-size: 10pt;">
      <li>📍 <strong>Sede Operativa:</strong> Fusagasugá, Cundinamarca, Colombia</li>
      <li>✉️ <strong>Correo Electrónico de Soporte:</strong> soporte@fusashop.com</li>
      <li>⚖️ <strong>Atención de PQRS y Asuntos Legales:</strong> pqrs@fusashop.com</li>
      <li>🌐 <strong>Portal Web Oficial:</strong> https://fusashop.com</li>
      <li>🤖 <strong>Asistencia Virtual 24/7:</strong> FusaBot Asistente IA dentro de la plataforma</li>
    </ul>
  </div>
</div>

</body>
</html>`;

const tempHtmlPath = 'docs/Manual_de_Usuario_FusaShop.html';
const outputPdfPath = 'docs/Manual_de_Usuario_FusaShop.pdf';

fs.writeFileSync(tempHtmlPath, htmlContent, 'utf8');
console.log('Archivo HTML generado en:', tempHtmlPath);

const edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const fullHtmlUrl = 'file:///' + path.resolve(tempHtmlPath).replace(/\\/g, '/');
const fullPdfPath = path.resolve(outputPdfPath);

console.log('Compilando PDF con Microsoft Edge Headless...');
try {
    execSync(`"${edgePath}" --headless=new --disable-gpu --no-pdf-header-footer --run-all-compositor-stages-before-draw --print-to-pdf="${fullPdfPath}" "${fullHtmlUrl}"`, { stdio: 'inherit' });
    console.log('✅ Manual de Usuario generado con éxito en:', outputPdfPath);
} catch (err) {
    console.error('Error al generar PDF con Edge:', err.message);
}
