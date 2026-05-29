<?php

namespace Modules\Maintenance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Maintenance\Models\Equipment;
use Modules\Maintenance\Models\MaintenanceRequest;
use Modules\Maintenance\Models\MaintenanceStage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaintenanceRequestCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;
    protected MaintenanceStage $stage;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web']);

        $this->technician = User::factory()->create();
        $this->technician->assignRole('technician');

        $this->stage = MaintenanceStage::factory()->create(['sequence' => 1]);
    }

    public function test_technician_can_create_maintenance_request(): void
    {
        $equipment = Equipment::factory()->create();

        $response = $this->actingAs($this->technician)->post(route('mantenimiento.store'), [
            'title' => 'Falla en compresor',
            'description' => 'El compresor no enciende.',
            'type' => 'corrective',
            'user_id' => $this->technician->id,
            'stage_id' => $this->stage->id,
            'equipment_id' => $equipment->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('maintenance_requests', ['title' => 'Falla en compresor']);
    }

    public function test_technician_can_view_maintenance_request(): void
    {
        $request = MaintenanceRequest::factory()->create([
            'user_id' => $this->technician->id,
            'stage_id' => $this->stage->id,
        ]);

        $response = $this->actingAs($this->technician)->get(route('mantenimiento.show', $request->id));

        $response->assertOk();
    }

    public function test_technician_can_delete_maintenance_request(): void
    {
        $request = MaintenanceRequest::factory()->create([
            'user_id' => $this->technician->id,
            'stage_id' => $this->stage->id,
        ]);

        $response = $this->actingAs($this->technician)->delete(route('mantenimiento.destroy', $request->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('maintenance_requests', ['id' => $request->id]);
    }

    public function test_technician_can_archive_maintenance_request(): void
    {
        $request = MaintenanceRequest::factory()->create([
            'user_id' => $this->technician->id,
            'stage_id' => $this->stage->id,
            'is_archived' => false,
        ]);

        $this->actingAs($this->technician)->post(route('mantenimiento.toggleArchive', $request->id));

        $this->assertDatabaseHas('maintenance_requests', ['id' => $request->id, 'is_archived' => true]);
    }
}
