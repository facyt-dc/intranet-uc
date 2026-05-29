<?php

namespace Modules\Maintenance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Maintenance\Models\MaintenanceRequest;
use Modules\Maintenance\Models\MaintenanceStage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KanbanBoardTest extends TestCase
{
    use RefreshDatabase;

    protected User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'web']);

        $this->technician = User::factory()->create();
        $this->technician->assignRole('technician');
    }

    public function test_kanban_index_returns_requests_grouped_by_stage(): void
    {
        $stage1 = MaintenanceStage::factory()->create(['sequence' => 1]);

        MaintenanceRequest::factory()->create([
            'user_id' => $this->technician->id,
            'stage_id' => $stage1->id,
            'is_archived' => false,
        ]);

        $response = $this->actingAs($this->technician)->get(route('mantenimiento.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('maintenance::KanbanBoard', false)
                ->has('initialStages')
        );
    }

    public function test_technician_can_move_request_to_different_stage(): void
    {
        $stage1 = MaintenanceStage::factory()->create(['sequence' => 1]);
        $stage2 = MaintenanceStage::factory()->create(['sequence' => 2]);

        $request = MaintenanceRequest::factory()->create([
            'user_id' => $this->technician->id,
            'stage_id' => $stage1->id,
        ]);

        $response = $this->actingAs($this->technician)->post(route('mantenimiento.updateStage', $request->id), [
            'stage' => $stage2->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('maintenance_requests', ['id' => $request->id, 'stage_id' => $stage2->id]);
    }
}
