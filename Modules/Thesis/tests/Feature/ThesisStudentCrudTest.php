<?php

namespace Modules\Thesis\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Thesis\Models\StudentStatus;
use Modules\Thesis\Models\ThesisStudent;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ThesisStudentCrudTest extends TestCase
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

        StudentStatus::firstOrCreate(['name' => 'inscrito'], ['description' => 'Estudiante inscrito']);
        StudentStatus::firstOrCreate(['name' => 'PTEG inscrito'], ['description' => 'El estudiante ha inscrito PTEG']);
        StudentStatus::firstOrCreate(['name' => 'TEG inscrito'], ['description' => 'El estudiante ha inscrito TEG']);
    }

    public function test_admin_can_list_thesis_students(): void
    {
        ThesisStudent::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get(route('thesisStudent.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_thesis_student(): void
    {
        $response = $this->actingAs($this->admin)->post(route('thesisStudent.store'), [
            'id_uc' => '20260001',
            'name' => 'Tesista de Prueba',
            'email' => 'tesista@example.com',
            'ci' => '100200300',
        ]);

        $response->assertRedirect(route('thesisStudent.index'));
        $this->assertDatabaseHas('thesis_student', [
            'id_uc' => '20260001',
            'name' => 'Tesista de Prueba',
            'email' => 'tesista@example.com',
            'ci' => '100200300',
        ]);
    }

    public function test_admin_can_update_thesis_student(): void
    {
        $student = ThesisStudent::factory()->create();
        $targetStatus = StudentStatus::firstOrCreate(['name' => 'TEG inscrito'], ['description' => 'El estudiante ha inscrito TEG']);

        $response = $this->actingAs($this->admin)->put(route('thesisStudent.update', $student), [
            'id_uc' => '20260002',
            'name' => 'Tesista Actualizado',
            'email' => 'tesista.actualizado@example.com',
            'ci' => '300200100',
            'status_id' => $targetStatus->id,
        ]);

        $response->assertRedirect(route('thesisStudent.index'));
        $this->assertDatabaseHas('thesis_student', [
            'id' => $student->id,
            'name' => 'Tesista Actualizado',
            'status_id' => $targetStatus->id,
        ]);
    }

    public function test_admin_can_delete_thesis_student(): void
    {
        $student = ThesisStudent::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('thesisStudent.destroy', $student));

        $response->assertRedirect(route('thesisStudent.index'));
        $this->assertDatabaseMissing('thesis_student', ['id' => $student->id]);
    }
}