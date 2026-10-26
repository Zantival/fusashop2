<?php

/**
 * FusaShop - Servidor de Desarrollo Seguro para `php artisan serve`
 * Emula mod_rewrite y previene brechas de seguridad (Ruta Transversal, divulgación de .htaccess y cabeceras PHP).
 */

header_remove('X-Powered-By');
if (function_exists('header_remove')) {
    header_remove('Server');
}

$publicPath = __DIR__ . '/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? ''
);

// 1. Detección y bloqueo de Ruta Transversal (Path / Directory Traversal)
if (str_contains($uri, '..') || str_contains($uri, "\0") || str_contains($uri, '\\')) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo '403 Forbidden: Invalid path traversal sequence detected.';
    exit;
}

// 2. Bloqueo estricto de archivos ocultos o confidenciales (.htaccess, .env, .git, etc.)
$segments = explode('/', trim($uri, '/'));
foreach ($segments as $segment) {
    if (str_starts_with($segment, '.') || in_array(strtolower($segment), ['web.config', 'composer.json', 'composer.lock', 'package.json'])) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo '404 Not Found';
        exit;
    }
}

// 3. Servir archivos estáticos permitidos existentes en /public
$targetFile = $publicPath . $uri;
if ($uri !== '/' && file_exists($targetFile) && is_file($targetFile)) {
    return false;
}

// 4. Registro y reenvío a Laravel index.php
require_once $publicPath . '/index.php';
