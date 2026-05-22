<?php

namespace Modules\Thesis\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Thesis\Models\StudentStatus;
use Modules\Thesis\Models\StudentStatusHistory;
use Modules\Thesis\Models\ThesisStudent;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GanttChartTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $inscrito = StudentStatus::firstOrCreate(['name' => 'inscrito'], ['description' => 'Estudiante inscrito']);
        $ptegInscrito = StudentStatus::firstOrCreate(['name' => 'PTEG inscrito'], ['description' => 'El estudiante ha inscrito PTEG']);
        StudentStatus::firstOrCreate(['name' => 'TEG inscrito'], ['description' => 'El estudiante ha inscrito TEG']);

        $student = ThesisStudent::factory()->create(['status_id' => $ptegInscrito->id]);

        StudentStatusHistory::create([
            'thesis_student_id' => $student->id,
            'student_status_id' => $inscrito->id,
            'start_date' => now()->subWeeks(8),
        ]);

        StudentStatusHistory::create([
            'thesis_student_id' => $student->id,
            'student_status_id' => $ptegInscrito->id,
            'start_date' => now()->subWeeks(2),
        ]);
    }

    public function test_gantt_chart_route_loads_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('thesis.ganttChart'));

        $response->assertOk();
    }
}