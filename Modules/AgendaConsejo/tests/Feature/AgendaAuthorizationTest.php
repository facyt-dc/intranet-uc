<?php

namespace Modules\AgendaConsejo\Tests\Feature;

use App\Models\User;
use Modules\AgendaConsejo\Models\Agenda;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgendaAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $director;
    protected User $counselor;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'director', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'counselor', 'guard_name' => 'web']);

        $this->director = User::factory()->create();
        $this->director->assignRole('director');

        $this->counselor = User::factory()->create();
        $this->counselor->assignRole('counselor');

        $this->regularUser = User::factory()->create();
        // regularUser no tiene ningún rol
    }

    /** @test */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('agendas.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_user_without_role_cannot_access_agendas(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('agendas.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function test_counselor_can_view_assigned_agendas(): void
    {
        $agenda = Agenda::create([
            'name'        => 'Consejo para Consejero',
            'date'        => now()->addDays(5)->toDateString(),
            'director_id' => $this->director->id,
            'status'      => 'Programado',
        ]);
        $agenda->participants()->sync([$this->counselor->id]);

        $response = $this->actingAs($this->counselor)
            ->get(route('agendas.index'));

        $response->assertOk();
    }

    /** @test */
    public function test_counselor_cannot_create_or_edit_agendas(): void
    {
        $agenda = Agenda::create([
            'name'        => 'Consejo Existente',
            'date'        => now()->addDays(3)->toDateString(),
            'director_id' => $this->director->id,
            'status'      => 'Programado',
        ]);

        // Counselor no puede acceder al formulario de creación
        $this->actingAs($this->counselor)
            ->get(route('agendas.create'))
            ->assertForbidden();

        // Counselor no puede acceder al formulario de edición
        $this->actingAs($this->counselor)
            ->get(route('agendas.edit', $agenda))
            ->assertForbidden();
    }
}
