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
        Schema::create('voting_options', function (Blueprint $table) {
            $table->id();

            // name: El texto de la opción de voto (ej: "A favor", "En contra").
            // Debe ser único para evitar tener opciones duplicadas en el sistema.
            $table->string('name')->unique();

            // is_active: Un booleano para habilitar o deshabilitar una opción globalmente.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_options');
    }
};
