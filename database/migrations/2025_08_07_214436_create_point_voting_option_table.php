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
        // 'council_points' y 'voting_options'. Define qué opciones de voto
        // están disponibles para un punto específico.
        Schema::create('council_point_voting_option', function (Blueprint $table) {
            $table->foreignId('council_point_id')
                  ->constrained('council_points')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('voting_option_id')
                  ->constrained('voting_options')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['council_point_id', 'voting_option_id']);
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
