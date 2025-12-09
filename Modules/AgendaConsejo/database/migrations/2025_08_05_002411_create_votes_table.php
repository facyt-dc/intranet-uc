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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_point_id')
                  ->constrained('agenda_points')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->foreignId('voting_option_id')
                  ->constrained('voting_options')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // timestamps: Añade las columnas 'created_at' y 'updated_at'.
            // 'created_at' servirá como la marca de tiempo oficial de cuándo se emitió el voto.
            $table->timestamps();
            $table->unique(['agenda_point_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
