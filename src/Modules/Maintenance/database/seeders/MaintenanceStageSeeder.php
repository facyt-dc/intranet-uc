<?php

namespace Modules\Maintenance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Maintenance\Models\MaintenanceStage;

class MaintenanceStageSeeder extends Seeder
{
    public function run(): void
    {
        MaintenanceStage::firstOrCreate(['name' => 'Nueva Solicitud'], ['sequence' => 1]);
        MaintenanceStage::firstOrCreate(['name' => 'En Progreso'], ['sequence' => 2]);
        MaintenanceStage::firstOrCreate(['name' => 'Reparado'], ['sequence' => 3, 'is_final_stage' => true]);
        MaintenanceStage::firstOrCreate(['name' => 'Descartado'], ['sequence' => 4, 'is_final_stage' => true]);
    }
}
