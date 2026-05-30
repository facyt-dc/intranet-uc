<?php

namespace Modules\Inventory\Database\Seeders;

use Modules\Inventory\Models\ItemCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Mobiliario', 'description' => 'Mesas, sillas, escritorios, etc.'],
            ['name' => 'Equipo Tecnológico', 'description' => 'Computadoras, proyectores, periféricos, etc.'],
        ];

        foreach ($categories as $category) {
            ItemCategory::create($category);
        }
    }
}
