<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('council_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_id')->constrained('councils')->onDelete('cascade');
            // Campo de texto para almacenar el nombre del consejero que solicitó el punto.
            // Esta información la introduce el Director manualmente.
            $table->string('requesting_counselor');
            $table->text('description');
            // Estado del punto (ej. 'Pendiente', 'Abierto para Votación', 'Cerrado').
            $table->string('status')->default('Pendiente');
            // Cantidad mínima de votos requerida para habilitar la notificación de cierre.
            $table->unsignedInteger('min_votes_to_close')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_points');
    }
};
