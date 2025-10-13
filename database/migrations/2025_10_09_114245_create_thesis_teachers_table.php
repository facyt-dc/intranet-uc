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
        Schema::create('thesis_teachers', function (Blueprint $table) {
            $table->id();
            $table->string('id_uc')->unique()->nullable(); // ID universitario, único y opcional
            $table->string('name');
            $table->string('ci')->unique(); // Cédula de Identidad, única
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesis_teachers');
    }
};