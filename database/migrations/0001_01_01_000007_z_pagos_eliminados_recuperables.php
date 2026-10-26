<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columna de "pago eliminado" (se puede recuperar) creada junto con la
 * tabla de pagos, para que los pasos siguientes ya la tengan. Donde ya
 * existe (agregada luego por 2026_10_13_130000) no hace nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pagos', 'deleted_at')) {
            Schema::table('pagos', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // La quita 2026_10_13_130000 (o se borra la tabla).
    }
};
