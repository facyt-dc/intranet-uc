<?php

namespace Modules\Maintenance\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TechnicianRoleSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'technician', 'guard_name' => 'web']
        );

        $permission = Permission::firstOrCreate(
            ['name' => 'isTechnician', 'guard_name' => 'web'],
            ['description' => 'Acceso a las funciones del módulo de mantenimiento']
        );

        $role->givePermissionTo($permission);

        app()['cache']->forget('spatie.permission.cache');
    }
}
