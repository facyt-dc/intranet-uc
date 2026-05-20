<?php

namespace Modules\Thesis\Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests del Gantt Chart del módulo Thesis.
 *
 * NOTA: Este módulo está actualmente vacío en la rama `develop`.
 * La rama `thesis-module` contiene GanttChartController.
 *
 * El plan de prioridades menciona explícitamente este test como
 * "importante porque protege contra la regresión del bug de operadores invertidos"
 * que fue corregido en la integración del módulo Thesis.
 *
 * Una vez modularizado, este test debe activarse (quitar el markTestSkipped).
 */
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
    }

    /** @test */
    public function test_gantt_chart_route_loads_successfully(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Smoke test crítico: protege contra la regresión del bug de operadores ' .
            'invertidos en GanttChartController. ' .
            'Comportamiento esperado: GET /thesis/gantt devuelve 200 sin error 500.'
        );
    }

    /** @test */
    public function test_gantt_chart_returns_valid_data_structure(): void
    {
        $this->markTestSkipped(
            'Módulo Thesis vacío en develop. ' .
            'Comportamiento esperado: la respuesta del Gantt contiene una estructura ' .
            'de datos con las columnas de fechas de inicio y fin correctamente ordenadas ' .
            '(inicio < fin para todos los items). ' .
            'Este test verifica el bug corregido de operadores invertidos.'
        );
    }
}
