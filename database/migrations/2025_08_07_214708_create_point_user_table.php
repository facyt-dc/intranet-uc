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
        // 'council_points' y 'users'. Define qué usuarios tienen permiso
        // para votar en un punto específico.
        Schema::create('council_point_votable_user', function (Blueprint $table) {
            $table->foreignId('council_point_id')
                  ->constrained('council_points')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->primary(['council_point_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_user');
    }
};
