<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InventoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(
            ['name' => 'inventory.access', 'guard_name' => 'web'],
            ['description' => 'Acceso a las funciones del módulo de inventario']
        );
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('admin.item.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_inventory_access_cannot_enter(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.item.index'));

        $response->assertForbidden();
    }

    public function test_user_with_inventory_access_can_enter(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('inventory.access');

        $response = $this->actingAs($user)->get(route('admin.item.index'));

        $response->assertOk();
    }
}
