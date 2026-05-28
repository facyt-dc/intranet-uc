<?php

namespace Modules\Employees\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeesAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('employee.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_role_cannot_access(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('employee.index'));

        $response->assertForbidden();
    }
}
