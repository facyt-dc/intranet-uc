<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('thesis_files', function (Blueprint $table) {
            // Se añade la columna 'type' después de 'thesis_id'
            // Será 'pteg', 'teg' o cualquier otro tipo que necesites en el futuro.
            $table->string('type')->after('thesis_id');
        });
    }

    public function down(): void {
        Schema::table('thesis_files', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};