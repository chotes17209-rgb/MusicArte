<?php

namespace Database\Seeders;

use App\Models\Aviso;
use App\Models\User;
use Illuminate\Database\Seeder;

class AvisoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        Aviso::updateOrCreate(
            ['titulo' => 'Bienvenido al sistema MusicArte'],
            [
                'mensaje' => "Este es tu panel de gestion. Desde aqui puedes administrar alumnos, horarios, el calendario de clases, asistencia, pagos, egresos y reportes.\n\nRecuerda: solo el Administrador puede modificar precios y montos.",
                'tipo' => 'info',
                'activo' => true,
                'creado_por' => $admin?->id,
            ]
        );
    }
}
