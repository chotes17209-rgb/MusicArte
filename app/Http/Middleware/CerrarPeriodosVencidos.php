<?php

namespace App\Http\Middleware;

use App\Models\Periodo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra solos los periodos vencidos. El servidor no tiene tareas
 * programadas, asi que se revisa con la primera visita de cada dia (la
 * cache evita repetir la consulta en cada pagina).
 */
class CerrarPeriodosVencidos
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Cache::add('periodos-vencidos-revisados:'.now()->toDateString(), true, now()->endOfDay())) {
            Periodo::cerrarVencidos();
        }

        return $next($request);
    }
}
