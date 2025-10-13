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
        Schema::create('teacher_thesis_pivot', function (Blueprint $table) {
            $table->id();
            // Clave foránea para el docente (tutor)
            $table->foreignId('teacher_id')->constrained('thesis_teachers')->onDelete('cascade');
            // Clave foránea para el proyecto de tesis
            $table->foreignId('thesis_id')->constrained('thesis')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_thesis_pivot');
    }
};