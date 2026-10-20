<?php

namespace Modules\Employees\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employees\Models\Benefit;
use Modules\Employees\Models\TimeUnit;

class BenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days   = TimeUnit::firstOrCreate(['name' => 'Día(s)']);
        $months = TimeUnit::firstOrCreate(['name' => 'Mes(es)']);
        $years  = TimeUnit::firstOrCreate(['name' => 'Año(s)']);

        // Beneficios base de ejemplo, pendientes por confirmar con el área de personal
        $benefits = [
            [
                'name' => 'Vacaciones',
                'time_lapse' => 1,
                'time_lapse_unit' => $months->id,
                'time_between_use' => 1,
                'time_between_use_unit' => $years->id,
            ],
            [
                'name' => 'Permiso remunerado',
                'time_lapse' => 3,
                'time_lapse_unit' => $days->id,
                'time_between_use' => 6,
                'time_between_use_unit' => $months->id,
            ],
        ];

        foreach ($benefits as $benefit) {
            Benefit::firstOrCreate(['name' => $benefit['name']], $benefit);
        }
    }
}
