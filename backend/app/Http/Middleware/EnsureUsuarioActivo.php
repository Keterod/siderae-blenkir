<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUsuarioActivo
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->activo) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Su cuenta está desactivada. Contacte al administrador del sistema.',
                ], 403);
            }

            abort(403, 'Su cuenta está desactivada.');
        }

        return $next($request);
    }
}
