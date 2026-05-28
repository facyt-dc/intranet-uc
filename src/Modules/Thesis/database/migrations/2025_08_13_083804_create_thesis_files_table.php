<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thesis_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained('thesis')->onDelete('cascade');
            $table->string('original_name');
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thesis_files');
    }
};