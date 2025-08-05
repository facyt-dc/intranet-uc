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
        Schema::create('council_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_id')->constrained()->onDelete('cascade');
            $table->text('topic');
            $table->foreignId('requested_by_id')->constrained('users');
            $table->string('status')->default('pending'); // pending, open, closed
            $table->integer('minimum_votes')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_points');
    }
};
