<?php

namespace Modules\AgendaConsejo\Tests\Feature;

use App\Models\User;
use Modules\AgendaConsejo\Models\Agenda;
use Modules\AgendaConsejo\Models\AgendaPoint;
use Modules\AgendaConsejo\Models\Vote;
use Modules\AgendaConsejo\Models\VotingOption;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VotingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $director;
    protected User $counselor;
    protected Agenda $agenda;
    protected AgendaPoint $point;
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

        $this->point = AgendaPoint::factory()->create([
            'agenda_id' => $this->agenda->id,
            'requested_by_user_id' => $this->counselor->id,
            'status' => 'Pendiente',
            'min_votes_to_close' => 1,
            'order' => 1,
        ]);
        $this->point->votableUsers()->sync([$this->counselor->id]);
        $this->point->votingOptions()->sync([$this->option->id]);
    }

    /** @test */
    public function test_counselor_can_cast_vote_on_point(): void
    {
        $response = $this->actingAs($this->counselor)
            ->post(route('points.votes.store', $this->point), [
                'voting_option_id' => $this->option->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('votes', [
            'agenda_point_id'  => $this->point->id,
            'user_id'          => $this->counselor->id,
            'voting_option_id' => $this->option->id,
        ]);
    }

    /** @test */
    public function test_counselor_cannot_vote_twice_on_same_point(): void
    {
        // Primer voto
        Vote::create([
            'agenda_point_id'  => $this->point->id,
            'user_id'          => $this->counselor->id,
            'voting_option_id' => $this->option->id,
        ]);

        // Intento de segundo voto
        $response = $this->actingAs($this->counselor)
            ->post(route('points.votes.store', $this->point), [
                'voting_option_id' => $this->option->id,
            ]);

        // El controlador hace un catch de QueryException y redirige con error
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Solo debe existir un voto
        $this->assertDatabaseCount('votes', 1);
    }

    /** @test */
    public function test_director_cannot_vote(): void
    {
        // El director no tiene el rol 'counselor', por lo que la ruta de votar
        // está protegida por 'role:counselor' → debe recibir 403
        $response = $this->actingAs($this->director)
            ->post(route('points.votes.store', $this->point), [
                'voting_option_id' => $this->option->id,
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function test_vote_is_persisted_in_database(): void
    {
        $this->actingAs($this->counselor)
            ->post(route('points.votes.store', $this->point), [
                'voting_option_id' => $this->option->id,
            ]);

        $vote = Vote::where('user_id', $this->counselor->id)
            ->where('agenda_point_id', $this->point->id)
            ->first();

        $this->assertNotNull($vote);
        $this->assertEquals($this->option->id, $vote->voting_option_id);
    }
}
