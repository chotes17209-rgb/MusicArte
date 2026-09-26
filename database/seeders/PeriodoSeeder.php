<?php

namespace Database\Seeders;

use App\Models\Periodo;
use Illuminate\Database\Seeder;

class PeriodoSeeder extends Seeder
{
    /**
     * Crea los 9 periodos de 2026 (Enero a Setiembre), que es lo que ya
     * esta cargado en ADMINISTRACION_2026.xlsx. Se busca por mes/anio para
     * que correr el seeder de nuevo (pasa en cada deploy, ver Dockerfile:
     * "php artisan migrate --seed") no duplique periodos ni pise fechas o
     * el "activo" que ya se hayan ajustado a mano desde el CRUD de
     * Periodos (pestana Alumnos > Periodos).
     *
     * Setiembre nace como el UNICO periodo activo (es el que esta en
     * curso). Cuando arranque Octubre, ese periodo se crea desde el CRUD
     * y ahi mismo se desactiva Setiembre; este seeder no lo hace solo
     * para no pisar esa decision manual en despliegues futuros.
     */
    private const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Setiembre',
    ];

    public function run(): void
    {
        $anio = 2026;

        foreach (self::MESES as $mes => $nombreMes) {
            $periodo = Periodo::where('mes', $mes)->where('anio', $anio)->first();

            if ($periodo) {
                // Ya existia (deploy anterior o creado a mano): solo se
                // refresca el nombre por si acaso, sin tocar fechas ni
                // "activo" que el usuario ya haya definido.
                $periodo->update(['nombre' => $nombreMes.' '.$anio]);

                continue;
            }

            [$inicio, $fin] = Periodo::sugerirRango($mes, $anio);

            Periodo::create([
                'nombre' => $nombreMes.' '.$anio,
                'mes' => $mes,
                'anio' => $anio,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'activo' => $mes === 9,
            ]);
        }
    }
}
