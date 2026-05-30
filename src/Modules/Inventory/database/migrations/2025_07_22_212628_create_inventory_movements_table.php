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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('user_id')->constrained('users')->comment('Usuario que realiza el movimiento');
            $table->string('description');
            $table->json('details')->nullable()->comment('Cambios registrados en formato JSON');
            $table->timestamp('movement_date');
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('movement_type_id')->constrained('movement_types')->comment('Tipo de movimiento realizado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
