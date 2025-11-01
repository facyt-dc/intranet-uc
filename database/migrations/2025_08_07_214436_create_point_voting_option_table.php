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
        // Esta es una tabla pivote para la relación muchos-a-muchos entre
        // 'agenda_points' y 'voting_options'. Define qué opciones de voto
        // están disponibles para un punto específico.
        Schema::create('agenda_point_voting_option', function (Blueprint $table) {
            $table->foreignId('agenda_point_id')
                  ->constrained('agenda_points')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('voting_option_id')
                  ->constrained('voting_options')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->primary(['agenda_point_id', 'voting_option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_voting_option');
    }
};
