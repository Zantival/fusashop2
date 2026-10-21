<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompressResponse
{
    /**
     * Comprime las respuestas dinámicas (HTML, JSON, texto) usando Gzip si el cliente lo soporta.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // No comprimir si no soporta gzip, si la respuesta es un archivo o stream, o si ya está comprimida
        if (!$request->headers->has('Accept-Encoding') || 
            !str_contains((string) $request->header('Accept-Encoding'), 'gzip') ||
            $response instanceof BinaryFileResponse ||
            $response instanceof StreamedResponse ||
            $response->headers->has('Content-Encoding') ||
            !function_exists('gzencode')) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/plain',
            'text/css',
            'text/javascript',
            'application/json',
            'application/javascript',
            'application/xml',
            'image/svg+xml'
        ];

        $isCompressible = false;
        foreach ($compressibleTypes as $type) {
            if (str_contains($contentType, $type)) {
                $isCompressible = true;
                break;
            }
        }

        if (!$isCompressible) {
            return $response;
        }

        $content = $response->getContent();
        if ($content === false || strlen($content) < 1024) {
            // Respuestas muy pequeñas (< 1KB) no ameritan sobrecarga de compresión
            return $response;
        }

        $compressed = gzencode($content, 6);
        if ($compressed !== false) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Vary', 'Accept-Encoding', false);
            $response->headers->set('Content-Length', (string) strlen($compressed));
        }

        return $response;
    }
}
