<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\MovementType;

class MovementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Creación'],
            ['name' => 'Actualización'],
            ['name' => 'Cambio de Estado'],
            ['name' => 'Cambio de Ubicación'],
            ['name' => 'Archivo'],
            ['name' => 'Transferencia'],
            ['name' => 'Entrada'],
            ['name' => 'Salida'],
            ['name' => 'Ajuste'],
        ];

        foreach ($types as $type) {
            MovementType::create($type);
        }
    }
}
