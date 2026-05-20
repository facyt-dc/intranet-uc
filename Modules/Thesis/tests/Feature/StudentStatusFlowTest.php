<?php

namespace Modules\Thesis\Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests del flujo de cambio de estatus de tesistas.
 *
 * NOTA: Este módulo está actualmente vacío en la rama `develop`.
 * El código real en la rama `thesis-module` incluye StudentStatusesController
 * y los modelos ThesisStudent, ThesisStudentStatus, ThesisStudentStatusHistory.
 *
 * Flujo esperado: inscrito → PTEG inscrito → TEG inscrito
 */
class StudentStatusFlowTest extends TestCase
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
    }

    /** @test */
    public function test_default_statuses_are_seeded(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Comportamiento esperado: después de migrate:fresh --seed, existen los registros ' .
            '"inscrito", "PTEG inscrito" y "TEG inscrito" en la tabla de estatus de tesistas. ' .
            'Revisar ThesisDatabaseSeeder en la rama thesis-module.'
        );
    }

    /** @test */
    public function test_student_status_can_be_changed(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Comportamiento esperado: PATCH /thesis/students/{id}/status cambia el estatus ' .
            'del tesista correctamente (inscrito → PTEG inscrito).'
        );
    }

    /** @test */
    public function test_status_change_creates_history_entry(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Comportamiento esperado: al cambiar el estatus, se crea un registro en la tabla ' .
            'de historial de cambios de estatus del tesista.'
        );
    }
}
