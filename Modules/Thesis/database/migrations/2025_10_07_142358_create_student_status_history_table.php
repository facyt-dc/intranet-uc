<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_student_id')->constrained('thesis_student')->onDelete('cascade');
            $table->foreignId('student_status_id')->constrained('student_statuses')->onDelete('cascade');
            $table->date('start_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_status_history');
    }
};