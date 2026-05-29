<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Nwidart\Modules\Facades\Module;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $adminUser = User::firstOrCreate([
            'email' => 'admini@example.com',
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
        ]);

        User::firstOrCreate([
            'email' => 'regulari@example.com',
        ], [
            'name' => 'Regular User',
            'password' => bcrypt('password'),
        ]);

        // Permissions and Roles
        // Administrator, Director, Teacher and Administrative
        
        // Roles
        $admin          = Role::firstOrCreate(['name' => 'admin']);
        $director       = Role::firstOrCreate(['name' => 'director']);
        $teacher        = Role::firstOrCreate(['name' => 'teacher']);
        $administrative = Role::firstOrCreate(['name' => 'administrative']);

        // Permissions

        $isAdmin = Permission::firstOrCreate([
            'name' => 'isAdmin',
        ], [
            'description' => 'Permiso de Administrador'
        ]);
        $isDirector = Permission::firstOrCreate([
            'name' => 'isDirector',
        ], [
            'description' => 'Permiso de Director'
        ]);
        $isTeacher = Permission::firstOrCreate([
            'name' => 'isTeacher',
        ], [
            'description' => 'Permiso de Profesor'
        ]);
        $isAdministrative = Permission::firstOrCreate([
            'name' => 'isAdministrative',
        ], [
            'description' => 'Permiso Administrativo'
        ]);

        // Assing permissions to roles

        $isAdmin->syncRoles($admin);
        $isDirector->syncRoles([$admin, $director]);
        $isTeacher->syncRoles([$admin, $teacher]);
        $isAdministrative->syncRoles([$admin, $administrative]);

        // assing roles to users

        $adminUser->syncRoles(['admin', 'teacher']);

        // Seed modules conditionally

        $agendaModule = Module::find('AgendaConsejo');
        if ($agendaModule && $agendaModule->isEnabled()) {
            $this->call("Modules\\AgendaConsejo\\Database\\Seeders\\AgendaConsejoDatabaseSeeder");
        }

        $thesisModule = Module::find('Thesis');
        if ($thesisModule && $thesisModule->isEnabled()) {
            $this->call("Modules\\Thesis\\Database\\Seeders\\ThesisDatabaseSeeder");
        }

        $employeesModule = Module::find('Employees');
        if ($employeesModule && $employeesModule->isEnabled()) {
            $this->call("Modules\\Employees\\Database\\Seeders\\EmployeesDatabaseSeeder");
        }

        $maintenanceModule = Module::find('Maintenance');
        if ($maintenanceModule && $maintenanceModule->isEnabled()) {
            $this->call("Modules\\Maintenance\\Database\\Seeders\\MaintenanceDatabaseSeeder");
        }
    }
}
