<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_thesis_files_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thesis_files', function (Blueprint $table) {
            $table->id();
            
            // Clave foránea que conecta con  'thesis'
            $table->foreignId('thesis_id')->constrained('thesis')->onDelete('cascade');

            // El nombre original del archivo que subió el usuario (ej: "capitulo_1.pdf")
            $table->string('original_name');
            
            // La ruta donde Laravel guardó el archivo (ej: "thesis_files/asD8fGhaSdf.pdf")
            $table->string('path');


            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thesis_files');
    }
};
