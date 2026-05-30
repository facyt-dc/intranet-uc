<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemCategory;
use Modules\Inventory\Models\ItemStatus;
use Modules\Inventory\Models\Location;
use Modules\Inventory\Models\MovementType;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ItemCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(
            ['name' => 'inventory.access', 'guard_name' => 'web'],
            ['description' => 'Acceso a las funciones del módulo de inventario']
        );

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('inventory.access');
    }

    public function test_user_with_access_can_list_items(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.item.index'));

        $response->assertOk();
    }

    public function test_user_with_access_can_create_item(): void
    {
        // El ItemController registra un movimiento de tipo "Entrada" al crear.
        MovementType::factory()->create(['name' => 'Entrada']);

        $category = ItemCategory::factory()->create();
        $status = ItemStatus::factory()->create();
        $location = Location::factory()->create();

        $response = $this->actingAs($this->user)->post(route('admin.item.store'), [
            'name' => 'Proyector Epson',
            'description' => 'Proyector para el laboratorio',
            'category_id' => $category->id,
            'current_status_id' => $status->id,
            'current_location_id' => $location->id,
        ]);

        $response->assertRedirect(route('admin.item.index'));
        $this->assertDatabaseHas('items', [
            'name' => 'Proyector Epson',
            'category_id' => $category->id,
        ]);
    }

    public function test_user_with_access_can_delete_item(): void
    {
        $item = Item::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.item.destroy', $item));

        $response->assertRedirect(route('admin.item.index'));
        $this->assertSoftDeleted('items', ['id' => $item->id]);
    }
}
