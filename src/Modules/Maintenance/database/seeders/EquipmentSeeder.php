<?php

namespace Modules\Maintenance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Maintenance\Models\Equipment;
use Modules\Maintenance\Models\EquipmentCategory;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $demoEquipment = [
            'Informática' => [
                ['name' => 'Computadora Lab 1', 'serial_number' => 'PC-LAB-001'],
                ['name' => 'Computadora Lab 2', 'serial_number' => 'PC-LAB-002'],
            ],
            'Audiovisual' => [
                ['name' => 'Proyector Aula 101', 'serial_number' => 'PROJ-101'],
            ],
            'Climatización' => [
                ['name' => 'Aire acondicionado Aula 101', 'serial_number' => 'AC-101'],
            ],
            'Eléctrico' => [
                ['name' => 'Tablero eléctrico principal', 'serial_number' => 'TAB-001'],
            ],
        ];

        foreach ($demoEquipment as $categoryName => $equipos) {
            $category = EquipmentCategory::where('name', $categoryName)->first();
            if (!$category) {
                continue;
            }

            foreach ($equipos as $data) {
                Equipment::firstOrCreate(
                    ['serial_number' => $data['serial_number']],
                    [
                        'name' => $data['name'],
                        'equipment_category_id' => $category->id,
                    ]
                );
            }
        }
    }
}
