<?php

use App\Models\Alumno;
use Illuminate\Database\Migrations\Migration;

/**
 * El "taller principal" (taller y maestro que se muestran del alumno) se
 * tomaba del taller mas antiguo, que podia ser de otro mes y con otro
 * maestro. Se recalcula con el del periodo vigente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Alumno::query()->each(fn (Alumno $a) => $a->sincronizarTallerPrincipal());
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
