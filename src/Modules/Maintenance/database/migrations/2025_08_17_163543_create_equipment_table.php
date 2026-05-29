<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();

            $table->date('last_maintained_at')->nullable();
            $table->date('next_maintenance_at')->nullable();
            $table->date('last_failure_at')->nullable();

            $table->integer('mtbf')->nullable()->comment('Mean Time Between Failures in hours');
            $table->integer('mttr')->nullable()->comment('Mean Time To Repair in hours');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
