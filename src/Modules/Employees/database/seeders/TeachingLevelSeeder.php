<?php

namespace Modules\Employees\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employees\Models\TeachingLevel;
use Modules\Employees\Models\TimeUnit;

class TeachingLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = TimeUnit::firstOrCreate(['name' => 'Año(s)']);

        // Niveles de docencia en orden ascendente, cada uno referencia al anterior
        $levels = [
            ['name' => 'Instructor', 'time' => 2],
            ['name' => 'Asistente', 'time' => 4],
            ['name' => 'Agregado', 'time' => 4],
            ['name' => 'Asociado', 'time' => 5],
            ['name' => 'Titular', 'time' => 1],
        ];

        $previousLevel = null;

        foreach ($levels as $level) {
            $previousLevel = TeachingLevel::firstOrCreate(['name' => $level['name']], [
                'time' => $level['time'],
                'time_unit' => $years->id,
                'previous_level' => $previousLevel?->id,
            ]);
        }
    }
}
