<?php

namespace Modules\Maintenance\Database\Seeders;

use Illuminate\Database\Seeder;

class MaintenanceDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            TechnicianRoleSeeder::class,
            MaintenanceStageSeeder::class,
            MaintenanceRequestSeeder::class,
        ]);
    }
}
