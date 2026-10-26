# Inventario de servicios y recursos de terceros

Documento de trabajo para revisar privacidad, seguridad y licencias en FusaShop. La presencia de una integración en el código no confirma que esté activa en producción. Antes de publicar, contrastar cada fila con la configuración del entorno desplegado, los contratos y los avisos de cada proveedor.

| Servicio o recurso | Uso identificado | Datos que podrían salir de FusaShop | Revisión pendiente |
| --- | --- | --- | --- |
| Wompi | Pago y consulta de estado | Referencia, importe, datos de contacto necesarios y tokenización procesada por la pasarela | Confirmar sandbox/producción, contrato, webhook y aviso de privacidad vigente |
| Envia.com | Cotización, gestión de guía y rastreo, según flujo habilitado | Destinatario, teléfono, dirección, peso y datos del envío | Verificar si se generan guías automáticamente o solo se cotiza/rastrea; revisar retención |
| Factus | Facturación, según configuración | Identificación y datos requeridos en la factura | Confirmar activación, responsabilidades fiscales y entorno |
| Resend | Entrega de correo transaccional | Dirección de correo, nombre y contenido del mensaje | Verificar región, retención, contrato de tratamiento y listas de supresión |
| Google OAuth | Inicio de sesión social opcional | Identificador y perfil básico autorizados por la persona | Revisar scopes, aviso al usuario y configuración OAuth |
| OpenStreetMap / Nominatim / OSRM | Mapa, geocodificación o cálculo de ruta, donde se use | Consultas de ubicación, coordenadas e IP | Confirmar límites de uso, configuración y política de privacidad aplicable |
| YouTube / Vimeo | Reproducción de videos externos | IP y metadatos de reproducción al cargar el contenido | Usar carga bajo clic/consentimiento si hay cookies o seguimiento no esencial |
| Google Fonts, CDN, Unsplash y Svgrepo | Fuentes, bibliotecas, imágenes o iconos remotos | IP, agente de usuario y solicitudes de recursos | Preferir recursos locales; verificar licencias, atribución, versión e integridad |
| QR Server u otros generadores externos | Generación de QR, si se emplea | Texto o URL codificada, que puede contener identificadores | Confirmar qué datos se envían y sustituir por generación local si incorpora datos personales |

## Checklist de cierre

- Mantener un inventario de SDK, paquetes, scripts y dominios externos realmente cargados en el navegador.
- Verificar licencias de imágenes, fuentes, iconos y dependencias; guardar la licencia y atribución exigida.
- Fijar versiones y usar Subresource Integrity cuando el proveedor lo permita; retirar dependencias sin uso.
- Revisar cada SDK por permisos, cookies, telemetría, almacenamiento y transferencias internacionales.
- No enviar secretos, códigos OTP, datos completos de tarjeta ni información innecesaria a servicios externos.
- Actualizar la política de privacidad y cookies antes de habilitar una nueva integración no esencial.
- Registrar responsable, finalidad, categorías de datos, país/región, plazo de retención y mecanismo de eliminación por proveedor.
