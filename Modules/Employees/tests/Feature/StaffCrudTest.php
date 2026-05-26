<?php

namespace Modules\Employees\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Employees\Models\Staff;
use Modules\Employees\Models\StaffType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $isAdmin = Permission::firstOrCreate(['name' => 'isAdmin', 'guard_name' => 'web'], ['description' => 'Permiso de Administrador']);
        $isAdmin->syncRoles($adminRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_list_staffs(): void
    {
        StaffType::factory()->create();
        Staff::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('employee.staff.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_staff(): void
    {
        $this->markTestSkipped(
            'Issue 1 documentado: violación de foreign key en staffs_type_foreign. ' .
            'Ver docs/issues/employees.md'
        );
    }
}
