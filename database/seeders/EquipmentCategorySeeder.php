<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EquipmentCategory;

class EquipmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Mecánico',
            'Eléctrico',
            'Electrónico',
            'Climatización (HVAC)',
            'Fontanería / Plomería',
            'Informático / TI',
            'Vehículos',
            'Edilicio / Infraestructura',
            'Seguridad',
            'Mobiliario'
        ];

        // Recorremos la lista y creamos cada categoría
        foreach ($categories as $categoryName) {
            // Usamos firstOrCreate para evitar crear duplicados si el seeder se ejecuta varias veces
            EquipmentCategory::firstOrCreate(['name' => $categoryName]);
        }
    }
}
