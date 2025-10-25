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
        Permission::firstOrCreate(['name' => 'create agendas', 'description' => 'Create new agendas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit agendas', 'description' => 'Edit existing agendas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete agendas', 'description' => 'Delete agendas', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'vote in agendas', 'description' => 'Vote in agenda meetings', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view agendas', 'description' => 'View agendas', 'guard_name' => 'web']);

        // create roles and assign created permissions
        $directorRole = Role::createOrFirst(['name' => 'director', 'guard_name' => 'web']);
        $directorRole->givePermissionTo(['create agendas', 'edit agendas', 'delete agendas', 'vote in agendas', 'view agendas']);

        $counselorRole = Role::createOrFirst(['name' => 'counselor', 'guard_name' => 'web']);
        $counselorRole->givePermissionTo('vote in agendas', 'view agendas');
    }
}
