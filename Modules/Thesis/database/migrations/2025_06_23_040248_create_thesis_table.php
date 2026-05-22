<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thesis', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('is_active')->default(true);
            $table->string('title')->unique();
            $table->date('date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thesis');
    }
};