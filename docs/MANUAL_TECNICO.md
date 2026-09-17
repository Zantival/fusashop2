# Manual Técnico — FusaShop

Plataforma de comercio electrónico local (Fusagasugá) construida sobre Laravel 12 con vistas Blade, Tailwind CSS, Alpine.js y scripts auxiliares en Python para analítica.

---

## 1. Información general

| Elemento | Valor |
|---|---|
| Nombre del proyecto | FusaShop (`fusashop/platform`) |
| Repositorio | `Zantival/fusashop2` |
| Framework backend | Laravel 12 (PHP ^8.2) |
| Autenticación API | Laravel Sanctum 4 |
| Login social | Laravel Socialite 5 (Google, Facebook) |
| Frontend | Blade + Tailwind CSS 4 + Alpine.js 3 + Chart.js 4 |
| Build de assets | Vite 5 / Tailwind CLI |
| Base de datos | MySQL 8 (soporta SQLite para desarrollo) |
| Correo | Resend / Mailgun / SMTP (driver `log` en local) |
| Pagos | PayU LatAm (WebCheckout) |
| Analítica auxiliar | Python 3 (`python/analytics.py`, `python/ml_analyzer.py`) |
| Idioma/zona | `es` / `America/Bogota` |

### Roles del sistema

| Rol | Valor en BD | Alcance |
|---|---|---|
| Cliente | `consumer` | Catálogo, carrito, checkout, pedidos, reseñas, fidelización, PQRS, chat |
| Comerciante (MiPyme) | `merchant` | Perfil de empresa + KYC, productos, inventario, pedidos, reseñas, finanzas, banners |
| Administrador / Analista | `analyst` | Usuarios, KYC, pedidos, pagos, banners, reportes, PQRS, análisis ML |

---

## 2. Arquitectura

```
Navegador (Blade + Tailwind + Alpine + Chart.js)
        │  HTTP (sesión web)                 │ HTTP (token Sanctum)
        ▼                                    ▼
routes/web.php                        routes/api.php
        │                                    │
        ▼                                    ▼
Middleware: SecurityHeaders → auth → verified → role → EnsureKycApproved
        │
        ▼
Controladores (Auth, Consumer, Merchant, Analyst, Chat, PQRS, Payment, Api)
        │                    │                         │
        ▼                    ▼                         ▼
Modelos Eloquent      Servicios (PayUService)   Procesos Python (symfony/process)
        │                    │
        ▼                    ▼
   MySQL / SQLite      PayU WebCheckout ──webhook──► PaymentController@webhook
        │
        ▼
Notificaciones (BD + correo), Eventos/Listeners, Storage local (`storage/app/public`)
```

Patrón: MVC clásico de Laravel, sin capa de repositorios. La lógica de negocio vive en los controladores; `App\Services\PayUService` es el único servicio dedicado.

### Estructura de directorios

```
app/
  Events/           ProductOutOfStock, MessageSent
  Listeners/        NotifySellerOutOfStock
  Mail/             OrderReceipt (mailable del recibo de compra)
  Models/           User, Product, Order, OrderItem, Cart, CartItem, Review,
                    CompanyProfile, Coupon, Message, PQRS, LoyaltyPoint,
                    GlobalBanner, BannerRequest, PasswordResetOfflineRequest
  Notifications/    14 notificaciones (pedidos, KYC, PQRS, chat, banners, stock…)
  Services/         PayUService
  Http/
    Controllers/    Auth/, Consumer/, Merchant/, Analyst/, Api, Chat, PQRS, Payment
    Middleware/     SecurityHeaders, RoleMiddleware, EnsureKycApproved, CheckCookieConsent
bootstrap/app.php   Registro de rutas, middleware y manejo de excepciones
config/             app, auth, cache, database, filesystems, logging, sanctum, services, session
database/
  migrations/       Esquema completo (ver §5)
  seeders/          DatabaseSeeder (usuarios demo, 10 productos, 1 pedido)
public/             Front controller y assets compilados (`public/assets/css/app.css`)
python/             analytics.py (reportes CLI), ml_analyzer.py (scoring de comercios)
resources/views/    Blade agrupado por rol: consumer/, merchant/, analyst/, auth/, chat/, pqrs/…
routes/             web.php, api.php, console.php
tests/              PHPUnit (tests/Feature, tests/Unit)
Dockerfile          Imagen de producción PHP 8.2 + Apache con build de assets en Node 20
Tickets/            Copia parcial/histórica del esqueleto del proyecto (no se ejecuta)
```

