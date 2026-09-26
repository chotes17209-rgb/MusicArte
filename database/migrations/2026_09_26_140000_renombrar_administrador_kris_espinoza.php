<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** El administrador del sistema es Kris Espinoza (antes figuraba como "Administrador"). */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@musicarte.pe')
            ->where('name', 'Administrador')
            ->update(['name' => 'Kris Espinoza', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'admin@musicarte.pe')
            ->where('name', 'Kris Espinoza')
            ->update(['name' => 'Administrador']);
    }
};
