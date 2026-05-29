<?php

namespace Modules\Maintenance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Maintenance\Models\EquipmentCategory;

class EquipmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Eléctrico',
            'Mecánico',
            'Audiovisual',
            'Mobiliario',
            'Informática',
            'Climatización',
        ];

        foreach ($categories as $name) {
            EquipmentCategory::firstOrCreate(['name' => $name]);
        }
    }
}
