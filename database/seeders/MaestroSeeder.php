<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\Maestro;
use Illuminate\Database\Seeder;

class MaestroSeeder extends Seeder
{
    /**
     * Plana docente real de Musica Arte (Agosto 2026), con sus especialidades
     * y la tarifa por hora que se le paga en cada una. Las tarifas son un
     * valor de referencia tomado de la planilla de Julio (la mayoria de
     * clases de Piano se pagan a S/10/hora y Bateria a S/15/hora); ajustalas
     * libremente desde el modulo de Maestros si un caso puntual es distinto.
     */
    public function run(): void
    {
        $tarifasPorEspecialidad = [
            'Piano' => 10, 'Bateria' => 15, 'Violin' => 10, 'Guitarra' => 10,
            'Canto' => 10, 'Flauta' => 10, 'Saxofon' => 10,
        ];

        $datos = [
            ['Javier', ['Guitarra']],
            ['Jeanpier', ['Bateria', 'Piano']],
            ['Jerry', ['Piano']],
            ['Josue', ['Violin']],
            ['Kris', ['Canto', 'Piano']],
            ['Miriam', ['Flauta', 'Piano', 'Saxofon']],
            ['Noemi', ['Piano']],
            ['Rosa', ['Violin']],
        ];

        foreach ($datos as [$nombre, $especialidadesNombres]) {
            $maestro = Maestro::updateOrCreate(['nombre' => $nombre], ['activo' => true]);

            $especialidades = Especialidad::whereIn('nombre', $especialidadesNombres)->get();

            $pivotData = [];
            foreach ($especialidades as $esp) {
                $pivotData[$esp->id] = ['tarifa_hora' => $tarifasPorEspecialidad[$esp->nombre] ?? 10];
            }

            $maestro->especialidades()->sync($pivotData);
        }
    }
}
