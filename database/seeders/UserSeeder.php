<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Usuarios iniciales. firstOrCreate: solo se crean si no existen, asi
     * cada deploy ("migrate --seed") NO vuelve a poner la contrasena ni el
     * nombre que se hayan cambiado desde el modulo de Usuarios.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@musicarte.pe'],
            [
                'name' => 'Kris Espinoza',
                'password' => Hash::make('MusicArte2026'),
                'role' => 'admin',
                'activo' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'recepcion@musicarte.pe'],
            [
                'name' => 'Recepcion',
                'password' => Hash::make('MusicArte2026'),
                'role' => 'recepcion',
                'activo' => true,
            ]
        );
    }
}
