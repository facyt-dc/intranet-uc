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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('uc_code', 100)->unique()->nullable()->comment('Código de Inventario UC');
            $table->string('department_code', 100)->unique()->nullable()->comment('Código Interno del Departamento');
            $table->string('serial_number')->unique()->nullable()->comment('Serial para equipos tecnológicos');
            $table->text('description')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();
            $table->integer('quantity')->default(1)->comment('Cantidad de ítems similares');

            $table->foreignId('category_id')->constrained('item_categories');
            $table->foreignId('current_status_id')->constrained('item_statuses');
            $table->foreignId('current_location_id')->constrained('locations');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
