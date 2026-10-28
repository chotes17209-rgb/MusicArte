<?php

use App\Models\AlumnoTaller;
use App\Models\Horario;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Un taller al que se le cambio el periodo conservaba el horario del periodo
 * anterior. Si el alumno ademas tiene su propio taller en ese periodo (mismo
 * taller y maestro), sus clases salian repetidas. Se desactiva ese horario
 * sobrante, lo que quita solo sus clases repetidas sin marcar; lo marcado se
 * conserva. (Caso detectado: Sonia Jimena Justo Veliz.)
 */
return new class extends Migration
{
    public function up(): void
    {
        $sobrantes = Horario::with('alumnoTaller')->where('activo', true)->whereNotNull('alumno_taller_id')
            ->whereNotNull('periodo_id')
            ->whereHas('alumnoTaller', fn ($t) => $t->whereColumn('alumno_talleres.periodo_id', '!=', 'horarios.periodo_id'))
            ->get()
            ->filter(fn (Horario $h) => AlumnoTaller::where('alumno_id', $h->alumnoTaller->alumno_id)
                ->where('periodo_id', $h->periodo_id)->where('id', '!=', $h->alumno_taller_id)
                ->where('especialidad_id', $h->alumnoTaller->especialidad_id)->where('estado', 'activo')->exists());

        foreach ($sobrantes as $h) {
            $quitadas = $h->clasesPendientes()->count();
            $h->update(['activo' => false]);
            Log::info('REPETIDAS corregido: horario '.$h->id.' del taller '.$h->alumno_taller_id
                .' ('.($h->alumno->nombre ?? $h->alumno_id).'), '.$quitadas.' clases repetidas sin marcar quitadas.');
        }
    }

    public function down(): void
    {
    }
};
