<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Solo lectura: busca rastros de una alumna que pudo eliminarse por error
 * (Danna Santos Maldonado) y deja el resultado en el registro del servidor.
 * No cambia datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $buscar = fn ($q, $col) => $q->where($col, $like, '%danna%')->orWhere($col, $like, '%dana %')
            ->orWhere($col, $like, '%santos%')->orWhere($col, $like, '%maldonado%');

        Log::info('RASTRO alumnos: '.json_encode(DB::table('alumnos')->where(fn ($q) => $buscar($q, 'nombre'))
            ->orWhere(fn ($q) => $buscar($q, 'tutor'))->get(['id', 'nombre', 'tutor', 'activo', 'created_at'])));

        // Huecos en la numeracion de alumnos = alumnos eliminados (con la fecha de sus vecinos).
        $ids = DB::table('alumnos')->orderBy('id')->pluck('created_at', 'id');
        $huecos = [];
        $anterior = null;
        foreach ($ids as $id => $creado) {
            if ($anterior !== null && $id > $anterior + 1) {
                $huecos[] = ['faltan' => ($anterior + 1).'-'.($id - 1), 'antes' => substr((string) $ids[$anterior], 0, 16), 'despues' => substr((string) $creado, 0, 16)];
            }
            $anterior = $id;
        }
        Log::info('RASTRO ids eliminados: max '.$ids->keys()->max().' '.json_encode(array_slice($huecos, -15)));

        Log::info('RASTRO planillas sin alumno: '.json_encode(DB::table('planillas')->whereNull('alumno_id')->get(['id', 'maestro_id', 'mes', 'anio', 'observacion', 'updated_at'])));
        foreach (['egresos' => 'detalle', 'caja_chica' => 'detalle', 'avisos' => 'mensaje'] as $tabla => $col) {
            try {
                Log::info("RASTRO {$tabla}: ".json_encode(DB::table($tabla)->where(fn ($q) => $buscar($q, $col))->limit(20)->get()));
            } catch (\Throwable $e) {
                Log::info("RASTRO {$tabla}: sin columna {$col}");
            }
        }
    }

    public function down(): void
    {
    }
};
