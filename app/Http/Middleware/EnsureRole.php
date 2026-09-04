<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Permite el paso solo si el usuario autenticado tiene uno de los
     * roles indicados. Uso en rutas: ->middleware('role:admin')
     *
     * Ejemplo: 'role:admin,recepcion' permite ambos roles.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tienes permiso para realizar esta accion. Esta seccion es exclusiva del Administrador.',
                ], 403);
            }

            abort(403, 'No tienes permiso para acceder a esta seccion.');
        }

        return $next($request);
    }
}
