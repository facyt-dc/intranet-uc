<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\maintenanceStage;

class maintenanceStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MaintenanceStage::create(['name' => 'Nueva Solicitud', 'sequence' => 1]);
        MaintenanceStage::create(['name' => 'En Progreso', 'sequence' => 2]);
        MaintenanceStage::create(['name' => 'Reparado', 'sequence' => 3]);
        MaintenanceStage::create(['name' => 'Descartado', 'sequence' => 4]);
    }
}
