<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Seccion 15 del requerimiento: al hacer clic en "Pagos" el sistema debe
 * solicitar autenticacion antes de permitir el acceso, ademas de exigir
 * sesion iniciada. Aqui se reutiliza el sistema de login/permisos ya
 * existente (no se crea un mecanismo paralelo): se le vuelve a pedir su
 * propia contrasena al usuario ya logueado, y esa confirmacion queda
 * vigente por un tiempo corto en su sesion antes de volver a pedirsela.
 */
class EnsurePagosAuth
{
    /** Minutos que dura la confirmacion antes de volver a pedirla. */
    const MINUTOS_VIGENCIA = 20;

    public function handle(Request $request, Closure $next): Response
    {
        $confirmadoEn = $request->session()->get('pagos_confirmado_en');

        $vigente = $confirmadoEn && now()->diffInMinutes($confirmadoEn) < self::MINUTOS_VIGENCIA;

        if (! $vigente) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Debes confirmar tu contrasena para continuar en Pagos.',
                    'requiere_confirmacion' => true,
                ], 423);
            }

            $request->session()->put('pagos_url_intentada', $request->fullUrl());

            return redirect()->route('pagos.confirmar');
        }

        return $next($request);
    }
}
