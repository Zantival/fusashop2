<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Auto-limpieza de cachés obsoletos de rutas y vistas tras despliegues en hosting compartido (Hostinger).
try {
    $basePath = dirname(__DIR__);
    $webRoutesFile = $basePath . '/routes/web.php';
    if (is_file($webRoutesFile)) {
        $routesMtime = @filemtime($webRoutesFile) ?: 0;
        foreach (glob($basePath . '/bootstrap/cache/routes*.php') ?: [] as $cachedRouteFile) {
            if ((@filemtime($cachedRouteFile) ?: 0) < $routesMtime) {
                @unlink($cachedRouteFile);
            }
        }

        $deployVersion = md5($routesMtime . '|' . (@filemtime(__FILE__) ?: 0));
        $markerFile = $basePath . '/storage/framework/deploy_cache_version.txt';
        $previousVersion = is_file($markerFile) ? trim((string) @file_get_contents($markerFile)) : '';
        if ($previousVersion !== $deployVersion) {
            foreach (glob($basePath . '/bootstrap/cache/routes*.php') ?: [] as $cachedRouteFile) {
                @unlink($cachedRouteFile);
            }
            foreach (glob($basePath . '/storage/framework/views/*.php') ?: [] as $compiledView) {
                @unlink($compiledView);
            }
            @file_put_contents($markerFile, $deployVersion);
        }
    }
} catch (\Throwable) {
    // Continuar normalmente si el sistema de archivos tiene restricciones.
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\CompressResponse::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'El archivo supera el tamaño permitido.'], 413);
            }

            return response()->view('errors.413', [], 413);
        });
    })->create();
