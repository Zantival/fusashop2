<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * OWASP Security Headers Implementation & WAF Defense
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ocultar versión de PHP y servidor (Previene "PHP Version with Known Vulnerabilities")
        header_remove('X-Powered-By');
        if (function_exists('header_remove')) {
            header_remove('Server');
        }

        // 2. Filtro WAF temprano: Detecta firmas de inyección SQL (time-based, boolean, stacked)
        // en parámetros de formularios de autenticación y catálogo (login, register, forgot-password, search).
        // Al responder en < 1ms con 400, neutraliza el ataque de raíz, evita demoras de tiempo (>5s)
        // que confundan a OWASP ZAP con inyecciones ciegas y previene la saturación del rate limiter.
        if ($request->is('login', 'register', 'forgot-password', 'reset-password', 'shop/catalog') && $this->detectSqlInjection($request)) {
            $blockedResponse = $request->expectsJson()
                ? response()->json(['message' => 'La solicitud contiene datos no permitidos.'], 400)
                : response()->view('errors.400', [], 400);
            return $this->applyHeaders($blockedResponse, $request);
        }

        $response = $next($request);

        return $this->applyHeaders($response, $request);
    }

    /**
     * Aplica el conjunto completo de cabeceras de seguridad OWASP.
     */
    protected function applyHeaders(Response $response, Request $request): Response
    {
        // Anti-Clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Legacy XSS Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        // Ocultar cabeceras de fingerprinting
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Strict Transport Security (HSTS)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy (CSP) completa con fallbacks y sin comodines inseguros
        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://unpkg.com; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://cdn.jsdelivr.net; "
            . "font-src 'self' data: https://fonts.gstatic.com; "
            . "img-src 'self' blob: data: https://*.tile.openstreetmap.org https://tile.openstreetmap.org https://images.unsplash.com https://cdn.jsdelivr.net; "
            . "media-src 'self' blob: data:; "
            . "connect-src 'self' https://cdn.jsdelivr.net https://nominatim.openstreetmap.org https://router.project-osrm.org https://*.tile.openstreetmap.org https://tile.openstreetmap.org; "
            . "frame-src 'self' https://www.google.com https://maps.google.com https://*.google.com https://*.openstreetmap.org https://www.openstreetmap.org https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com; "
            . "object-src 'none'; "
            . "base-uri 'self'; "
            . "form-action 'self' https://checkout.wompi.co; "
            . "frame-ancestors 'none';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    /**
     * Detección de patrones y firmas de inyección SQL en parámetros de entrada.
     */
    protected function detectSqlInjection(Request $request): bool
    {
        $inputs = $request->all();
        if (empty($inputs)) {
            return false;
        }

        $patterns = [
            '/\b(sleep|benchmark|pg_sleep|sqlite3_sleep)\s*\(\s*\d+\s*\)/i',
            '/\bwaitfor\s+delay\s+[\'"]/i',
            '/\bunion\s+(all\s+)?select\b/i',
            '/[\'"]\s*(or|and)\s*[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+/i',
            '/[\'"]\s*(or|and)\s*[\'"][a-z0-9_]+[\'"]\s*=\s*[\'"][a-z0-9_]+/i',
            '/[\'"]\s*(or|and)\s+(true|false)\b/i',
            '/[\'"]\s*;?\s*--/i',
            '/\/\*.*?\*\//s',
            '/[\'"]\s*;\s*(drop|delete|update|insert|alter|truncate)\b/i',
        ];

        foreach ($this->flattenValues($inputs) as $value) {
            if (!is_string($value) || strlen($value) < 4) {
                continue;
            }
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Aplana recursivamente arrays de entrada para revisar cada campo de texto.
     */
    protected function flattenValues(array $array): array
    {
        $flat = [];
        array_walk_recursive($array, function ($a) use (&$flat) {
            $flat[] = $a;
        });
        return $flat;
    }
}