---

## 3. Instalación y ejecución

### Requisitos

- PHP 8.2+ con extensiones `pdo_mysql`, `mbstring`, `zip`, `exif`, `bcmath`, `gd`
- Composer 2
- Node.js 20+ y npm
- MySQL 8 (o SQLite para desarrollo)
- Python 3 (opcional, para los reportes y el análisis ML del panel de administrador)

### Instalación local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link      # expone storage/app/public
npm run build                 # compila Tailwind a public/assets/css/app.css
php artisan serve             # http://localhost:8000
```

Durante el desarrollo del CSS: `npm run css:watch`.

### Usuarios de prueba (seeder)

| Rol | Correo | Contraseña |
|---|---|---|
| analyst | `admin@fusashop.com` | `password123` |
| merchant | `tienda@fusashop.com` | `password123` |
| merchant | `tech@fusashop.com` | `password123` |
| consumer | `juan@fusashop.com` | `password123` |
| consumer | `maria@fusashop.com` | `password123` |

### Variables de entorno relevantes

| Variable | Descripción |
|---|---|
| `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL` | Configuración base de Laravel |
| `APP_LOCALE=es`, `APP_TIMEZONE=America/Bogota` | Localización |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Conexión a base de datos |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION`, `FILESYSTEM_DISK` | Infraestructura (por defecto `file`/`sync`/`local`) |
| `MAIL_MAILER`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `RESEND_API_KEY`, `MAILGUN_*` | Envío de correo |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | Login con Google (callback `{APP_URL}/auth/google/callback`) |
| `FACEBOOK_CLIENT_ID` / `FACEBOOK_CLIENT_SECRET` | Login con Facebook (callback `{APP_URL}/auth/facebook/callback`) |
| `SERVICES_PAYU_*` (`merchant_id`, `api_key`, `account_id`, `test`) | Credenciales PayU; sin ellas se usan las de sandbox |

> En entorno `local`, `AppServiceProvider` fuerza `Mail::alwaysTo('garzonzanti@gmail.com')` para que el modo de prueba de Resend acepte los envíos. Debe revisarse antes de cualquier despliegue real.

### Despliegue

- **Docker**: el `Dockerfile` construye los assets con Node 20, instala dependencias con `composer install --no-dev --optimize-autoloader`, genera `APP_KEY`, crea el enlace `public/storage` y sirve con Apache (`mpm_prefork`) apuntando a `public/`.
- **PaaS**: README recomienda Railway, Render, Heroku o DigitalOcean App Platform. `vercel.json` solo contempla el build estático de assets, no la app PHP.
- Tras desplegar: `php artisan migrate --force`, `php artisan storage:link`, `php artisan config:cache route:cache view:cache`.
- Endpoint de salud: `GET /up`.

---

## 4. Rutas

### 4.1 Web (`routes/web.php`)

**Público**

| Método | Ruta | Acción |
|---|---|---|
| GET | `/` | Redirige a `consumer.home` |
| GET | `/privacidad` | Política de privacidad |
| POST | `/cookie-consent` | Guarda el consentimiento en sesión y cookie permanente |
| GET/POST | `/login`, `/register` | Autenticación (throttle 5/min) |
| GET/POST | `/forgot-password-offline` | Solicitud de reseteo manual atendida por un analista |
| GET | `/auth/{provider}/redirect` y `/callback` | Login social (Google/Facebook) |
| GET | `/shop`, `/shop/catalog`, `/shop/product/{id}` | Home, catálogo con filtros y ficha de producto |
| GET | `/shop/directory`, `/shop/directory/brand/{id}` | Directorio de comercios aprobados y perfil de marca |
| GET | `/files/{disk}/{path}` | Servidor local de archivos de `storage/app/public` |

