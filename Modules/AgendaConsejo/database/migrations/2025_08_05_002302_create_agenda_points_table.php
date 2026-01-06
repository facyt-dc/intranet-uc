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
        Schema::create('agenda_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')
                  ->constrained('agendas')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->text('description');
            $table->foreignId('requested_by_user_id')
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->string('status')->default('Abierto para Votación');
            $table->unsignedInteger('min_votes_to_close')->default(1);
            $table->text('conclusion')->nullable();

            // order: Campo numérico para permitir la ordenación de los puntos dentro de la agenda.
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_points');
    }
};
