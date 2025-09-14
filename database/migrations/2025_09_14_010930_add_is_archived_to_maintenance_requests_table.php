<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Añadimos la columna booleana. Por defecto, nada está archivado.
            // La indexamos para que las consultas sean rápidas.
            $table->boolean('is_archived')->default(false)->after('stage_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn('is_archived');
        });
    }
};