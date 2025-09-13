<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admini@example.com',
        ]);

        User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regulari@example.com',
        ]);

        // Permissions and Roles
        // Administrator, Director, Teacher and Administrative
        
        // Roles
        $admin          = Role::create(['name' => 'admin']);
        $director       = Role::create(['name' => 'director']);
        $teacher        = Role::create(['name' => 'teacher']);
        $administrative = Role::create(['name' => 'administrative']);
        $technician     = Role::create(['name' => 'technician']);
        // Permissions

        $isAdmin = Permission::create([
            'name' => 'isAdmin',
            'description' => 'Permiso de Administrador'
        ]);
        $isDirector = Permission::create([
            'name' => 'isDirector',
            'description' => 'Permiso de Director'
        ]);
        $isTeacher = Permission::create([
            'name' => 'isTeacher',
            'description' => 'Permiso de Profesor'
        ]);
        $isAdministrative = Permission::create([
            'name' => 'isAdministrative',
            'description' => 'Permiso Administrativo'
        ]);
        $isTechnician = Permission::create([
            'name' => 'isTechnician',
            'description' => 'Permiso Tecnico'
        ]);

        // Assing permissions to roles

        $isAdmin->syncRoles($admin);
        $isDirector->syncRoles([$admin, $director]);
        $isTeacher->syncRoles([$admin, $teacher]);
        $isAdministrative->syncRoles([$admin, $administrative]);
        $isTechnician->syncRoles([$admin, $technician]);

        // assing roles to users
        $this->call(EquipmentSeeder::class);
        $adminUser->assignRole(['admin','teacher']);
        $this->call(maintenanceStageSeeder::class);
        $this->call([
            MaintenanceRequestSeeder::class,
        ]);
        $this->call(EquipmentCategorySeeder::class);
    }
}
