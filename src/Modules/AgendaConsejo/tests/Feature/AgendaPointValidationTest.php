<?php

namespace Modules\AgendaConsejo\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AgendaConsejo\Models\Agenda;
use Modules\AgendaConsejo\Models\VotingOption;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgendaPointValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $director;
    protected User $counselor;
    protected Agenda $agenda;
    protected VotingOption $option;

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

        $this->agenda = Agenda::factory()->create([
            'director_id' => $this->director->id,
        ]);
        $this->agenda->participants()->sync([$this->counselor->id]);

        $this->option = VotingOption::create(['name' => 'A favor', 'is_active' => true]);
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'description' => 'Descripción del punto de prueba',
            'requested_by_user_id' => $this->counselor->id,
            'min_votes_to_close' => 1,
            'votable_users' => [$this->counselor->id],
            'available_options' => [$this->option->id],
        ], $overrides);
    }

    public function test_director_can_create_agenda_point(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('agendas.points.store', $this->agenda), $this->validPayload());

        $response->assertRedirect(route('agendas.show', $this->agenda));
        $this->assertDatabaseHas('agenda_points', [
            'agenda_id' => $this->agenda->id,
            'description' => 'Descripción del punto de prueba',
            'requested_by_user_id' => $this->counselor->id,
        ]);
    }

    public function test_counselor_cannot_create_agenda_point(): void
    {
        $response = $this->actingAs($this->counselor)
            ->post(route('agendas.points.store', $this->agenda), $this->validPayload());

        $response->assertForbidden();
        $this->assertDatabaseCount('agenda_points', 0);
    }

    public function test_agenda_point_requires_description(): void
    {
        $response = $this->actingAs($this->director)
            ->post(
                route('agendas.points.store', $this->agenda),
                $this->validPayload(['description' => null])
            );

        $response->assertSessionHasErrors(['description']);
        $this->assertDatabaseCount('agenda_points', 0);
    }

    public function test_agenda_point_requires_requested_by_user(): void
    {
        $response = $this->actingAs($this->director)
            ->post(
                route('agendas.points.store', $this->agenda),
                $this->validPayload(['requested_by_user_id' => null])
            );

        $response->assertSessionHasErrors(['requested_by_user_id']);
        $this->assertDatabaseCount('agenda_points', 0);
    }

    public function test_agenda_point_requested_by_must_be_participant(): void
    {
        $outsider = User::factory()->create();
        $outsider->assignRole('counselor');

        $response = $this->actingAs($this->director)
            ->post(
                route('agendas.points.store', $this->agenda),
                $this->validPayload(['requested_by_user_id' => $outsider->id])
            );

        $response->assertSessionHasErrors(['requested_by_user_id']);
        $this->assertDatabaseCount('agenda_points', 0);
    }

    public function test_agenda_point_requires_at_least_one_votable_user(): void
    {
        $response = $this->actingAs($this->director)
            ->post(
                route('agendas.points.store', $this->agenda),
                $this->validPayload(['votable_users' => []])
            );

        $response->assertSessionHasErrors(['votable_users']);
        $this->assertDatabaseCount('agenda_points', 0);
    }

    public function test_agenda_point_requires_at_least_one_voting_option(): void
    {
        $response = $this->actingAs($this->director)
            ->post(
                route('agendas.points.store', $this->agenda),
                $this->validPayload(['available_options' => []])
            );

        $response->assertSessionHasErrors(['available_options']);
        $this->assertDatabaseCount('agenda_points', 0);
    }

    public function test_agenda_point_min_votes_cannot_exceed_votable_users(): void
    {
        $response = $this->actingAs($this->director)
            ->post(
                route('agendas.points.store', $this->agenda),
                $this->validPayload([
                    'votable_users' => [$this->counselor->id],
                    'min_votes_to_close' => 5,
                ])
            );

        $response->assertSessionHasErrors(['min_votes_to_close']);
        $this->assertDatabaseCount('agenda_points', 0);
    }

    public function test_agenda_point_belongs_to_existing_agenda(): void
    {
        $response = $this->actingAs($this->director)
            ->post('/agendas/codigo-inexistente/points', $this->validPayload());

        $response->assertNotFound();
        $this->assertDatabaseCount('agenda_points', 0);
    }
}
