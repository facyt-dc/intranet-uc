<?php

namespace Modules\Inventory\Database\Seeders;

use Modules\Inventory\Models\ItemStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Activo', 'description' => 'Disponible para uso', 'is_operational' => true],
            ['name' => 'Dañado', 'description' => 'Necesita reparación', 'is_operational' => false],
            ['name' => 'En Reparación', 'description' => 'En proceso de reparación', 'is_operational' => false],
            ['name' => 'Desincorporado', 'description' => 'Fuera del sistema o inventario activo', 'is_operational' => false],
            ['name' => 'En Almacén', 'description' => 'Guardado sin uso activo', 'is_operational' => true],
        ];

        foreach ($statuses as $status) {
            ItemStatus::create($status);
        }
    }
}
