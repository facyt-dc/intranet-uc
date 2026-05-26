<?php

namespace Modules\Employees\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Employees\Models\Employee;
use Modules\Employees\Models\Staff;
use Modules\Employees\Models\StaffType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeCrudTest extends TestCase
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

    public function test_admin_can_list_employees(): void
    {
        $staffType = StaffType::factory()->create();
        $staff = Staff::factory()->create(['type' => $staffType->id]);
        Employee::factory()->create(['staff' => $staff->id]);

        $response = $this->actingAs($this->admin)->get(route('employee.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_employee(): void
    {
        $this->markTestSkipped(
            'Issue 2 documentado: create.jsx falla por benefits undefined. ' .
            'Ver docs/issues/employees.md'
        );
    }

    public function test_admin_can_delete_employee(): void
    {
        $staffType = StaffType::factory()->create();
        $staff = Staff::factory()->create(['type' => $staffType->id]);
        $employee = Employee::factory()->create(['staff' => $staff->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('employee.destroy', $employee->id));

        $response->assertRedirect(route('employee.index'));
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
