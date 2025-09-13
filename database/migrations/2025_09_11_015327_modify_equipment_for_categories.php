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
        Schema::table('equipment', function (Blueprint $table) {
            // 1. Eliminar la columna de texto anterior
            $table->dropColumn('category');
            
            // 2. Añadir la nueva clave foránea (que puede ser nula)
            $table->foreignId('equipment_category_id')->nullable()->constrained('equipment_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropForeign(['equipment_category_id']);
            $table->dropColumn('equipment_category_id');
            $table->string('category')->nullable();
        });
    }
};
