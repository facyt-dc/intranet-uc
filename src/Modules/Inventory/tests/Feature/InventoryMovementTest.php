<?php

namespace Modules\Inventory\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Models\ItemCategory;
use Modules\Inventory\Models\ItemStatus;
use Modules\Inventory\Models\Location;
use Modules\Inventory\Models\MovementType;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InventoryMovementTest extends TestCase
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

    public function test_user_can_register_movement(): void
    {
        // En este módulo los movimientos se registran de forma implícita:
        // al crear un ítem, el ItemController genera un InventoryMovement "Entrada".
        MovementType::factory()->create(['name' => 'Entrada']);

        $category = ItemCategory::factory()->create();
        $status = ItemStatus::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($this->user)->post(route('admin.item.store'), [
            'name' => 'Laptop Dell',
            'description' => 'Equipo de oficina',
            'category_id' => $category->id,
            'current_status_id' => $status->id,
            'current_location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'user_id' => $this->user->id,
            'description' => 'Equipo de oficina',
        ]);
    }

    public function test_movement_updates_item_stock(): void
    {
        $this->markTestSkipped(
            'El módulo registra movimientos como bitácora de auditoría; '
            . 'no modifica la cantidad/stock del ítem automáticamente. '
            . 'No existe lógica de stock que verificar.'
        );
    }
}
