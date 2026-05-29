<?php

namespace Modules\Maintenance\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TechnicianRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate([
            'name' => 'technician',
            'guard_name' => 'web',
        ]);
    }
}
