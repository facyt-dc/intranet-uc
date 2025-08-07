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
        Schema::create('point_voting_option', function (Blueprint $table) {
            $table->foreignId('council_point_id')->constrained('council_points')->onDelete('cascade');
            $table->foreignId('voting_option_id')->constrained('voting_options')->onDelete('cascade');
            $table->primary(['council_point_id', 'voting_option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_voting_option');
    }
};
