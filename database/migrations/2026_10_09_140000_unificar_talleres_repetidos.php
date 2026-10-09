<?php

use App\Services\UnificarTalleresService;
use Illuminate\Database\Migrations\Migration;

/**
 * Junta los talleres que quedaron repetidos (mismo alumno, taller, maestro
 * y periodo), por ejemplo al presionar "Guardar taller" varias veces. Ver
 * UnificarTalleresService: no se pierden asistencias ni abonos.
 */
return new class extends Migration
{
    public function up(): void
    {
        UnificarTalleresService::unificarTodo();
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
