<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            EspecialidadSeeder::class,
            MaestroSeeder::class,
            PeriodoSeeder::class,
            AlumnoSeeder::class,
            HorarioSeeder::class,
            PagoSeeder::class,
            EgresoSeeder::class,
            CajaChicaSeeder::class,
            AvisoSeeder::class,
        ]);
    }
}