**Autenticado (cualquier rol)**: `/notifications` (+ `read`, `read-all`), `/account/profile`, `/chat` (`show`, `poll`, `store`, `destroy`), `/pqrs` (`create`, `store`, `show`), verificación de correo (`/email/verify…`), `POST /logout`.

**Cliente** (`auth` + `verified` + `role:consumer`, prefijo `/shop`): `cart`, `cart/add`, `cart/item/{item}` (PATCH/DELETE), `checkout` (GET/POST), `orders`, `orders/{id}/receipt`, `product/{id}/review`.

**Comerciante** (`auth` + `verified` + `role:merchant`, prefijo `/merchant`):
- Sin KYC aprobado: `profile` (GET/POST), `support/contact`.
- Con KYC aprobado (`EnsureKycApproved`): `dashboard`, `store/edit`, `products` (CRUD), `inventory` (GET/POST), `orders` y `orders/{order}/status`, `reviews` y `reviews/{review}/reply`, `finances`, `banner-request` (GET/POST).

**Administrador** (`auth` + `role:analyst`, prefijo `/admin`): `dashboard`, `users` (CRUD, `toggle-block`, `rut`, `kyc`), `orders`, `payments`, `sales-report-print`, `run-ml`, `banners` (alta, toggle, borrado, logo global), `banner-requests/{id}` y `approve`, `reviews-report`, `pqrs` y `pqrs/{pqrs}/reply`.

### 4.2 API (`routes/api.php`, Sanctum)

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| POST | `/api/login` | No | Devuelve token Sanctum |
| POST | `/api/register` | No | Alta de cliente + token |
| GET | `/api/products` | No | Listado de productos activos |
| GET | `/api/products/{id}` | No | Detalle de producto |
| POST | `/api/logout` | Sí | Revoca el token |
| GET/POST | `/api/cart` | Sí | Consulta y alta de ítems |
| DELETE | `/api/cart/{item}` | Sí | Elimina ítem |
| GET | `/api/orders` | Sí | Pedidos del usuario |
| GET | `/api/analytics` | Sí | Ventas mensuales y por categoría (`AnalystController@analyticsData`) |

---

## 5. Modelo de datos

Tablas principales (ver `database/migrations/`):

