<?php

namespace Modules\Employees\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employees\Models\TimeUnit;
use Modules\Employees\Models\StaffType;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Unidades de tiempo
        TimeUnit::firstOrCreate(['name' => 'Día(s)']);
        TimeUnit::firstOrCreate(['name' => 'Mes(es)']);
        TimeUnit::firstOrCreate(['name' => 'año(s)']);
        TimeUnit::firstOrCreate(['name' => 'Semana(s)']);
        TimeUnit::firstOrCreate(['name' => 'Hora(s)']);

        // Tipo de personal
        StaffType::firstOrCreate(['name' => 'Administrativo']);
        StaffType::firstOrCreate(['name' => 'Obrero']);
        StaffType::firstOrCreate(['name' => 'Docente']);
    }
}
