<?php

namespace Modules\Thesis\Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests CRUD de Tesistas para el módulo Thesis.
 *
 * NOTA: Este módulo está actualmente vacío en la rama `develop`.
 * El código real existe en la rama `thesis-module` (App\Http\Controllers\Thesis\*
 * y App\Models\Thesis*) y aún no ha sido modularizado.
 *
 * Estos tests representan el esqueleto de pruebas esperado, definiendo la
 * especificación de comportamiento ANTES de la modularización. Se esperan
 * fallos hasta que el módulo esté completamente integrado en `develop`.
 *
 * Revisar docs/MODULES_COOKBOOK.md para el proceso de modularización.
 */
class ThesisStudentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->regularUser = User::factory()->create();
    }

    /** @test */
    public function test_admin_can_list_thesis_students(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Modularizar desde rama thesis-module siguiendo MODULES_COOKBOOK.md. ' .
            'Ruta esperada: GET /thesis/students'
        );
    }

    /** @test */
    public function test_admin_can_create_thesis_student(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Modularizar desde rama thesis-module siguiendo MODULES_COOKBOOK.md. ' .
            'Ruta esperada: POST /thesis/students con campos: cedula, name, status'
        );
    }

    /** @test */
    public function test_non_admin_cannot_access_thesis_students(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Modularizar desde rama thesis-module siguiendo MODULES_COOKBOOK.md. ' .
            'Comportamiento esperado: GET /thesis/students devuelve 403 para usuario sin rol admin'
        );
    }

    /** @test */
    public function test_thesis_student_requires_valid_status(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Modularizar desde rama thesis-module siguiendo MODULES_COOKBOOK.md. ' .
            'Comportamiento esperado: POST /thesis/students con status inválido devuelve 422'
        );
    }
}
