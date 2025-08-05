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
        Permission::create(['name' => 'create councils', 'description' => 'Create new councils']);
        Permission::create(['name' => 'edit councils', 'description' => 'Edit existing councils']);
        Permission::create(['name' => 'delete councils', 'description' => 'Delete councils']);
        Permission::create(['name' => 'vote in councils', 'description' => 'Vote in council meetings']);

        // create roles and assign created permissions
        $directorRole = Role::createOrFirst(['name' => 'director']);
        $directorRole->givePermissionTo(['create councils', 'edit councils', 'delete councils', 'vote in councils']);

        $counselorRole = Role::createOrFirst(['name' => 'counselor']);
        $counselorRole->givePermissionTo('vote in councils');
    }
}
