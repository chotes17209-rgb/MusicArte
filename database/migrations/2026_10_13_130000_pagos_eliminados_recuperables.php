<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un pago eliminado por error se puede recuperar (con sus abonos): al
 * eliminarlo solo se marca como eliminado.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pagos', 'deleted_at')) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
