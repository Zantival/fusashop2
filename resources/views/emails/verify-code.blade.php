<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifica tu correo en FusaShop</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#1e293b;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:40px 15px;">
    <tr>
      <td align="center">
        <!-- Contenedor Principal -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;background-color:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 10px 25px -5px rgba(0,0,0,0.06),0 8px 10px -6px rgba(0,0,0,0.04);border:1px solid #e2e8f0;">
          
          <!-- Encabezado Corporativo Oficial FusaShop -->
          <tr>
            <td style="padding:32px 36px 26px;text-align:center;background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
                <tr>
                  <td align="center">
                    <div style="font-size:32px;font-weight:900;letter-spacing:-0.5px;line-height:1;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
                      <span style="color:#10B981;">Fus</span><span style="color:#2563EB;">a</span><span style="color:#F97316;">Shop</span>
                    </div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:7px;letter-spacing:1px;text-transform:uppercase;font-weight:700;">
                      El epicentro comercial del Sumapaz · Fusagasugá
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Indicador de Paso (Paso 1 de 2) -->
          <tr>
            <td style="background-color:#ecfdf5;padding:10px 24px;border-bottom:1px solid #d1fae5;text-align:center;">
              <span style="font-size:12px;font-weight:800;color:#047857;letter-spacing:0.5px;text-transform:uppercase;">
                Paso 1 de 2: Verificación de identidad por correo
              </span>
            </td>
          </tr>

          <!-- Cuerpo Principal -->
          <tr>
            <td style="padding:38px 36px 30px;">
              
              <h1 style="margin:0 0 14px;font-size:24px;font-weight:900;color:#0f172a;line-height:1.3;text-align:center;">
                Confirma tu correo electrónico
              </h1>

              <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#475569;text-align:center;">
                Hola <strong>{{ $user->name }}</strong>, ¡gracias por registrarte en FusaShop!
              </p>

              <p style="margin:0 0 24px;font-size:14px;line-height:1.6;color:#64748b;text-align:center;">
                Para proteger tu cuenta y completar tu registro como
                @if($user->isMerchant())
                  <strong style="color:#10B981;">Comercio Aliado de Fusagasugá</strong>,
                @else
                  <strong style="color:#2563EB;">Comprador</strong>,
                @endif
                ingresa el siguiente <strong>código de 6 números</strong> en la pantalla de verificación:
              </p>

              <!-- Tarjeta Destacada del Código OTP -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;">
                <tr>
                  <td align="center">
                    <div style="display:inline-block;background-color:#ecfdf5;border:2px dashed #10B981;border-radius:18px;padding:18px 34px;text-align:center;">
                      <span style="font-family:'Courier New',Courier,monospace;font-size:40px;font-weight:900;letter-spacing:10px;color:#047857;display:block;margin-left:10px;">
                        {{ $code }}
                      </span>
                    </div>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 26px;font-size:12px;color:#94a3b8;text-align:center;">
                ⏱️ Este código expira en <strong>30 minutos</strong> y es confidencial. Nunca te pediremos este código por llamada o redes sociales.
              </p>

              <!-- Botón CTA para ingresar código -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center">
                    <a href="{{ route('verification.notice') }}" target="_blank" style="display:inline-block;background:linear-gradient(135deg, #10B981 0%, #059669 100%);color:#ffffff;text-decoration:none;font-size:15px;font-weight:800;padding:14px 34px;border-radius:14px;box-shadow:0 6px 16px rgba(16,185,129,0.3);">
                      Ingresar Código en FusaShop &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <!-- MÓDULO DE SEGURIDAD: ¿NO FUISTE TÚ? REPORTAR PROBLEMA -->
              <div style="margin-top:36px;padding:22px;border:2px solid #fecdd3;border-radius:18px;background-color:#fff1f2;text-align:left;">
                <div style="display:flex;align-items:center;margin-bottom:8px;">
                  <span style="font-size:20px;margin-right:8px;">🚨</span>
                  <strong style="color:#9f1239;font-size:15px;font-weight:800;">¿No fuiste tú o no reconoces este registro?</strong>
                </div>
                <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#881337;">
                  Si tú no creaste esta cuenta en FusaShop ni solicitaste este código, es posible que otra persona haya digitado tu correo electrónico por error. <strong>No compartas este código con nadie.</strong>
                </p>
                <div style="text-align:center;margin:14px 0 10px;">
                  <a href="{{ $reportUrl }}" target="_blank" style="display:inline-block;background-color:#e11d48;color:#ffffff;text-decoration:none;font-size:13px;font-weight:800;padding:11px 22px;border-radius:12px;box-shadow:0 4px 10px rgba(225,29,72,0.25);">
                    ⚠️ Reportar problema: No fui yo
                  </a>
                </div>
                <p style="margin:0;font-size:11px;line-height:1.5;color:#9f1239;text-align:center;">
                  Al reportarlo, el código quedará invalidado, la cuenta se bloqueará preventivamente y nuestro equipo administrador recibirá una alerta de seguridad de inmediato.
                </p>
              </div>

            </td>
          </tr>

          <!-- Pie de Página Oficial -->
          <tr>
            <td style="padding:24px 36px;background-color:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;">
              <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:#64748b;">
                <span style="color:#10B981;">Fus</span><span style="color:#2563EB;">a</span><span style="color:#F97316;">Shop</span> · Fusagasugá, Cundinamarca, Colombia
              </p>
              <p style="margin:0 0 10px;font-size:11px;color:#94a3b8;">
                Atención al cliente: 320 910 6326 · 322 367 4364 | Comercio local seguro con Wompi y Envia.com
              </p>
              <p style="margin:0;font-size:10px;color:#cbd5e1;">
                <a href="{{ route('privacy') }}" style="color:#94a3b8;text-decoration:underline;">Privacidad</a> · 
                <a href="{{ route('terms') }}" style="color:#94a3b8;text-decoration:underline;">Términos</a> · 
                <a href="{{ route('returns') }}" style="color:#94a3b8;text-decoration:underline;">Reembolsos</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
