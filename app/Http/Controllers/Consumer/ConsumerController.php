<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\{Product, Cart, CartItem, Order, OrderItem, CompanyProfile, Review, User, GlobalBanner};
use App\Support\PasswordPolicy;
use App\Events\ProductOutOfStock;
use App\Notifications\{NewProductReview, NewOrderNotification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConsumerController extends Controller
{
    public function home()
    {
        $featured = Product::active()->with('merchant.companyProfile')->latest()->take(8)->get();
        $flashDeals = Product::active()->with('merchant.companyProfile')
            ->orderByDesc('discount_percent')
            ->latest()
            ->take(4)
            ->get();
        $categories = Product::active()->select('category')->distinct()->pluck('category');
        $globalBanners = GlobalBanner::where('is_active', true)->orderBy('sort_order')->take(8)->get();
        $topBanners = $globalBanners->take(4)->values();
        $bottomBanners = $globalBanners->slice(4, 4)->values();
        return view('consumer.home', compact('featured', 'flashDeals', 'categories', 'globalBanners', 'topBanners', 'bottomBanners'));
    }

    public function catalog(Request $request)
    {
        $q = Product::active()->with('merchant.companyProfile');
        
        if ($request->filled('search'))        $q->search($request->search);
        if ($request->filled('category'))      $q->where('category', $request->category);
        if ($request->filled('min_price'))     $q->where('price', '>=', $request->min_price);
        if ($request->filled('max_price'))     $q->where('price', '<=', $request->max_price);
        if ($request->boolean('on_sale'))      $q->where('discount_percent', '>', 0);
        if ($request->boolean('allows_points')) $q->where('allows_points', true);
        if ($request->filled('merchant_id'))   $q->where('merchant_id', (int) $request->merchant_id);
        if ($request->filled('products')) {
            $ids = array_values(array_filter(array_map('intval', explode(',', (string) $request->products))));
            if (!empty($ids)) {
                $q->whereIn('id', $ids);
            }
        }
        
        $products = $q->paginate(12)->withQueryString();
        $categories = Product::active()->select('category')->distinct()->pluck('category');

        // Solo los primeros 10 para el carrusel de vendedores
        $directory = CompanyProfile::where('kyc_status', 'approved')->select('id','merchant_id','company_name','logo_path')->take(10)->get();

        if ($request->boolean('partial') || $request->ajax()) {
            return view('consumer.partials.products-grid', compact('products', 'categories'));
        }

        return view('consumer.catalog', compact('products', 'categories', 'directory'));
    }

    public function assistantSearch(Request $request)
    {
        $data = $request->validate([
            'q' => 'required|string|min:2|max:150',
        ]);

        $query = mb_strtolower(trim($data['q']), 'UTF-8');
        $normalized = \Illuminate\Support\Str::ascii($query);
        $user = Auth::user();
        $role = $user ? $user->role : 'consumer';

        // Administrador disponible para escalamiento o soporte
        $adminId = \App\Models\User::where('role', 'analyst')->value('id');

        // ==========================================
        // 1. ROL: COMERCIANTE / VENDEDOR
        // ==========================================
        if ($role === 'merchant') {
            return $this->handleMerchantAssistant($query, $normalized, $user, $adminId);
        }

        // ==========================================
        // 2. ROL: ADMINISTRADOR / ANALISTA
        // ==========================================
        if ($role === 'analyst') {
            return $this->handleAdminAssistant($query, $normalized, $user);
        }

        // ==========================================
        // 3. ROL: COMPRADOR / CLIENTE (O INVITADO)
        // ==========================================
        return $this->handleConsumerAssistant($query, $normalized, $data['q'], $adminId);
    }

    private function handleMerchantAssistant(string $query, string $normalized, \App\Models\User $user, ?int $adminId)
    {
        $links = [];
        $escalate = false;

        // 1. Error en Facturación Electrónica Factus / DIAN
        if (preg_match('/\b(error.*factur|fallo.*factur|error.*factus|fallo.*factus|error.*dian|cufe|xml.*error|error.*xml)\b/', $normalized)) {
            $answer = "🧾 **Solución a Errores de Facturación Electrónica con Factus:**\n\n"
                . "1. ⚙️ **Rango de Numeración DIAN**: Verifica en tu configuración que la resolución y rango de facturación autorizados por la DIAN no hayan expirado ni alcanzado el número consecutivo límite.\n"
                . "2. ⏱️ **Reintentos del Sistema**: Si la API de Factus experimentó lentitud, FusaShop reintenta la emisión en segundo plano. Puedes auditar el CUFE en tu Dashboard.\n"
                . "3. 👨‍💼 **Asistencia Técnica**: Si un pedido pagado continúa sin generar CUFE legal, comunícate con el Administrador para forzar la reemisión.";
            $links[] = ['label' => '📊 Ver Dashboard y Ventas', 'url' => route('merchant.dashboard')];
            if ($adminId) {
                $links[] = ['label' => '💬 Solicitar Revisión con Administrador', 'url' => route('chat.show', $adminId), 'primary' => true];
            }
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => true]);
        }

        // 2. Error de Envíos / Guías Envia.com
        if (preg_match('/\b(error.*envia|fallo.*envia|error.*guia|fallo.*guia|no.*genera.*guia|error.*despacho|flete.*error)\b/', $normalized)) {
            $answer = "🚚 **Solución a Inconvenientes con Guías de Envia.com:**\n\n"
                . "1. 🏢 **Dirección de Origen Completa**: En tu *Perfil Comercial*, verifica que la dirección de tu negocio tenga nomenclatura exacta (calle/carrera, número, barrio) y teléfono válido.\n"
                . "2. ⚖️ **Peso y Dimensiones**: Asegúrate de que el producto tenga peso (en kg) y dimensiones mayores a 0; de lo contrario Envia no puede liquidar el flete.\n"
                . "3. 📦 **Generación de Guía**: Puedes ingresar a *Pedidos para Despachar* y generar el rótulo en PDF para entregarlo a la transportadora.";
            $links[] = ['label' => '🏢 Revisar Dirección en Perfil', 'url' => route('merchant.profile')];
            $links[] = ['label' => '📦 Pedidos para Despachar', 'url' => route('merchant.orders'), 'primary' => true];
            if ($adminId) {
                $links[] = ['label' => '💬 Soporte Logístico con Administrador', 'url' => route('chat.show', $adminId)];
            }
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => true]);
        }

        // 3. Error al Publicar o Modificar Productos / Fotos
        if (preg_match('/\b(error.*producto|fallo.*producto|no.*deja.*publicar|error.*foto|error.*imagen|error.*subir|error.*variante|error.*precio)\b/', $normalized)) {
            $answer = "📸 **Solución a Problemas al Publicar o Editar Productos:**\n\n"
                . "1. 🖼️ **Formato y Peso de Imágenes**: Las fotos deben ser en formato JPG, PNG o WEBP y pesar máximo 5 MB cada una.\n"
                . "2. 💲 **Formato de Precio**: Ingresa únicamente números enteros (sin puntos, comas ni símbolos de moneda).\n"
                . "3. 🏷️ **Categoría y Stock**: Recuerda seleccionar una categoría del listado e indicar un stock inicial mayor a 0.\n"
                . "4. 📑 **Verificación KYC**: Para publicar productos tu perfil debe estar verificado con RUT y Cámara de Comercio aprobados.";
            $links[] = ['label' => '➕ Publicar Producto', 'url' => route('merchant.products.create'), 'primary' => true];
            $links[] = ['label' => '📦 Gestionar Mis Productos', 'url' => route('merchant.products')];
            $links[] = ['label' => '🏢 Estado de Verificación KYC', 'url' => route('merchant.profile')];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 4. Error de Comisiones, Liquidación o Retiro de Fondos
        if (preg_match('/\b(error.*comision|error.*retiro|error.*saldo|fallo.*pago|no.*puedo.*retirar|problema.*banco)\b/', $normalized)) {
            $answer = "💰 **Solución a Dudas y Problemas de Liquidación / Retiros:**\n\n"
                . "1. 📦 **Estado de Entrega**: Los ingresos de una venta quedan disponibles en tu saldo una vez la transportadora confirme la entrega exitosa del pedido.\n"
                . "2. 🏦 **Datos Bancarios**: Verifica que tu certificación bancaria o cuenta inscrita en el perfil comercial coincida con el titular registrado.\n"
                . "3. ⏱️ **Dispersión**: Las transferencias se procesan según los cortes semanales establecidos por la plataforma.";
            $links[] = ['label' => '💳 Consultar Saldo en Dashboard', 'url' => route('merchant.dashboard'), 'primary' => true];
            if ($adminId) {
                $links[] = ['label' => '💬 Contactar al Analista Financiero', 'url' => route('chat.show', $adminId)];
            }
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => true]);
        }

        // 5. Errores Generales / Falla en Plataforma / Soporte Administrativo
        if (preg_match('/\b(error|errores|problema|problemas|fallo|fallos|falla|fallas|bug|bugs|soporte|ayuda|administrador|admin|asesor|humano|reclamo)\b/', $normalized)) {
            $answer = "👨‍💼 **Soporte Técnico y Operativo para Comerciantes:**\n\n"
                . "Si experimentas un error o inconveniente con tu panel de ventas, sigue estas recomendaciones:\n\n"
                . "1. 🔄 **Actualizar página**: Presiona `Ctrl + F5` para recargar el panel con la versión más reciente.\n"
                . "2. 🔐 **Verificar sesión**: Si estuviste inactivo por varias horas, cierra sesión e ingresa nuevamente.\n"
                . "3. 💬 **Chat con el Administrador**: Para fallas en pedidos, liquidaciones o validación de documentos, nuestro equipo de soporte analítico está disponible para asistirte en tiempo real.";
            $escalate = true;
            $links[] = [
                'label' => '💬 Abrir Chat con el Administrador',
                'url' => $adminId ? route('chat.show', $adminId) : route('chat.index'),
                'primary' => true
            ];
            $links[] = ['label' => '📊 Volver al Dashboard', 'url' => route('merchant.dashboard')];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => true]);
        }

        // Sugerencias para optimizar productos y vender más
        if (preg_match('/\b(mejorar|vender mas|mas ventas|ventas|foto|fotos|imagen|imagenes|descripcion|descripciones|titulo|presentacion|consejo|consejos|tip|tips|estrategia|marketing|variante|variantes|colores|presentar)\b/', $normalized)) {
            $answer = "📈 **Consejos para mejorar la presentación de tus productos y vender más en FusaShop:**\n\n"
                . "1. 📸 **Fotografías profesionales**: Usa fotos nítidas con luz natural y fondo claro. Sube fotos desde varios ángulos y, si tu producto tiene variantes de color, asigna la foto real de cada color.\n"
                . "2. ✍️ **Títulos y descripciones atractivas**: Especifica medidas exactas, materiales de confección o elaboración, instrucciones de cuidado y tiempos de despacho.\n"
                . "3. 🪙 **Activa la redención de FusaCoins**: En la edición de tu producto, marca la opción *'Permitir redimir FusaCoins en este producto'*. Los compradores buscan productos con la insignia dorada para aprovechar sus puntos.\n"
                . "4. 📦 **Inventario y stock al día**: Mantén el stock real actualizado para evitar cancelaciones involuntarias y mantener una reputación impecable.";
            $links[] = ['label' => '📦 Gestionar mis productos', 'url' => route('merchant.products')];
            $links[] = ['label' => '➕ Publicar nuevo producto', 'url' => route('merchant.products.create')];
        } elseif (preg_match('/\b(envia|envio|envios|guia|guias|despacho|despachos|paquete|flete|recoleccion|transportadora)\b/', $normalized)) {
            $answer = "🚚 **Envíos y Logística con Envia.com:**\n\n"
                . "Cuando un cliente paga su compra, el sistema genera la guía de Envia.com automáticamente. Puedes descargar e imprimir el rótulo en PDF desde tu módulo de pedidos, embalar adecuadamente el paquete y entregarlo a la transportadora asignada (o llevarlo a una sede Envia de Fusagasugá).";
            $links[] = ['label' => '📦 Ver pedidos para despachar', 'url' => route('merchant.orders')];
        } elseif (preg_match('/\b(factura|facturas|factus|dian|cufe|electronica|iva|impuesto|xml)\b/', $normalized)) {
            $answer = "🧾 **Facturación Electrónica con Factus:**\n\n"
                . "FusaShop emite la factura electrónica legal ante la DIAN mediante Factus inmediatamente después de confirmado el pago con Wompi. Cada factura cuenta con código CUFE, PDF y XML oficial. Los compradores pueden descargarla desde su historial de pedidos.";
            $links[] = ['label' => '📊 Ver métricas en dashboard', 'url' => route('merchant.dashboard')];
        } elseif (preg_match('/\b(comision|comisiones|pago|pagos|retiro|retirar|dinero|saldo|banco|transferencia|liquidar)\b/', $normalized)) {
            $answer = "💰 **Comisiones y Retiro de Fondos:**\n\n"
                . "La plataforma deduce la comisión pactada por cada pedido entregado con éxito. En tu Dashboard puedes consultar el saldo neto disponible y solicitar la dispersión de tus ingresos directamente a tu cuenta bancaria.";
            $links[] = ['label' => '💳 Consultar saldo y finanzas', 'url' => route('merchant.dashboard')];
        } elseif (preg_match('/\b(kyc|rut|camara|comercio|documento|documentos|verificar|verificacion|aprobar|perfil)\b/', $normalized)) {
            $answer = "📑 **Validación Comercial (KYC):**\n\n"
                . "Para habilitar la publicación de productos y recepción de fondos, debes subir tu RUT y certificado de Cámara de Comercio vigente en tu perfil. El equipo administrativo valida los documentos en un plazo máximo de 24 horas hábiles.";
            $links[] = ['label' => '🏢 Ver mi perfil y documentos', 'url' => route('merchant.profile')];
        } else {
            $answer = "Hola " . e($user->name) . " 👋 Soy FusaBot para Comerciantes. Estoy aquí para ayudarte a optimizar la presentación de tus productos para vender más, resolver dudas sobre logística con Envia.com, facturación Factus o tus comisiones y retiros. ¿Qué deseas consultar?";
            $links[] = ['label' => '📈 Cómo vender más', 'url' => '#', 'prompt' => '¿Cómo mejorar la presentación de mis productos para vender más?'];
            $links[] = ['label' => '📦 Guías Envia.com', 'url' => route('merchant.orders')];
            $links[] = ['label' => '🏢 Perfil comercial', 'url' => route('merchant.profile')];
        }

        return response()->json([
            'answer' => $answer,
            'links' => $links,
            'products' => [],
            'escalate' => $escalate,
        ]);
    }

    private function handleAdminAssistant(string $query, string $normalized, \App\Models\User $user)
    {
        $links = [];
        $paidOrdersCount = \App\Models\Order::where('payment_status', 'paid')->count();
        $totalSales = (float) \App\Models\Order::where('payment_status', 'paid')->sum('total');
        $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
        $pendingKyc = \App\Models\CompanyProfile::where('kyc_status', 'pending')->count();
        $openPqrs = \App\Models\PQRS::whereIn('status', ['open', 'in_progress'])->count();

        if (preg_match('/\b(ventas|metricas|ingresos|totales|dinero|recaudo|facturado)\b/', $normalized)) {
            $answer = "📊 **Resumen Financiero y de Operaciones:**\n\n"
                . "- **Ventas Pagadas Totales**: $" . number_format($totalSales, 0, ',', '.') . " COP\n"
                . "- **Pedidos Pagados Aprobados**: {$paidOrdersCount}\n"
                . "- **Pedidos Pendientes**: {$pendingOrders}\n"
                . "- **PQRS en Gestión**: {$openPqrs}\n\n"
                . "Puedes ver el detalle completo en los tableros analíticos:";
            $links[] = ['label' => '📊 Dashboard General', 'url' => route('analyst.dashboard')];
        } elseif (preg_match('/\b(kyc|comercio|comercios|verificar|vendedor|vendedores|documento|documentos)\b/', $normalized)) {
            $answer = "🏢 **Verificaciones KYC de Comerciantes:**\n\n"
                . "Actualmente hay **{$pendingKyc} comercios pendientes** por validación de documentos (RUT y Cámara de Comercio). Recuerda auditar la autenticidad antes de autorizar la publicación de catálogo.";
            $links[] = ['label' => '🏢 Validar Comercios en Panel', 'url' => route('analyst.dashboard')];
        } elseif (preg_match('/\b(pqrs|reclamo|reclamos|queja|quejas|solicitud|ticket|tickets)\b/', $normalized)) {
            $answer = "📑 **Bandeja de PQRS y Solicitudes:**\n\n"
                . "Hay **{$openPqrs} casos activos** que requieren seguimiento o respuesta según los términos legales de SLA (Ley 1755).";
            $links[] = ['label' => '📑 Ir a Gestión de PQRS', 'url' => route('pqrs.index')];
        } elseif (preg_match('/\b(log|logs|error|errores|fallo|fallos|auditoria|wompi|envia|factus)\b/', $normalized)) {
            $failedLogsCount = \App\Models\IntegrationLog::where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count();
            $answer = "🛡️ **Auditoría e Integraciones:**\n\n"
                . "Se han registrado **{$failedLogsCount} incidentes** en logs de integración (Wompi / Factus / Envia.com) durante los últimos 7 días. Los pedidos en estado de reintento pueden ser reprocesados desde el módulo de logs.";
            $links[] = ['label' => '🛡️ Ver Auditoría en Dashboard', 'url' => route('analyst.dashboard')];
        } else {
            $answer = "Hola Administrador " . e($user->name) . " 🛡️\n\n"
                . "Soy el Asistente Interno FusaBot. Tengo acceso a las métricas del sistema:\n"
                . "• Pedidos pagados: **{$paidOrdersCount}** ($" . number_format($totalSales, 0, ',', '.') . " COP)\n"
                . "• Comercios pendientes KYC: **{$pendingKyc}**\n"
                . "• PQRS en curso: **{$openPqrs}**\n\n"
                . "¿Qué consulta o tarea administrativa deseas realizar?";
            $links[] = ['label' => '📊 Dashboard Analista', 'url' => route('analyst.dashboard')];
            $links[] = ['label' => '📑 Bandeja PQRS', 'url' => route('pqrs.index')];
            $links[] = ['label' => '💬 Mensajes de soporte', 'url' => route('chat.index')];
        }

        return response()->json([
            'answer' => $answer,
            'links' => $links,
            'products' => [],
            'escalate' => false,
        ]);
    }

    private function handleConsumerAssistant(string $query, string $normalized, string $rawQuery, ?int $adminId)
    {
        $links = [];
        $escalate = false;

        // =========================================================================
        // A. ERRORES ESPECÍFICOS Y RESOLUCIÓN TÉCNICA (NUNCA ENVIAR A COMPRAR)
        // =========================================================================

        // 1. Error en Pagos, Wompi, PSE, Tarjetas, Cobros o Transacciones
        if (preg_match('/\b(error.*pago|pago.*error|error.*pagar|no.*puedo.*pagar|no.*me.*deja.*pagar|rechazad|declinad|fallo.*pago|pago.*fallo|error.*pse|error.*wompi|error.*tarjeta|error.*nequi|error.*daviplata|cobro.*duplicado|descontaron.*dinero|debitaron.*no aparece|dinero.*descontado)\b/', $normalized)) {
            $answer = "💳 **Solución a Inconvenientes con Pagos (Wompi / PSE / Tarjeta):**\n\n"
                . "1. 🔒 **Fondos y Permisos**: Revisa que tu cuenta bancaria o billetera (Nequi / Daviplata) tenga saldo suficiente y compras por internet / PSE activas.\n"
                . "2. ⏱️ **Espera de Confirmación**: Wompi puede tardar hasta 5 minutos en confirmar pagos por PSE o corresponsal. No hagas un segundo pago hasta verificar el estado en *Mis Pedidos*.\n"
                . "3. 🛡️ **¿El dinero fue debitado pero el pedido no se confirmó?**: ¡Tu dinero está protegido! El Administrador puede conciliar tu transacción de inmediato en el sistema bancario.\n"
                . "4. 🔄 **Reintentar Pago**: Puedes volver a tu carrito de compras y elegir otro método de pago (por ejemplo, Tarjeta de Crédito/Débito en vez de PSE).";

            $escalate = true;
            if (Auth::check()) {
                $links[] = [
                    'label' => '💬 Hablar con el Administrador en Vivo',
                    'url' => $adminId ? route('chat.show', $adminId) : route('chat.index'),
                    'primary' => true
                ];
            } else {
                $links[] = [
                    'label' => '💬 Iniciar Sesión para Soporte en Vivo',
                    'url' => route('login'),
                    'primary' => true
                ];
            }
            $links[] = ['label' => '📝 Radicar PQRS Oficial', 'url' => route('pqrs.create')];
            $links[] = ['label' => '🛒 Ir a Mi Carrito', 'url' => route('consumer.cart')];
            $links[] = ['label' => '📦 Consultar Mis Pedidos', 'url' => Auth::check() ? route('consumer.orders') : route('login')];

            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => true]);
        }

        // 2. Error de Inicio de Sesión, Registro, Código OTP de 6 Dígitos, Contraseña o Bloqueo
        if (preg_match('/\b(error.*iniciar|error.*login|error.*registro|error.*clave|error.*contrasena|error.*codigo|codigo.*incorrecto|no.*llega.*codigo|error.*verific|no.*puedo.*entrar|no.*puedo.*ingresar|cuenta.*bloqueada|error.*google)\b/', $normalized)) {
            $answer = "🔐 **Solución a Inconvenientes de Acceso, Registro o Verificación:**\n\n"
                . "1. 📩 **Código de Verificación de 6 Dígitos**: Revisa tu carpeta de *Spam / Correo no deseado*. El código expira a los 30 minutos. Puedes pulsar en *'Reenviar código'* desde la pantalla de verificación.\n"
                . "2. 🌐 **Inicio de Sesión con Google**: Si es tu primera vez registrándote, el sistema te solicitará seleccionar tu rol (Cliente o Vendedor) e indicar tu número de teléfono.\n"
                . "3. 🔑 **Contraseña Olvidada**: En la pantalla de login haz clic en *'¿Olvidaste tu contraseña?'* para solicitar el restablecimiento seguro.\n"
                . "4. 🚫 **Cuenta Bloqueada**: Si tu cuenta fue bloqueada por seguridad o intentos fallidos, el Administrador puede reactivarla.";

            if (Auth::check()) {
                $links[] = [
                    'label' => '💬 Solicitar Desbloqueo al Administrador',
                    'url' => $adminId ? route('chat.show', $adminId) : route('chat.index'),
                    'primary' => true
                ];
            } else {
                $links[] = ['label' => '🔑 Ir a Iniciar Sesión', 'url' => route('login'), 'primary' => true];
                $links[] = ['label' => '📝 Completar Registro', 'url' => route('register')];
            }
            $links[] = ['label' => '📧 Verificar Código de Correo', 'url' => route('verification.notice')];

            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 3. Error en Envíos, Cobertura, Dirección o Guías de Transporte
        if (preg_match('/\b(error.*envio|error.*direccion|no.*calcula.*envio|no.*llega.*pedido|paquete.*retrasado|guia.*invalida|error.*cobertura)\b/', $normalized)) {
            $answer = "🚚 **Solución a Inconvenientes con Envíos y Logística:**\n\n"
                . "1. 📍 **Dirección de Entrega**: Asegúrate de incluir la nomenclatura completa (ej: Calle 8 # 5-20, Barrio Centro, Fusagasugá). Si faltan datos, el cotizador de Envia.com no puede calcular la tarifa.\n"
                . "2. 🔍 **Rastreo de Guía**: Si tu pedido ya fue despachado, consulta el número de guía de Envia.com en *Mis Pedidos* para ver el trayecto en vivo.\n"
                . "3. ⚠️ **Demoras de la Transportadora**: Si tu paquete presenta novedad en ruta, nuestro equipo de soporte gestiona la prioridad directamente con Envia.";

            $links[] = ['label' => '📦 Consultar Mis Pedidos y Guías', 'url' => Auth::check() ? route('consumer.orders') : route('login'), 'primary' => true];
            $links[] = ['label' => '📍 Mis Direcciones Guardadas', 'url' => Auth::check() ? route('consumer.addresses') : route('login')];
            if (Auth::check()) {
                $links[] = ['label' => '💬 Reportar Novedad al Administrador', 'url' => $adminId ? route('chat.show', $adminId) : route('chat.index')];
            }

            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 4. Error con el Carrito de Compras o Reserva de Stock
        if (preg_match('/\b(error.*carrito|no.*deja.*agregar|stock.*insuficiente|reserva.*vencida|error.*checkout)\b/', $normalized)) {
            $answer = "🛒 **Solución a Problemas con el Carrito de Compras:**\n\n"
                . "1. 📦 **Stock No Disponible**: Si otro usuario compró la última unidad disponible de un producto, el sistema te avisará en el carrito para removerlo o reducir la cantidad.\n"
                . "2. ⏳ **Tiempo de Reserva**: Al iniciar la orden de compra, el stock queda reservado durante 30 minutos. Si expira este tiempo, los productos vuelven al inventario del comercio.\n"
                . "3. 🔄 **Actualizar Carrito**: Si ves un aviso de error, entra a tu carrito y actualiza las cantidades para recalcular el total.";

            $links[] = ['label' => '🛒 Ir a Mi Carrito', 'url' => route('consumer.cart'), 'primary' => true];
            $links[] = ['label' => '🛍️ Explorar Catálogo', 'url' => route('consumer.catalog')];

            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 5. Casos Críticos (Estafas, cobro indebido, producto dañado, robo, demanda)
        $isCriticalIssue = (bool) preg_match(
            '/\b(estafa|robo|cobro.*indebido|no llega|no llego|extraviad|perdid|danad|rot|grave|demanda|abogado)\b/i',
            $normalized
        );

        if ($isCriticalIssue) {
            $escalate = true;
            $answer = "⚠️ **Atención Prioritaria con Soporte Administrativo:**\n\n"
                . "Lamento sinceramente el inconveniente con tu compra o pedido. Para resolver este caso con la mayor rapidez y seguridad, he habilitado la conexión directa para que te atienda un **Administrador en Vivo** a través de nuestro chat interno, o puedas formalizar tu solicitud mediante radicación de PQRS:";

            if (Auth::check()) {
                $links[] = [
                    'label' => '💬 Hablar con el Administrador en Vivo',
                    'url' => $adminId ? route('chat.show', $adminId) : route('chat.index'),
                    'primary' => true
                ];
            } else {
                $links[] = [
                    'label' => '💬 Iniciar Sesión para Chat en Vivo',
                    'url' => route('login'),
                    'primary' => true
                ];
            }

            $links[] = ['label' => '📝 Radicar PQRS Oficial', 'url' => route('pqrs.create')];
            $links[] = ['label' => 'ℹ️ Centro de Ayuda', 'url' => route('consumer.support.contact')];

            return response()->json([
                'answer' => $answer,
                'links' => $links,
                'products' => [],
                'escalate' => true,
            ]);
        }

        // 6. ERRORES GENERALES, FALLAS TÉCNICAS O SOLICITUD DE ASISTENCIA HUMANA
        if (preg_match('/\b(error|errores|problema|problemas|fallo|fallos|falla|fallas|bug|bugs|no funciona|no me deja|se trabo|se cayo|pantalla blanca|no carga|inconveniente|inconvenientes|soporte tecnico|ayuda tecnica|asesor|humano|persona|administrador|admin|soporte en vivo|agente|hablar con alguien|hablar con persona|atencion humana)\b/i', $normalized)) {
            $escalate = true;
            $answer = "🛠️ **Diagnóstico y Solución de Errores en FusaShop:**\n\n"
                . "Lamento que estés experimentando un error en la plataforma. Sigue estos pasos prácticos para resolverlo:\n\n"
                . "1. 🔄 **Recarga Forzada**: Presiona `Ctrl + F5` en tu computador o desliza hacia abajo en el móvil para limpiar la memoria caché y cargar la versión más reciente.\n"
                . "2. 🔐 **Renovar Sesión**: Cierra sesión e inicia nuevamente si tu sesión expiró por inactividad.\n"
                . "3. 🌐 **Navegador**: Te recomendamos usar navegadores modernos como Google Chrome, Microsoft Edge, Brave o Safari.\n"
                . "4. 👨‍💼 **Atención en Vivo**: Si el error persiste o necesitas ayuda técnica inmediata, puedes **abrir un chat directo con nuestro Administrador** para que revise tu caso en tiempo real.";

            if (Auth::check()) {
                $links[] = [
                    'label' => '💬 Hablar con el Administrador en Vivo',
                    'url' => $adminId ? route('chat.show', $adminId) : route('chat.index'),
                    'primary' => true
                ];
            } else {
                $links[] = [
                    'label' => '💬 Iniciar Sesión para Chat en Vivo',
                    'url' => route('login'),
                    'primary' => true
                ];
            }
            $links[] = ['label' => '📝 Radicar PQRS Técnica', 'url' => route('pqrs.create')];
            $links[] = ['label' => 'ℹ️ Centro de Ayuda', 'url' => route('consumer.support.contact')];

            return response()->json([
                'answer' => $answer,
                'links' => $links,
                'products' => [], // NUNCA mandar a comprar productos cuando reportan un error
                'escalate' => true,
            ]);
        }

        // 7. FAQ: FusaCoins
        if (preg_match('/\b(fusacoin|fusacoins|coins|puntos|redimir|descuento puntos|acumular)\b/', $normalized)) {
            $answer = "🌸 **Programa de FusaCoins de FusaShop:**\n\n"
                . "• **Cómo ganar**: Acumulas **1 FusaCoin por cada $10.000 COP** de compra en pedidos confirmados.\n"
                . "• **Valor de descuento**: Cada 1 FusaCoin equivale a **$100 COP de descuento**.\n"
                . "• **Tope por compra**: Puedes redimir hasta **$50.000 COP de descuento** (500 FusaCoins) en cada pedido sobre productos participantes marcados con la insignia dorada de FusaCoins.\n"
                . "• **Uso fácil**: Al momento de pagar o en tu carrito, activa la opción *'Redimir FusaCoins'* y se descontará antes de pagar.";
            $links[] = ['label' => '🛒 Explorar productos con FusaCoins', 'url' => route('consumer.catalog')];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 8. FAQ: Cancelaciones, Devoluciones y Garantías
        if (preg_match('/\b(cancelar|cancelacion|anular|devolucion|devolver|reembolso|garantia)\b/', $normalized)) {
            $answer = "🔄 **Políticas de Cancelación y Garantías:**\n\n"
                . "• **Antes del despacho**: Puedes solicitar la cancelación del pedido desde tu historial mientras el comerciante no haya generado la guía de transporte.\n"
                . "• **Pedidos sin pago**: Se cancelan automáticamente al vencer el tiempo de reserva y el stock se libera.\n"
                . "• **Garantías y devoluciones**: Si el producto recibido presenta averías o no corresponde con la descripción, cuentas con la garantía y mediación de FusaShop mediante PQRS oficial.";
            $links[] = ['label' => '📦 Mis Pedidos', 'url' => Auth::check() ? route('consumer.orders') : route('login')];
            $links[] = ['label' => '📝 Radicar PQRS', 'url' => route('pqrs.create')];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 9. FAQ: Envíos con Envia.com
        if (preg_match('/\b(envio|envios|entrega|entregas|despacho|flete|tiempo|tiempos|cuanto tarda|cobertura|llegada|transportadora)\b/', $normalized)) {
            $answer = "🚚 **Envíos y Cobertura con Envia.com:**\n\n"
                . "En FusaShop todos los envíos son gestionados por **Envia.com** con cobertura urbana en Fusagasugá, zonas turísticas (Chinauta, Silvania) y despacho a nivel nacional.\n\n"
                . "• **Tarifa**: Se calcula de forma transparente según el peso volumétrico y tu dirección en el checkout.\n"
                . "• **Tiempos**: Entregas locales en 24 a 48 horas y nacionales de 2 a 4 días hábiles una vez despachado.";
            $links[] = ['label' => '🛍️ Ir a la Tienda', 'url' => route('consumer.catalog')];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 10. FAQ: Métodos de Pago con Wompi
        if (preg_match('/\b(pago|pagos|pagar|metodo|metodos|nequi|daviplata|pse|tarjeta|tarjetas|wompi|credito|debito|bancolombia)\b/', $normalized)) {
            $answer = "💳 **Métodos de Pago Seguros con Wompi:**\n\n"
                . "Tu dinero está 100% protegido. Aceptamos:\n"
                . "• Transferencias PSE desde cualquier banco del país.\n"
                . "• Nequi y Daviplata en línea.\n"
                . "• Tarjetas de crédito y débito Visa, Mastercard y American Express.\n"
                . "• Botón Bancolombia.\n\n"
                . "Al pagar, tu pedido se confirma al instante y recibes la factura electrónica legal en tu correo.";
            $links[] = ['label' => '🛍️ Explorar Catálogo', 'url' => route('consumer.catalog')];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 11. FAQ: Registro como Vendedor
        if (preg_match('/\b(vender|vendedor|vendedora|registrar|registrarme|mi negocio|publicar)\b/', $normalized)) {
            $answer = "🏪 **¿Quieres vender tus productos en FusaShop?**\n\n"
                . "Únete a la red comercial del Sumapaz. Regístrate seleccionando el perfil de comerciante, sube tu RUT y Cámara de Comercio, y comienza a publicar tu catálogo para llegar a miles de compradores.";
            $links[] = ['label' => '📝 Registrarme como comerciante', 'url' => route('register')];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 12. FAQ: Perfil y Gestión de Cuenta
        if (preg_match('/\b(perfil|cuenta|nombre|correo|email|telefono|direccion|avatar|foto)\b/', $normalized)) {
            $answer = "👤 **Gestión de Mi Perfil:**\n\n"
                . "Puedes cambiar tu nombre, correo, teléfono, direcciones de envío guardadas y foto desde Mi Perfil. Edita los campos y guarda los cambios al final del formulario.";
            $links[] = [
                'label' => 'Abrir Mi Perfil',
                'url' => Auth::check() ? route('account.profile') : route('login'),
            ];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // 13. FAQ: Cómo comprar / Pedidos / Carrito
        if (preg_match('/\b(comprar|compra|como comprar|como realizo una compra|pedido|pedidos|carrito|checkout)\b/', $normalized)) {
            $answer = "🛍️ **Cómo comprar en FusaShop:**\n\n"
                . "Para comprar, abre el catálogo, elige un producto y agrégalo al carrito. Después confirma tu dirección de entrega y el método de pago para completar el pedido con total seguridad.";
            $links[] = ['label' => 'Ir al catálogo', 'url' => route('consumer.catalog')];
            return response()->json(['answer' => $answer, 'links' => $links, 'products' => [], 'escalate' => false]);
        }

        // =========================================================================
        // B. BÚSQUEDA Y RECOMENDACIÓN DE PRODUCTOS EN CATÁLOGO
        // =========================================================================
        $words = preg_split('/[^a-z0-9]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        $stopWords = [
            'como', 'que', 'quiero', 'quisiera', 'puedo', 'puedes', 'podria', 'podrias',
            'tienes', 'tiene', 'hay', 'ver', 'mostrar', 'muestrame', 'busco', 'buscar',
            'necesito', 'una', 'uno', 'unas', 'unos', 'el', 'la', 'los', 'las', 'de',
            'del', 'para', 'por', 'con', 'en', 'me', 'mi', 'un', 'al', 'alguna',
            'algun', 'favor', 'venden', 'vender', 'comprar', 'compra',
            'error', 'errores', 'problema', 'problemas', 'fallo', 'fallos', 'falla', 'fallas',
            'bug', 'bugs', 'presento', 'tengo', 'sucede', 'pasa', 'salio', 'sale',
            'pantalla', 'sistema', 'pagina', 'soporte', 'ayuda'
        ];
        $terms = array_values(array_unique(array_filter(
            $words,
            fn (string $word) => mb_strlen($word) >= 3 && !in_array($word, $stopWords, true)
        )));

        $singularTerms = [
            'prendas' => 'prenda',
            'camisas' => 'camisa',
            'camisetas' => 'camiseta',
            'pantalones' => 'pantalon',
            'vestidos' => 'vestido',
            'hoteles' => 'hotel',
            'glampings' => 'glamping',
            'servicios' => 'servicio',
            'restaurantes' => 'restaurante',
            'productos' => 'producto',
            'articulos' => 'articulo',
        ];
        $terms = array_values(array_unique(array_map(
            fn (string $word) => $singularTerms[$word] ?? $word,
            $terms
        )));

        $topics = [
            'ropa' => [
                'keywords' => ['ropa', 'prenda', 'camisa', 'camiseta', 'pantalon', 'vestido', 'falda', 'chaqueta', 'zapato', 'calzado', 'moda'],
                'categories' => ['ropa', 'moda', 'vestuario', 'calzado'],
            ],
            'turismo' => [
                'keywords' => ['hotel', 'hostal', 'glamping', 'hospedaje', 'alojamiento', 'habitacion', 'cabana', 'turismo'],
                'categories' => ['turismo', 'hotel', 'glamping', 'hospedaje', 'alojamiento'],
            ],
            'servicios' => [
                'keywords' => ['servicio', 'servicios', 'restaurante', 'comida', 'peluqueria', 'reparacion', 'experiencia'],
                'categories' => ['servicio', 'restaurante', 'gastronomia', 'experiencia'],
            ],
        ];

        $categoryHints = [];
        foreach ($topics as $topic) {
            if (array_intersect($terms, $topic['keywords'])) {
                $categoryHints = array_merge($categoryHints, $topic['categories']);
            }
        }

        $productKeywords = array_merge(...array_map(fn ($topic) => $topic['keywords'], array_values($topics)));
        $isProductRequest = (bool) array_intersect($terms, $productKeywords)
            || (bool) array_intersect($terms, ['producto', 'productos', 'articulo', 'articulos']);

        $products = collect();
        if (!empty($terms)) {
            $products = \App\Models\Product::active()
                ->where(function ($builder) use ($terms, $categoryHints) {
                    foreach ($terms as $term) {
                        $builder->orWhere('name', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%")
                            ->orWhere('category', 'like', "%{$term}%");
                    }

                    foreach ($categoryHints as $hint) {
                        $builder->orWhere('category', 'like', "%{$hint}%");
                    }
                })
                ->orderByDesc('stock')
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'description', 'category', 'price', 'discount_percent', 'image', 'images']);
        }

        if ($products->isNotEmpty()) {
            $answer = "🌸 Encontré estas excelentes opciones en los comercios de Fusagasugá para ti:";
            $links[] = ['label' => 'Ver todo el catálogo', 'url' => route('consumer.catalog', ['search' => $rawQuery])];
        } elseif ($isProductRequest) {
            $answer = "No encontré coincidencias con ese término en el catálogo en este momento. Prueba con otro nombre o categoría.";
            $links[] = ['label' => 'Explorar catálogo completo', 'url' => route('consumer.catalog')];
        } else {
            $answer = "Puedo ayudarte a resolver dudas sobre tus compras, pagos, envíos o encontrar productos locales en Fusagasugá. ¿En qué te puedo colaborar hoy?";
            $links[] = ['label' => 'Explorar catálogo completo', 'url' => route('consumer.catalog')];
            $links[] = ['label' => 'Ver ofertas especiales', 'url' => route('consumer.home') . '#ofertas'];
        }

        return response()->json([
            'answer' => $answer,
            'links' => $links,
            'products' => $products->map(function (\App\Models\Product $product) {
                $imagePath = collect(array_merge([$product->image], $product->images ?? []))
                    ->filter(fn ($path) => is_string($path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path))
                    ->first();

                return [
                    'name' => $product->name,
                    'category' => $product->category,
                    'description' => \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 90),
                    'original_price' => $product->hasDiscount() ? number_format($product->originalPrice(), 0, ',', '.') : null,
                    'discount_percent' => $product->discount_percent ?? 0,
                    'price' => number_format($product->effectivePrice(), 0, ',', '.'),
                    'image' => $imagePath ? url('files/' . ltrim($imagePath, '/')) : null,
                    'url' => route('consumer.product', $product->id),
                ];
            })->values(),
            'escalate' => false,
        ]);
    }


    public function productDetail($id)
    {
        $product = Product::active()->with('reviews.user', 'merchant.companyProfile')->findOrFail($id);
        $related = Product::active()->with('merchant.companyProfile')->where('category', $product->category)->where('id','!=',$id)->take(4)->get();
        
        $hasPurchased = false;
        if (Auth::check()) {
            $hasPurchased = Order::where('user_id', Auth::id())
                ->where('status', 'delivered')
                ->whereHas('items', function ($q) use ($id) {
                    $q->where('product_id', $id);
                })->exists();
        }

        return view('consumer.product-detail', compact('product', 'related', 'hasPurchased'));
    }

    // --- MÉTODOS DEL CARRITO ---
    public function cartIndex()
    {
        $user = Auth::user();
        $cart = $user->cart()->with('items.product.merchant.companyProfile')->firstOrCreate(['user_id' => $user->id]);
        foreach ($cart->items as $item) {
            if ($item->product) {
                $currentPrice = $item->product->priceForOptions($item->selected_options ?? []);
                if ((float) $item->unit_price !== $currentPrice) {
                    $item->update(['unit_price' => $currentPrice]);
                }
            }
        }
        $userPoints = $user->totalPoints();
        return view('consumer.cart', compact('cart', 'userPoints'));
    }

    public function cartAdd(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id', 
            'quantity'   => 'integer|min:1',
            'options'    => 'nullable|array'
        ]);

        $product = Product::active()->with('merchant.companyProfile')->findOrFail($request->integer('product_id'));
        if ($product->isReservationOnly()) {
            $message = 'Este comercio gestiona este artículo como reserva. Abre su ficha y envía una solicitud de reserva.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('consumer.product', $product->id)->with('error', $message);
        }
        
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        
        $selectedOptions = $request->input('options', []);
        if (!$product->validOptionSelection($selectedOptions)) {
            return back()->withErrors(['options' => 'Selecciona una opción válida para cada especificación del producto.'])->withInput();
        }

        $variant = $product->variantForOptions($selectedOptions);
        if ($product->variants && !$variant) {
            return back()->withErrors(['options' => 'La combinación seleccionada ya no está disponible.'])->withInput();
        }
        $unitPrice = $product->priceForOptions($selectedOptions);
        
        // Intentamos encontrar un item existente con las mismas opciones
        // Para simplificar, buscamos por JSON exacto si es posible, o simplemente creamos uno nuevo
        // En MySQL/Laravel, comparar JSON exacto:
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->where('selected_options', json_encode($selectedOptions))
            ->first();

        if ($item) {
            $newQuantity = $item->quantity + (int) $request->input('quantity', 1);
            if ($variant && $newQuantity > max(0, (int) $variant['stock'] - (int) ($variant['reserved_stock'] ?? 0))) {
                return back()->withErrors(['quantity' => 'No hay suficientes unidades disponibles de esta combinación.'])->withInput();
            }
            $item->update(['quantity' => $newQuantity, 'unit_price' => $unitPrice]);
        } else {
            if ($variant && (int) $request->input('quantity', 1) > max(0, (int) $variant['stock'] - (int) ($variant['reserved_stock'] ?? 0))) {
                return back()->withErrors(['quantity' => 'No hay suficientes unidades disponibles de esta combinación.'])->withInput();
            }
            $item = CartItem::create([
                'cart_id'          => $cart->id,
                'product_id'       => $request->product_id,
                'quantity'         => $request->input('quantity', 1),
                'selected_options' => $selectedOptions,
                'unit_price' => $unitPrice,
            ]);
        }

        if ($request->boolean('buy_now')) {
            session(['checkout_selected_items' => [$item->id]]);
            return redirect()->route('consumer.checkout');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '¡Producto agregado al carrito con éxito!',
                'cart_count' => $cart->items()->sum('quantity')
            ]);
        }

        return back()->with('success', '¡Producto agregado al carrito con éxito!');
    }

    public function cartUpdate(Request $request, CartItem $item)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        if ($item->cart->user_id !== Auth::id()) abort(403);
        $variant = $item->product?->variantForOptions($item->selected_options ?? []);
        if ($variant && $request->integer('quantity') > max(0, (int) $variant['stock'] - (int) ($variant['reserved_stock'] ?? 0))) {
            return back()->withErrors(['quantity' => 'La cantidad supera el stock disponible para esta combinación.']);
        }
        $item->update(['quantity' => $request->quantity]);
        return back()->with('success', 'Carrito actualizado.');
    }

    public function cartRemove(CartItem $item)
    {
        if ($item->cart->user_id !== Auth::id()) abort(403);
        $item->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    public function cartSelect(Request $request)
    {
        $selected = $request->input('selected_items', []);
        session(['checkout_selected_items' => is_array($selected) ? array_map('intval', $selected) : []]);
        return response()->json(['success' => true]);
    }

    // El checkout (Wompi + Envia) vive en CheckoutController.

    public function orders()
    {
        $orders = Order::where('user_id', Auth::id())->with('items.product')->latest()->paginate(10);
        return view('consumer.orders', compact('orders'));
    }

    public function cancelOrder(Request $request, Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403, 'No tienes permiso para cancelar este pedido.');

        if (!$order->canBeCancelledByBuyer()) {
            return back()->with('error', 'Este pedido ya no puede cancelarse directamente porque ya fue despachado, tiene guía asignada o ya se encuentra finalizado.');
        }

        $reason = trim(strip_tags((string) $request->input('reason', 'Cancelado por el comprador')));
        if ($reason === '') {
            $reason = 'Cancelado por el comprador';
        }

        $order->load('items.product.merchant', 'user');

        \Illuminate\Support\Facades\DB::transaction(function () use ($order, $reason) {
            // 1. Liberar o restituir stock de los productos
            if ($order->stock_reserved) {
                app(\App\Services\StockService::class)->release($order);
            } elseif ($order->payment_status === 'paid') {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }

            // 2. Reintegrar FusaCoins si el comprador había redimido puntos en este pedido
            if ($order->points_used > 0) {
                $firstMerchantId = $order->items->pluck('product.merchant_id')->filter()->first();
                if ($firstMerchantId) {
                    $loyalty = \App\Models\LoyaltyPoint::firstOrCreate(
                        ['user_id' => $order->user_id, 'merchant_id' => $firstMerchantId],
                        ['points' => 0]
                    );
                    $loyalty->increment('points', (int) $order->points_used);
                }
            }

            // 3. Marcar la orden como cancelada con la nota del comprador
            $order->update([
                'status' => 'cancelled',
                'notes'  => $reason,
            ]);
        });

        // 4. Notificar a todos los comerciantes cuyos productos estaban en el pedido
        $merchants = $order->items
            ->map(fn ($item) => $item->product?->merchant)
            ->filter()
            ->unique('id');

        foreach ($merchants as $merchant) {
            try {
                $merchant->notify(new \App\Notifications\OrderCancelledByBuyerNotification($order, $reason));
            } catch (\Throwable $e) {
                // La notificación en base de datos se guarda aunque el transporte de correo no esté activo
            }
        }

        return back()->with('success', "El Pedido #{$order->id} ha sido cancelado exitosamente y el comercio fue notificado.");
    }

    public function receipt($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['items.product.merchant.companyProfile'])
            ->findOrFail($id);
            
        $userPoints = \App\Models\LoyaltyPoint::where('user_id', Auth::id())->sum('points');
            
        return view('consumer.receipt', compact('order', 'userPoints'));
    }

    // --- PERFIL Y DASHBOARD DE USUARIO ---
    public function profile(Request $request)
    {
        $user = Auth::user();
        $initialTab = $request->query('tab', 'overview');
        $userPoints = $user->totalPoints();
        $loyaltyPoints = \App\Models\LoyaltyPoint::where('user_id', $user->id)
            ->with('merchant.companyProfile')
            ->where('points', '>', 0)
            ->get();

        $orders = Order::where('user_id', $user->id)
            ->with(['items.product.merchant.companyProfile'])
            ->latest()
            ->take(15)
            ->get();

        // Envíos activos o con número de guía de Envia.com
        $activeShipments = Order::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereIn('status', ['processing', 'shipped'])
                  ->orWhereNotNull('tracking_number');
            })
            ->where('status', '!=', 'delivered')
            ->with(['items.product'])
            ->latest()
            ->take(5)
            ->get();

        // Facturas electrónicas emitidas por Factus (con CUFE / número)
        $invoices = Order::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNotNull('invoice_number')
                  ->orWhereNotNull('invoice_cufe')
                  ->orWhereNotNull('invoice_pdf_path');
            })
            ->latest()
            ->take(10)
            ->get();

        // Reservas O2O: órdenes cuyos ítems pertenezcan a hotelería, gastronomía, turismo o experiencias
        $o2oOrders = Order::where('user_id', $user->id)
            ->whereHas('items.product', function ($q) {
                $q->where('type', 'service')
                  ->orWhere('category', 'LIKE', '%Turismo%')
                  ->orWhere('category', 'LIKE', '%Gastronom%')
                  ->orWhere('category', 'LIKE', '%Restaurante%')
                  ->orWhere('category', 'LIKE', '%Hotel%')
                  ->orWhere('category', 'LIKE', '%Glamping%')
                  ->orWhere('category', 'LIKE', '%O2O%');
            })
            ->with(['items.product.merchant.companyProfile'])
            ->latest()
            ->take(6)
            ->get();

        $addresses = $user->shippingAddresses()->latest()->get();

        $coupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($q) { 
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now()); 
            })
            ->with('merchant.companyProfile')
            ->take(6)
            ->get();

        return view('consumer.profile', compact(
            'user', 
            'initialTab',
            'userPoints', 
            'loyaltyPoints', 
            'orders', 
            'activeShipments', 
            'invoices', 
            'o2oOrders', 
            'addresses', 
            'coupons'
        ));
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('consumer.profile-edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,'.$user->id,
            'phone'            => 'required|string|max:50',
            'address'          => 'nullable|string|max:255',
            'avatar'           => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'remove_avatar'    => 'nullable|boolean',
            'wompi_public_key' => 'nullable|string|max:120',
            'wompi_integrity_secret' => 'nullable|string|max:120',
            'wompi_events_secret' => 'nullable|string|max:120',
            'clear_wompi_credentials' => 'nullable|boolean',
            'current_password' => 'nullable|required_with:password|current_password',
            'password'         => ['nullable', 'confirmed', PasswordPolicy::rule()],
        ], [
            ...PasswordPolicy::messages(),
            'name.required'                     => 'El nombre completo es obligatorio.',
            'email.required'                    => 'El correo electrónico es obligatorio.',
            'email.unique'                      => 'Este correo electrónico ya está registrado por otra cuenta.',
            'phone.required'                    => 'El número de teléfono móvil o WhatsApp es obligatorio.',
            'avatar.image'                      => 'El archivo de imagen no es válido.',
            'avatar.max'                        => 'La imagen de perfil no debe superar los 4 MB.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.min'                      => 'La contraseña debe tener al menos 12 caracteres.',
            'password.confirmed'                => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $updateData = [
            'name'    => strip_tags($data['name']),
            'email'   => strip_tags($data['email']),
            'phone'   => strip_tags($data['phone']),
            'address' => strip_tags($data['address'] ?? ''),
        ];

        // Opción de eliminar avatar
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $updateData['avatar'] = null;
        }

        // Subida de nueva imagen de avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $path;
        }

        // Cambio de contraseña opcional
        if (!empty($data['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        $payoutData = [];
        if ($user->isMerchant()) {
            $payoutData = CompanyProfile::validateAndExtractPayoutData($request, false);
        }

        $user->update($updateData);

        if ($user->isMerchant()) {
            $cp = $user->companyProfile ?? new CompanyProfile([
                'merchant_id'  => $user->id,
                'company_name' => $user->name,
                'phone'        => $updateData['phone'],
                'rut_path'     => '',
                'kyc_status'   => 'pending',
            ]);
            $cp->phone = $updateData['phone'];
            $cp->address = $updateData['address'] ?: $cp->address;
            if (!empty($payoutData)) {
                $cp->payout_method_type    = $payoutData['payout_method_type'];
                $cp->payout_bank_name      = $payoutData['payout_bank_name'];
                $cp->payout_account_type   = $payoutData['payout_account_type'];
                $cp->payout_account_number = $payoutData['payout_account_number'];
                $cp->payout_account_holder = $payoutData['payout_account_holder'] ?? $user->name;
                $cp->payout_id_number      = $payoutData['payout_id_number'];
            }
            if ($request->boolean('clear_wompi_credentials')) {
                $cp->wompi_public_key = null;
                $cp->wompi_integrity_secret = null;
                $cp->wompi_events_secret = null;
            } else {
                foreach (['wompi_public_key', 'wompi_integrity_secret', 'wompi_events_secret'] as $credential) {
                    if ($request->filled($credential)) {
                        $cp->{$credential} = strip_tags(trim((string) $request->input($credential)));
                    }
                }
            }
            $cp->save();
        }

        // Si es comerciante y tiene perfil comercial, sincronizar también el teléfono de contacto
        if ($user->isMerchant() && $user->companyProfile) {
            $user->companyProfile->update([
                'phone'   => $updateData['phone'],
                'address' => $updateData['address'] ?: $user->companyProfile->address,
            ]);
        }

        return redirect()->route('account.profile.edit')->with('success', '¡Tu perfil y datos personales se actualizaron correctamente!');
    }

    // --- DIRECTORIO Y PERFIL DE MARCA ---
    public function merchantDirectory(Request $request)
    {
        $search = $request->query('search');
        $query = CompanyProfile::where('kyc_status', 'approved')
            ->select('id', 'merchant_id', 'company_name', 'logo_path', 'description', 'address', 'phone', 'business_type');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        $merchants = $query->latest()->paginate(12)->withQueryString();
        return view('consumer.directory', compact('merchants', 'search'));
    }

    /**
     * MÉTODO CORREGIDO: Busca por merchant_id para evitar el error 404
     */
    public function merchantProfile($id)
    {
        $profile = CompanyProfile::with('user')->where('merchant_id', $id)->firstOrFail();

        $products = Product::active()
            ->where('merchant_id', $profile->merchant_id)
            ->paginate(12);

        return view('consumer.merchant-profile', compact('profile', 'products'));
    }

    // --- CALIFICACIONES (REVIEWS) ---
    public function productReview(Request $request, $id)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $product = Product::active()->findOrFail($id);
        
        $hasPurchased = Order::where('user_id', Auth::id())
            ->where('status', 'delivered')
            ->whereHas('items', function ($q) use ($id) {
                $q->where('product_id', $id);
            })->exists();

        if (!$hasPurchased) {
            abort(403, 'Debes comprar y recibir este producto para poder calificarlo.');
        }

        $review = Review::create([
            'product_id' => $product->id,
            'user_id'    => Auth::id(),
            'rating'     => $request->rating,
            'comment'    => strip_tags($request->comment ?? '')
        ]);

        $merchant = User::find($product->merchant_id);
        if ($merchant) {
            $merchant->notify(new NewProductReview($review->load('product', 'user')));
        }

        return back()->with('success', 'Gracias por tu valoración.');
    }

    public function contactSupport()
    {
        $admin = \App\Models\User::where('role', 'analyst')->first();
        if (!$admin) {
            return back()->with('error', 'No hay administradores disponibles en este momento.');
        }
        return redirect()->route('chat.show', $admin->id);
    }
}
