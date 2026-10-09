<?php

use App\Models\AlumnoTaller;
use App\Services\UnificarTalleresService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * - Junta tambien los talleres repetidos que estaban dados de baja (antes
 *   solo se unian los activos) con el taller que queda.
 * - Un taller dado de baja con horarios aun activos (de antes de que la baja
 *   los apagara) deja de generar y mostrar clases.
 * Solo en los periodos recientes; los meses pasados no se tocan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Solo el periodo actual y el anterior: los meses pasados son historial
        // (sus talleres quedaron "inactivos" al importar el Excel y no se tocan).
        UnificarTalleresService::unificarTodo(soloRecientes: true);

        AlumnoTaller::where('estado', 'inactivo')
            ->whereHas('periodo', fn ($q) => $q->whereDate('fecha_fin', '>=', now()->subDays(45)->toDateString()))
            ->whereHas('horarios', fn ($q) => $q->where('activo', true))
            ->get()
            ->each(fn (AlumnoTaller $t) => DB::transaction(fn () => $t->darDeBaja()));
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