| Tabla | Campos destacados |
|---|---|
| `users` | `name`, `email` único, `password` (nullable por login social), `role` enum(`consumer`,`merchant`,`analyst`), `phone`, `address`, `avatar`, `is_blocked`, `email_verified_at` |
| `company_profiles` | `merchant_id`→users, `company_name`, `business_type`, `phone`, `rut_path`, `camara_comercio_path`, `logo_path`, `banners_path` (json), `description`, `address`, `whatsapp`, `google_maps_url`, `latitude`, `longitude`, `employee_count`, `kyc_status` enum(`pending`,`approved`,`rejected`), `kyc_notes` |
| `products` | `merchant_id`, `name`, `slug` único, `description`, `price` decimal(10,2), `stock`, `category`, `image`, `images` (json), `available_options` (json), `specifications` (json), `is_active`; índices en `category+is_active` y `merchant_id` |
| `carts` / `cart_items` | Carrito 1–1 por usuario; ítems con `quantity`, `selected_options` (json), único `cart_id+product_id` |
| `orders` | `user_id`, `total`, `discount`, `points_used`, `points_earned`, `status` enum(`pending`,`processing`,`shipped`,`delivered`,`cancelled`), `shipping_address`, `payment_method`, `payment_status` enum(`pending`,`paid`,`failed`), `notes` |
| `order_items` | `order_id`, `product_id` (restrict), `quantity`, `price`, `selected_options` (json) |
| `reviews` | `product_id`, `user_id`, `rating` 1–5, `comment`, `merchant_reply`, `replied_at` |
| `loyalty_points` | `user_id`, `merchant_id`, `points` (saldo por comercio) |
| `coupons` | `merchant_id`, `code` único, `type` (`fixed`/`percentage`), `value`, `min_order_amount`, `expires_at`, `usage_limit`, `used_count`, `is_active` |
| `messages` | `sender_id`, `receiver_id`, `product_id` nullable, `content`, `is_read` |
| `p_q_r_s` | `user_id`, `type` enum(`peticion`,`queja`,`reclamo`,`sugerencia`), `subject`, `content`, `status` enum(`pending`,`in_review`,`resolved`,`closed`), `admin_response`, `resolved_at` |
| `global_banners` | `title`, `image_path`, `link_url`, `is_active`, `sort_order` |
| `banner_requests` | `user_id`, `image_path`, `payment_proof_path`, `status`, `cost`, `notes` |
| `password_reset_offline_requests` | `email`, `status` (`pending`/`resolved`) |
| `notifications` | Tabla estándar de Laravel (uuid, `notifiable`, `data`, `read_at`) |
| `personal_access_tokens`, `sessions`, `cache`, `password_reset_tokens` | Infraestructura de Laravel/Sanctum |

Relaciones Eloquent clave: `User hasMany Product (merchant_id)`, `User hasOne Cart`, `User hasOne CompanyProfile`, `User hasMany Order`, `Order hasMany OrderItem`, `Product hasMany Review`, `Cart hasMany CartItem`.

---

## 6. Flujos funcionales

### 6.1 Registro y autenticación

1. `AuthController@register` valida (contraseña mínima de 8, rol `consumer` o `merchant`), crea el usuario, crea el carrito si es cliente y dispara `Registered` → correo de verificación personalizado (definido en `AppServiceProvider`), `WelcomeNotification` y `NewUserNotification` a los analistas.
2. `redirectByRole()` envía a verificación de correo si falta, y luego a `merchant.profile` / `merchant.dashboard`, `analyst.dashboard` o `consumer.home`.
3. En login se bloquea el acceso si `is_blocked` es verdadero; los intentos están limitados a 5 por minuto.
4. `User::hasVerifiedEmail()` devuelve `true` siempre para el rol `analyst` (omite la verificación para administradores).
5. Recuperación de contraseña: no hay flujo automático por correo; `PasswordResetOfflineRequest` registra la solicitud y notifica a los analistas para gestión manual.
6. Login social vía `SocialAuthController` (Google/Facebook) usando `provider`/`provider_id`.

### 6.2 Onboarding y KYC del comerciante

1. El comerciante completa `merchant/profile`: datos de empresa, RUT en PDF (obligatorio), Cámara de Comercio, logo, banners, dirección y coordenadas.
2. El perfil queda con `kyc_status = pending` y se notifica a los analistas (`NewMerchantProfileNotification`).
3. `EnsureKycApproved` bloquea todo el panel del comerciante mientras el estado no sea `approved`, redirigiendo a `merchant.profile` con el mensaje correspondiente.
4. El analista revisa el RUT (`/admin/users/{user}/rut`) y aprueba o rechaza (`/admin/users/{user}/kyc`), lo que dispara `KycDecisionNotification`.

### 6.3 Compra (checkout)

`ConsumerController@checkoutProcess`:

