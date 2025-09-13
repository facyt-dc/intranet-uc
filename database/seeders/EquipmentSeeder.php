<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Equipment;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $equipmentData = [
            [
                'name' => 'Laptop de Marketing 01',
                'brand' => 'Dell',
                'model' => 'Latitude 7420',
                'serial_number' => 'SN-' . Str::upper(Str::random(8)),
                'description' => 'Equipo asignado a Julia en el departamento de Marketing.',
                'last_maintained_at' => Carbon::now()->subDays(90),
                'next_maintenance_at' => Carbon::now()->addMonths(6),
                'mtbf' => 4500, // Tiempo medio entre fallos en horas
                'mttr' => 4,     // Tiempo medio de reparación en horas
            ],
            [
                'name' => 'PC de Contabilidad',
                'brand' => 'HP',
                'model' => 'EliteDesk 800 G6',
                'serial_number' => 'SN-' . Str::upper(Str::random(8)),
                'description' => 'Computadora con software contable especializado.',
                'last_maintained_at' => Carbon::now()->subDays(120),
                'next_maintenance_at' => Carbon::now()->addMonths(8),
                'mtbf' => 6000,
                'mttr' => 3.5,
            ],
            [
                'name' => 'MacBook Pro de Diseño',
                'brand' => 'Apple',
                'model' => 'MacBook Pro 16" M1',
                'serial_number' => 'SN-' . Str::upper(Str::random(8)),
                'description' => 'Equipo de alto rendimiento para el equipo de diseño gráfico.',
                'last_maintained_at' => Carbon::now()->subDays(45),
                'next_maintenance_at' => Carbon::now()->addMonths(5),
                'mtbf' => 8000,
                'mttr' => 6,
            ],
            [
                'name' => 'PC de Recepción',
                'brand' => 'Lenovo',
                'model' => 'ThinkCentre M70q',
                'serial_number' => 'SN-' . Str::upper(Str::random(8)),
                'description' => 'Equipo para tareas administrativas en la recepción principal.',
                'last_maintained_at' => Carbon::now()->subDays(200),
                'next_maintenance_at' => Carbon::now()->addMonths(4),
                'mtbf' => 5500,
                'mttr' => 2,
            ],
            [
                'name' => 'Laptop de Ventas 03',
                'brand' => 'Lenovo',
                'model' => 'ThinkPad T14',
                'serial_number' => 'SN-' . Str::upper(Str::random(8)),
                'description' => 'Laptop para ejecutivo de ventas de campo.',
                'last_maintained_at' => Carbon::now()->subDays(60),
                'next_maintenance_at' => Carbon::now()->addMonths(6),
                'mtbf' => 7000,
                'mttr' => 3,
            ],
        ];

        // Recorremos el array y creamos cada registro en la base de datos.
        foreach ($equipmentData as $data) {
            Equipment::create($data);
        }
    }
}
