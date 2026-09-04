<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    /**
     * Especialidades reales dictadas en MusicArte, con el precio mensual
     * mas frecuente segun el registro de pagos de Agosto 2026.
     */
    public function run(): void
    {
        $especialidades = [
            ['nombre' => 'Bateria', 'color' => '#e67e22', 'precio_mensual' => 230.0, 'activo' => true],
            ['nombre' => 'Canto', 'color' => '#d81b60', 'precio_mensual' => 150.0, 'activo' => true],
            ['nombre' => 'Flauta', 'color' => '#2980b9', 'precio_mensual' => 230.0, 'activo' => true],
            ['nombre' => 'Guitarra', 'color' => '#c0392b', 'precio_mensual' => 230.0, 'activo' => true],
            ['nombre' => 'Piano', 'color' => '#3d2c8d', 'precio_mensual' => 230.0, 'activo' => true],
            ['nombre' => 'Saxofon', 'color' => '#8e44ad', 'precio_mensual' => 280.0, 'activo' => true],
            ['nombre' => 'Violin', 'color' => '#16a085', 'precio_mensual' => 230.0, 'activo' => true],
        ];

        foreach ($especialidades as $esp) {
            Especialidad::updateOrCreate(['nombre' => $esp['nombre']], $esp);
        }
    }
}