1. Valida dirección de envío y método de pago (`card`, `transfer`, `cash`).
2. Calcula el total del carrito y, si el cliente usa puntos, aplica el descuento con la equivalencia **1 punto = $50 COP**, descontando los saldos de `loyalty_points` por comercio.
3. Dentro de una transacción: crea el pedido (`payment_status = paid`, `status = pending`), crea los ítems, descuenta stock y lanza `ProductOutOfStock` cuando el stock llega a 0 (listener `NotifySellerOutOfStock`).
4. Acumula puntos nuevos: **1 punto por cada $1.000** del subtotal neto por comercio.
5. Notifica a cada comerciante involucrado (`NewOrderNotification`), vacía y elimina el carrito.
6. Redirige a `consumer.orders`; el recibo se consulta en `/shop/orders/{id}/receipt`.

### 6.4 Pagos con PayU

- `PayUService` construye los parámetros de WebCheckout y la firma `md5(apiKey~merchantId~referenceCode~amount~currency)` con referencia `ORDER-{id}-{timestamp}` y moneda COP; por defecto usa el sandbox de PayU.
- `PaymentController@response` interpreta `transactionState` (4 aprobado, 6 rechazado, 7/104 pendiente-error) y muestra `payment.result`.
- `PaymentController@webhook` recibe la confirmación servidor-a-servidor, extrae el id del pedido de la referencia, marca `paid`/`failed` y envía el recibo por correo (`OrderReceipt`).
- **Nota de estado**: `routes/web.php` no declara actualmente las rutas `payment.response` ni `payment.webhook` que `PayUService` referencia, y el checkout marca los pedidos como pagados directamente. La pasarela está implementada pero no conectada al flujo de compra.

### 6.5 Fidelización

`loyalty_points` guarda un saldo por par cliente–comercio. Se gana 1 punto por cada $1.000 de compra neta en ese comercio y se canjea a razón de $50 por punto en el checkout. El perfil del cliente y el recibo muestran los saldos vigentes.

### 6.6 Reseñas

Solo puede calificar quien tenga un pedido en estado `delivered` que contenga el producto (validación en `productDetail` y `productReview`; en caso contrario, HTTP 403). El comerciante responde desde `merchant/reviews`, y el analista consulta el informe consolidado en `/admin/reviews-report`.

### 6.7 Chat y PQRS

- Chat interno 1–1 basado en la tabla `messages` con *polling* (`GET /chat/{user}/poll`); existe el evento `MessageSent` y la notificación `NewMessageNotification`. Los enlaces "Contactar soporte" de cliente y comerciante abren un chat con el primer analista disponible.
- PQRS: el usuario crea la solicitud (`peticion`, `queja`, `reclamo`, `sugerencia`), los analistas la reciben (`NewPQRSNotification`), responden desde `/admin/pqrs` y el usuario recibe `PQRSResponseNotification`.

### 6.8 Publicidad (banners)

El comerciante sube una imagen y el comprobante de pago (`banner_requests`); el analista revisa, asigna el costo y aprueba, creando un `global_banner` visible en la portada. El gasto aprobado se descuenta en el módulo de finanzas del comerciante.

### 6.9 Panel del comerciante

- **Dashboard**: total de productos, agotados, ventas acumuladas, pedidos pendientes, últimos pedidos y reseñas, calificación promedio.
- **Inventario**: actualización masiva de stock y alerta de productos con stock ≤ 5.
- **Finanzas**: ventas brutas, descuentos por puntos prorrateados, inversión en publicidad aprobada e ingreso real (`brutas − puntos − publicidad`).
- **Pedidos**: cambio de estado con notificación `OrderStatusChanged` al cliente.

### 6.10 Panel del analista

Métricas globales (ventas, pedidos, ticket promedio, usuarios por rol), ventas mensuales (consulta compatible con MySQL y SQLite), pedidos por estado, ventas por empresa, productos más vendidos, censo por sectores económicos, KYC y banners pendientes, gestión de usuarios (incluye bloqueo), pagos, reporte de ventas imprimible y análisis ML.

`AnalystController@runMl` arma un JSON con `merchant_id`, `company_name`, `total_sales` y `avg_rating`, lo envía por STDIN a `python/ml_analyzer.py` mediante `symfony/process`, y muestra la clasificación devuelta (`Top`/`Average`/`Low Performer`, más `anomaly_detected`). Requiere `python3` disponible en el servidor.

