<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->foreignId('equipment_category_id')->nullable()->constrained('equipment_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['equipment_category_id']);
            $table->dropColumn('equipment_category_id');
            $table->string('category')->nullable();
        });
    }
};
