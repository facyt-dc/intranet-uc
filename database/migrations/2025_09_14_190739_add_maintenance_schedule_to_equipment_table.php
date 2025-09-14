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
        Schema::table('equipment', function (Blueprint $table) {
            $table->integer('maintenance_frequency')->nullable()->after('next_maintenance_at');
            // La unidad de tiempo
            $table->enum('maintenance_interval', ['days', 'months', 'years'])->nullable()->after('maintenance_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['maintenance_frequency', 'maintenance_interval']);
        });
    }
};
