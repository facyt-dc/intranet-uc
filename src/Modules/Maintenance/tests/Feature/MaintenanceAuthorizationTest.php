<?php

namespace Modules\Maintenance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaintenanceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web']);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('mantenimiento.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_role_cannot_access(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('mantenimiento.index'));

        $response->assertForbidden();
    }

    public function test_technician_can_access_index(): void
    {
        $user = User::factory()->create();
        $user->assignRole('technician');

        $response = $this->actingAs($user)->get(route('mantenimiento.index'));

        $response->assertOk();
    }
}
