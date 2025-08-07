<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Council;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('councils', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('director_id')
                  ->constrained('users')
                  ->onUpdate('cascade') // Si el ID del usuario cambia, se actualiza aquí también.
                  ->onDelete('cascade'); // Impide que se pueda borrar un usuario si tiene consejos asociados.
            $table->string('status')->default(Council::STATUS_SCHEDULED);
            $table->dateTime('scheduled_at');
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('councils');
    }
};
