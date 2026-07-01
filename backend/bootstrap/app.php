<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Proxies de confianza — necesario para Railway (TLS terminación en balanceador).
        // En producción confiamos en todos los proxies por defecto; en desarrollo se
        // puede sobrescribir con TRUSTED_PROXIES en .env.
        $trustedProxies = env('TRUSTED_PROXIES', env('APP_ENV') === 'production' ? '*' : '');
        if ($trustedProxies) {
            $middleware->trustProxies(
                at: $trustedProxies,
                headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                         \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                         \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                         \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                         \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
            );
        }

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\EnsureUsuarioActivo::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'usuario.activo' => \App\Http\Middleware\EnsureUsuarioActivo::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof \App\Exceptions\Curricular\AsignacionDocenteDuplicadaException
                || $e instanceof \App\Exceptions\Curricular\TemaSemanalDuplicadoException
                || $e instanceof \App\Exceptions\Curricular\PesosEvaluacionInvalidosException) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return null;
        });
    })->create();
