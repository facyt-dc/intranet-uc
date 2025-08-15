<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::firstOrCreate(['name' => 'create councils', 'description' => 'Create new councils', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit councils', 'description' => 'Edit existing councils', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete councils', 'description' => 'Delete councils', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'vote in councils', 'description' => 'Vote in council meetings', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view councils', 'description' => 'View councils', 'guard_name' => 'web']);

        // create roles and assign created permissions
        $directorRole = Role::createOrFirst(['name' => 'director', 'guard_name' => 'web']);
        $directorRole->givePermissionTo(['create councils', 'edit councils', 'delete councils', 'vote in councils, view councils']);

        $counselorRole = Role::createOrFirst(['name' => 'counselor', 'guard_name' => 'web']);
        $counselorRole->givePermissionTo('vote in councils, view councils');
    }
}
