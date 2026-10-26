<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>¡Bienvenido a FusaShop!</title>
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

          <!-- Banner de Estado Verificado -->
          <tr>
            <td style="background-color:#ecfdf5;padding:12px 24px;border-bottom:1px solid #d1fae5;text-align:center;">
              <span style="font-size:13px;font-weight:800;color:#047857;letter-spacing:0.5px;">
                ✓ ¡CORREO ELECTRÓNICO VERIFICADO CON ÉXITO!
              </span>
            </td>
          </tr>

          <!-- Cuerpo Principal -->
          <tr>
            <td style="padding:38px 36px 32px;">
              
              <!-- Icono de Celebración Checkmark -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center">
                    <div style="width:68px;height:68px;line-height:68px;border-radius:22px;background:linear-gradient(135deg, #10B981 0%, #059669 100%);color:#ffffff;font-size:34px;font-weight:bold;text-align:center;box-shadow:0 8px 20px rgba(16,185,129,0.35);margin-bottom:18px;">
                      ✓
                    </div>
                  </td>
                </tr>
              </table>

              <h1 style="margin:0 0 10px;font-size:26px;font-weight:900;color:#0f172a;line-height:1.3;text-align:center;">
                ¡Bienvenido a FusaShop!
              </h1>
              
              <p style="margin:0 0 20px;font-size:16px;font-weight:700;color:#10B981;text-align:center;">
                Ahora sí, todo listo para explorar el catálogo
              </p>

              <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#475569;text-align:center;">
                Hola <strong>{{ $user->name }}</strong>, tu correo electrónico (<strong style="color:#0f172a;">{{ $user->email }}</strong>) ha sido validado correctamente. Tu cuenta ya se encuentra 100% activa.
              </p>

              <!-- Tarjetas de Beneficios FusaShop -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0 28px;">
                <tr>
                  <td style="padding:14px 18px;background-color:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;margin-bottom:10px;">
                    <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:4px;">
                      🛍️ Compra Local en Fusagasugá
                    </div>
                    <div style="font-size:12px;color:#64748b;line-height:1.5;">
                      Encuentra cafés de origen, orquídeas, artesanías, viveros, gastronomía y productos de los mejores comercios del Sumapaz.
                    </div>
                  </td>
                </tr>
                <tr><td style="height:10px;"></td></tr>
                <tr>
                  <td style="padding:14px 18px;background-color:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;margin-bottom:10px;">
                    <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:4px;">
                      🚚 Envíos Confiables con Envia.com
                    </div>
                    <div style="font-size:12px;color:#64748b;line-height:1.5;">
                      Cotización transparente en tiempo real y seguimiento de tu paquete hasta la puerta de tu casa.
                    </div>
                  </td>
                </tr>
                <tr><td style="height:10px;"></td></tr>
                <tr>
                  <td style="padding:14px 18px;background-color:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;">
                    <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:4px;">
                      💳 Pagos Protegidos &amp; FusaCoins
                    </div>
                    <div style="font-size:12px;color:#64748b;line-height:1.5;">
                      Paga seguro con Wompi (Nequi, Daviplata, PSE, tarjetas) y acumula puntos de fidelidad en tus compras.
                    </div>
                  </td>
                </tr>
              </table>

              @if($user->isMerchant())
                <!-- Tarjeta Exclusiva para Comerciantes -->
                <div style="margin-bottom:28px;padding:20px;border-radius:16px;background-color:#fff7ed;border:1px solid #fed7aa;text-align:left;">
                  <strong style="display:block;color:#9a3412;font-size:14px;font-weight:800;margin-bottom:6px;">
                    🏪 Tu Vitrina Digital como Comercio Aliado
                  </strong>
                  <p style="margin:0 0 14px;font-size:12px;line-height:1.6;color:#7c2d12;">
                    Como comerciante verificado, puedes configurar tu tienda, subir productos y servicios, gestionar tu inventario y recibir pagos directos a tu cuenta o billetera digital.
                  </p>
                  <div style="text-align:center;">
                    <a href="{{ route('merchant.dashboard') }}" target="_blank" style="display:inline-block;background-color:#ea580c;color:#ffffff;text-decoration:none;font-size:13px;font-weight:800;padding:10px 22px;border-radius:10px;">
                      Ir a mi Panel de Comercio &rarr;
                    </a>
                  </div>
                </div>
              @endif

              <!-- Botón Principal CTA: Ahora sí, a Explorar el Catálogo -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:10px 0 20px;">
                <tr>
                  <td align="center">
                    <a href="{{ route('consumer.catalog') }}" target="_blank" style="display:inline-block;background:linear-gradient(135deg, #10B981 0%, #047857 100%);color:#ffffff;text-decoration:none;font-size:16px;font-weight:900;padding:16px 36px;border-radius:16px;box-shadow:0 8px 20px rgba(16,185,129,0.35);letter-spacing:0.3px;">
                      🛍️ Explorar el Catálogo Ahora &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:24px 0 0;font-size:13px;line-height:1.6;color:#64748b;text-align:center;">
                ¿Tienes alguna pregunta o necesitas ayuda? Nuestro equipo está a tu disposición a través de nuestro canal de <a href="{{ route('pqrs.create') }}" style="color:#047857;font-weight:700;">PQRS y Soporte</a> o al WhatsApp <strong style="color:#0f172a;">320 910 6326</strong>.
              </p>

            </td>
          </tr>

          <!-- Pie de Página Oficial FusaShop -->
          <tr>
            <td style="padding:24px 36px;background-color:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;">
              <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:#64748b;">
                <span style="color:#10B981;">Fus</span><span style="color:#2563EB;">a</span><span style="color:#F97316;">Shop</span> · Fusagasugá, Cundinamarca, Colombia
              </p>
              <p style="margin:0 0 10px;font-size:11px;color:#94a3b8;">
                Apoyando el talento local, el agro y el turismo del Sumapaz
              </p>
              <p style="margin:0;font-size:10px;color:#cbd5e1;">
                <a href="{{ route('privacy') }}" style="color:#94a3b8;text-decoration:underline;">Política de Privacidad</a> · 
                <a href="{{ route('terms') }}" style="color:#94a3b8;text-decoration:underline;">Términos del Servicio</a> · 
                <a href="{{ route('returns') }}" style="color:#94a3b8;text-decoration:underline;">Devoluciones y Reembolsos</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
