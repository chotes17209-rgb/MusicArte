<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@musicarte.pe'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('MusicArte2026'),
                'role' => 'admin',
                'activo' => true,
            ]
        );

        User::updateOrCreate(
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
