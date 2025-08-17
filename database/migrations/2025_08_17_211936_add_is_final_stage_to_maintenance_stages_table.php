<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_stages', function (Blueprint $table) {
            // Añadimos el campo después de 'sequence'.
            // Por defecto será 'false' y no puede ser nulo.
            $table->boolean('is_final_stage')->default(false)->after('sequence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_stages', function (Blueprint $table) {
            //
        });
    }
};
