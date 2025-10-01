<?php

namespace Database\Seeders;
use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['name' => 'Almacén Principal', 'description' => 'Depósito central de bienes'],
            ['name' => 'Laboratorio N°1', 'description' => 'Laboratorio de computación'],
            ['name' => 'Oficina de Dirección', 'description' => 'Despacho de Dirección General'],
            ['name' => 'Área de Desincorporados', 'description' => 'Zona de ítems retirados o inutilizados'],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
