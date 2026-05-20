<?php

namespace Modules\Thesis\Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests de autorización para el módulo Thesis.
 *
 * NOTA: Este módulo está actualmente vacío en la rama `develop`.
 * Estos tests son el esqueleto de especificación esperado.
 * Se esperan fallos hasta que el módulo esté modularizado desde la rama `thesis-module`.
 */
class ThesisAuthorizationTest extends TestCase
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
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Comportamiento esperado: GET /thesis/* redirige a login si no autenticado'
        );
    }

    /** @test */
    public function test_regular_user_cannot_access_thesis_module(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Comportamiento esperado: usuario sin rol admin recibe 403 en rutas /thesis/*'
        );
    }

    /** @test */
    public function test_admin_can_access_all_thesis_routes(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Comportamiento esperado: admin puede acceder a index, create, show, edit de /thesis/students'
        );
    }
}
