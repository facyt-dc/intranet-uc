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
            $table->string('code')->unique();
            $table->string('name');
            $table->dateTime('date');
            $table->string('status')->default('Programado');
            $table->foreignId('director_id')
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // closed_at: Marca de tiempo para saber cuándo se cerró el consejo.
            // Es 'nullable' porque solo tendrá valor cuando el director lo cierre.
            $table->timestamp('closed_at')->nullable();
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