---

## 7. Scripts Python

| Script | Uso | Descripción |
|---|---|---|
| `python/analytics.py` | `python3 python/analytics.py --report ventas\|usuarios\|productos\|all --output json\|csv` | Reportes CLI directos contra MySQL (`--host`, `--db`, `--user`, `--password`). Si faltan `mysql-connector-python` o la conexión, devuelve datos de demostración. |
| `python/ml_analyzer.py` | STDIN/STDOUT JSON | Scoring heurístico por comercio: `score = ventas*0.4 + rating*6`, clasificación en tres niveles y bandera de anomalía. No es un modelo entrenado. |

---

## 8. Seguridad

- **Headers** (`SecurityHeaders`, aplicado globalmente): `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, HSTS en HTTPS y una CSP que permite CDNs específicas (jsDelivr, unpkg, Google Fonts, Nominatim, OSRM).
- **Autorización por rol**: middleware `role:` con verificación de pertenencia; `EnsureKycApproved` para el panel comercial; comprobaciones de propiedad en carrito, productos y pedidos (HTTP 403).
- **Validación y saneamiento**: validación en todos los formularios; `strip_tags()` sobre textos libres (perfil, dirección, comentarios).
- **Rate limiting**: 5 intentos/min en login y registro, 6/min en reenvío de verificación.
- **Sesión y contraseñas**: regeneración de sesión al iniciar/cerrar, hash bcrypt (12 rondas), mínimo 8 caracteres, bloqueo de cuentas (`is_blocked`).
- **Cookies**: banner de consentimiento y middleware `CheckCookieConsent`.
- **Archivos**: subidas restringidas por tipo y tamaño (RUT PDF ≤ 2 MB, imágenes ≤ 3 MB); `PostTooLargeException` se traduce a un mensaje amigable.
- **Puntos a reforzar antes de producción**: retirar el `Mail::alwaysTo` de desarrollo, mover las credenciales PayU de sandbox a variables de entorno obligatorias, validar la firma del webhook de PayU y revisar la ruta `/files/{disk}/{path}` (sirve archivos desde `storage/app/public` a partir de parámetros de la URL).

---

## 9. Pruebas y calidad

```bash
php artisan test        # PHPUnit (phpunit.xml usa SQLite en memoria)
php artisan route:list  # Verificar rutas registradas
```

Actualmente solo existe `tests/Feature/ExampleTest.php`; no hay cobertura de los flujos de negocio (checkout, KYC, fidelización), lo que constituye la principal deuda técnica del proyecto.

---

## 10. Mantenimiento

| Tarea | Comando |
|---|---|
| Nueva migración | `php artisan make:migration nombre` |
| Aplicar migraciones | `php artisan migrate` (`--force` en producción) |
| Recargar datos demo | `php artisan migrate:fresh --seed` |
| Limpiar cachés | `php artisan optimize:clear` |
| Cachear para producción | `php artisan config:cache && php artisan route:cache && php artisan view:cache` |
| Recompilar CSS | `npm run build` |
| Enlace de almacenamiento | `php artisan storage:link` |
| Logs | `storage/logs/laravel.log` |

**Notas de mantenimiento**

- El directorio `Tickets/` contiene una copia parcial del esqueleto del proyecto y no participa en la ejecución.
- Módulo de cupones: existen `Coupon`, `Merchant\CouponController`, las vistas y la tabla `coupons`, pero no hay rutas registradas, por lo que la funcionalidad está inactiva.
- `QUEUE_CONNECTION=sync`: correos y notificaciones se envían en la misma petición. Para producción conviene configurar una cola (`database`/`redis`) y ejecutar `php artisan queue:work`.
- `BROADCAST_CONNECTION=log`: el chat funciona por *polling*; para tiempo real habría que configurar un driver de broadcasting.
