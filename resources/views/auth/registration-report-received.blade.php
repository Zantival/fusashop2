<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reporte de Seguridad Recibido | FusaShop</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 24px;
      background-color: #f1f5f9;
      font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      color: #1e293b;
    }
    .card {
      width: min(100%, 520px);
      padding: 42px 32px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      text-align: center;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
    }
    .brand {
      font-weight: 900;
      font-size: 30px;
      margin-bottom: 24px;
      letter-spacing: -0.5px;
    }
    .brand .fus { color: #10B981; }
    .brand .a { color: #2563EB; }
    .brand .shop { color: #F97316; }
    .shield-icon {
      width: 70px;
      height: 70px;
      display: grid;
      place-items: center;
      margin: 0 auto 20px;
      border-radius: 22px;
      background-color: #ecfdf5;
      color: #047857;
      font-size: 36px;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }
    h1 {
      font-size: 24px;
      color: #0f172a;
      font-weight: 800;
      margin: 0 0 12px;
    }
    p {
      color: #475569;
      line-height: 1.6;
      font-size: 14px;
      margin: 0 0 16px;
    }
    .status-box {
      padding: 16px 20px;
      border-radius: 14px;
      background-color: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #166534;
      font-size: 13px;
      line-height: 1.6;
      margin: 20px 0 28px;
      text-align: left;
    }
    .button-primary {
      display: inline-block;
      padding: 14px 28px;
      border-radius: 14px;
      background: linear-gradient(135deg, #10B981 0%, #059669 100%);
      color: #ffffff;
      text-decoration: none;
      font-weight: 800;
      font-size: 15px;
      box-shadow: 0 6px 16px rgba(16, 185, 129, 0.25);
      transition: all 0.2s ease;
    }
    .button-primary:hover {
      opacity: 0.95;
      transform: translateY(-1px);
    }
  </style>
</head>
<body>
  <main class="card">
    <div class="brand">
      <span class="fus">Fus</span><span class="a">a</span><span class="shop">Shop</span>
    </div>

    <div class="shield-icon" aria-hidden="true">🛡️</div>

    <h1>Reporte Procesado Exitosamente</h1>
    
    <p>
      Gracias por informarnos. Hemos registrado tu reporte de seguridad para el correo
      <strong style="color: #0f172a;">{{ $user->email ?? 'asociado' }}</strong>.
    </p>

    <div class="status-box">
      <strong>✅ Medidas inmediatas aplicadas:</strong><br>
      • El código OTP de verificación fue revocado y destruido.<br>
      • La cuenta no autorizada ha sido <strong>bloqueada preventivamente</strong> en el sistema.<br>
      • Una <strong>alerta prioritaria fue enviada al equipo administrador</strong> de FusaShop para su seguimiento y auditoría.
    </div>

    <p style="font-size: 13px; color: #64748b; margin-bottom: 26px;">
      No necesitas responder a ningún correo electrónico. Tu dirección está segura.
    </p>

    <a href="{{ route('consumer.home') }}" class="button-primary">
      Ir al Portal Principal de FusaShop &rarr;
    </a>
  </main>
</body>
</html>
