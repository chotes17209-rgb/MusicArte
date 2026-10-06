<?php

use App\Models\Alumno;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Alumnos que se marcaron como inactivos antes de que existiera la baja
 * automatica del periodo: quedaron inactivos pero con sus talleres del
 * periodo vigente aun activos (y por eso seguian apareciendo "Activo").
 * Se les aplica la misma baja que hace hoy el interruptor "Alumno activo".
 */
return new class extends Migration
{
    public function up(): void
    {
        $alumnos = Alumno::where('activo', false)
            ->whereHas('talleres', fn ($q) => $q->where('estado', 'activo')
                ->whereHas('periodo', fn ($p) => $p->whereDate('fecha_fin', '>=', now()->toDateString())))
            ->get();

        foreach ($alumnos as $alumno) {
            DB::transaction(fn () => $alumno->cambiarEstado(false));
        }
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
