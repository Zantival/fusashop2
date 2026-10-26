<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reportar Registro No Autorizado | FusaShop</title>
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
      width: min(100%, 540px);
      padding: 38px 32px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
      text-align: left;
    }
    .brand {
      text-align: center;
      font-weight: 900;
      font-size: 30px;
      margin-bottom: 24px;
      letter-spacing: -0.5px;
    }
    .brand .fus { color: #10B981; }
    .brand .a { color: #2563EB; }
    .brand .shop { color: #F97316; }
    .subbrand {
      font-size: 11px;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 1px;
      text-align: center;
      margin-top: -18px;
      margin-bottom: 24px;
      font-weight: 700;
    }
    .security-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 20px;
      background-color: #fee2e2;
      color: #b91c1c;
      font-size: 12px;
      font-weight: 800;
      margin-bottom: 14px;
    }
    h1 {
      font-size: 22px;
      margin: 0 0 12px;
      color: #0f172a;
      font-weight: 800;
      line-height: 1.3;
    }
    p {
      color: #475569;
      line-height: 1.6;
      font-size: 14px;
      margin: 0 0 16px;
    }
    .notice {
      padding: 16px 18px;
      border-radius: 14px;
      background-color: #fff1f2;
      border: 1px solid #fecdd3;
      color: #9f1239;
      font-size: 13px;
      line-height: 1.6;
      margin: 20px 0;
    }
    .check {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      margin: 22px 0 20px;
      color: #1e293b;
      font-size: 13px;
      line-height: 1.6;
      cursor: pointer;
    }
    .check input {
      margin-top: 3px;
      accent-color: #e11d48;
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    .button-danger {
      width: 100%;
      padding: 14px 24px;
      border: 0;
      border-radius: 14px;
      background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
      color: white;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 6px 16px rgba(225, 29, 72, 0.3);
      transition: all 0.2s ease;
    }
    .button-danger:hover {
      opacity: 0.95;
      transform: translateY(-1px);
    }
    .cancel-link {
      display: block;
      text-align: center;
      margin-top: 16px;
      color: #64748b;
      font-size: 13px;
      text-decoration: none;
      font-weight: 600;
    }
    .cancel-link:hover {
      color: #0f172a;
    }
    .error {
      color: #be123c;
      font-size: 12px;
      margin-top: 6px;
      font-weight: 700;
    }
  </style>
</head>
<body>
  <main class="card">
    <div class="brand">
      <span class="fus">Fus</span><span class="a">a</span><span class="shop">Shop</span>
    </div>
    <div class="subbrand">Seguridad &amp; Protección de Identidad</div>

    <div class="security-badge">
      <span>🛡️ Alerta de Registro No Solicitado</span>
    </div>

    <h1>¿No reconoces este registro en FusaShop?</h1>
    
    <p>
      Estás reportando una cuenta creada recientemente utilizando la dirección de correo:
      <strong style="color: #0f172a; word-break: break-all;">{{ $user->email }}</strong>.
    </p>

    <div class="notice">
      <strong>⚠️ ¿Qué sucederá al confirmar este reporte?</strong><br>
      1. El código de verificación de 6 dígitos quedará <strong>anulado de inmediato</strong>.<br>
      2. La cuenta no verificada será <strong>bloqueada automáticamente</strong> en la plataforma.<br>
      3. Se enviará una <strong>alerta prioritaria de seguridad al equipo administrador</strong> para investigar el intento de registro no autorizado.
    </div>

    <form method="POST" action="{{ request()->fullUrl() }}">
      @csrf
      <label class="check">
        <input type="checkbox" name="confirm_report" value="1" required>
        <span>
          <strong>Confirmo que yo NO solicité este registro</strong> ni deseo crear una cuenta con este correo en FusaShop. Deseo bloquear el registro y notificar a administración.
        </span>
      </label>
      @error('confirm_report')
        <p class="error">{{ $message }}</p>
      @enderror

      <button class="button-danger" type="submit">
        🚨 Confirmar y Bloquear Cuenta No Autorizada
      </button>
    </form>

    <a href="{{ route('consumer.home') }}" class="cancel-link">
      Cancelar y regresar al inicio
    </a>
  </main>
</body>
</html>
