# Despliegue de FusaShop en Hostinger (sin Docker)

FusaShop puede ejecutarse localmente y en Hostinger con el mismo código. Cada entorno debe tener su propio archivo `.env`: conserva el `.env` local y crea el del servidor a partir de `.env.hostinger.example`. Nunca publiques ni copies al repositorio el `.env` real.

## Antes de subir

1. Contrata un plan con PHP 8.3, SSH/Composer y base de datos MySQL. Confirma que `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `gd`, `intl`, `zip` y `bcmath` estén habilitadas para PHP web y CLI.
2. Configura el dominio y HTTPS. En Google Cloud crea o actualiza el cliente OAuth para el dominio final y la URL `https://TU-DOMINIO/auth/google/callback`; no reutilices en producción el secreto OAuth local. Conserva las credenciales locales en tu `.env`.
3. Obtén llaves de producción para Wompi, Envia, Factus y Resend, y configura el remitente de Resend con un dominio verificado. No copies las claves de sandbox al servidor.
4. Crea la base MySQL y el usuario desde hPanel. Completa los valores del servidor en un `.env` fuera de la carpeta pública. Genera una `APP_KEY` nueva para producción antes de migrar; guarda una copia segura y no la cambies luego.

## Subida y configuración

1. Sube la aplicación fuera de `public_html` y coloca el contenido de la carpeta `public` en `public_html`, ajustando las rutas de `public_html/index.php` para apuntar al directorio real de la aplicación. No expongas `.env`, `storage`, `vendor` ni el código fuente desde la web.
2. En la raíz de la aplicación ejecuta `composer install --no-dev --optimize-autoloader --no-interaction`.
3. En un entorno con Node ejecuta `npm ci` y `npm run build`; sube el CSS generado en `public/assets/css` junto con los recursos públicos necesarios. Docker no es requerido.
4. Con el `.env` de producción listo, ejecuta `php artisan migrate --force`, `php artisan storage:link`, `php artisan config:cache`, `php artisan route:cache` y `php artisan view:cache`. Haz respaldo de la base antes de migrar datos existentes.
5. Configura una tarea cron para ejecutar cada minuto `php /RUTA/APP/artisan schedule:run`. Las colas usan la base de datos: programa un worker de ejecución corta según los límites de tu plan o usa VPS si Hostinger no permite mantenerlo activo. No dependas de `QUEUE_CONNECTION=sync` en producción.
6. Registra en Wompi y Envia los webhooks `https://TU-DOMINIO/api/webhooks/wompi` y `https://TU-DOMINIO/api/webhooks/envia`, respectivamente.

## Operación local

El archivo `.env` actual permanece intacto y fuera de Git. La simulación de Wompi solo responde cuando `APP_ENV=local` o `testing` y `WOMPI_ENV=sandbox`; en producción esas rutas devuelven 404, incluso si alguien dejó por error el ambiente de Wompi en sandbox.

Después de cambiar variables de entorno en el servidor, vuelve a ejecutar `php artisan config:cache`. Para depurar localmente, elimina la caché con `php artisan optimize:clear` si los valores no se actualizan.
