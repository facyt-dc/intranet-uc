<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MovementType;

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