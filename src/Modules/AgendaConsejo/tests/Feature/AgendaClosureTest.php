<?php

namespace Modules\AgendaConsejo\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AgendaConsejo\Models\Agenda;
use Modules\AgendaConsejo\Models\AgendaPoint;
use Modules\AgendaConsejo\Models\VotingOption;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgendaClosureTest extends TestCase
{
    use RefreshDatabase;

    protected User $director;
    protected User $counselor;
    protected Agenda $agenda;

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
            'status' => 'Programado',
        ]);
        $this->agenda->participants()->sync([$this->counselor->id]);
    }

    public function test_director_can_close_agenda(): void
    {
        $response = $this->actingAs($this->director)
            ->put(route('agendas.close', $this->agenda));

        $response->assertRedirect(route('agendas.show', $this->agenda));
        $this->assertDatabaseHas('agendas', [
            'id' => $this->agenda->id,
            'status' => 'Cerrado',
        ]);

        $this->assertNotNull($this->agenda->fresh()->closed_at);
    }

    public function test_counselor_cannot_close_agenda(): void
    {
        $response = $this->actingAs($this->counselor)
            ->put(route('agendas.close', $this->agenda));

        $response->assertForbidden();
        $this->assertDatabaseHas('agendas', [
            'id' => $this->agenda->id,
            'status' => 'Programado',
        ]);
    }

    public function test_closed_agenda_cannot_be_updated(): void
    {
        $this->agenda->update(['status' => 'Cerrado', 'closed_at' => now()]);

        $response = $this->actingAs($this->director)
            ->put(route('agendas.update', $this->agenda), [
                'name' => 'Intento de Actualización',
                'date' => now()->addDays(10)->toDateString(),
                'participants' => [$this->counselor->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('agendas', [
            'id' => $this->agenda->id,
            'name' => 'Intento de Actualización',
        ]);
    }

    public function test_closed_agenda_cannot_receive_votes(): void
    {
        $option = VotingOption::create(['name' => 'A favor', 'is_active' => true]);

        $point = AgendaPoint::factory()->create([
            'agenda_id' => $this->agenda->id,
            'requested_by_user_id' => $this->counselor->id,
        ]);
        $point->votableUsers()->sync([$this->counselor->id]);
        $point->votingOptions()->sync([$option->id]);

        $this->agenda->update(['status' => 'Cerrado', 'closed_at' => now()]);

        $response = $this->actingAs($this->counselor)
            ->post(route('points.votes.store', $point), [
                'voting_option_id' => $option->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_closed_agenda_cannot_receive_new_points(): void
    {
        $this->agenda->update(['status' => 'Cerrado', 'closed_at' => now()]);
        $option = VotingOption::create(['name' => 'A favor', 'is_active' => true]);

        $response = $this->actingAs($this->director)
            ->post(route('agendas.points.store', $this->agenda), [
                'description' => 'Punto que no debería crearse',
                'requested_by_user_id' => $this->counselor->id,
                'min_votes_to_close' => 1,
                'votable_users' => [$this->counselor->id],
                'available_options' => [$option->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('agenda_points', 0);
    }

    public function test_closure_notification_flag_is_set_when_all_points_voted(): void
    {
        $this->markTestSkipped(
            'El listener CheckVotingStatus vive en Modules/AgendaConsejo/app/Listeners, '
            .'pero el EventServiceProvider del módulo no apunta su discoverEventsWithin() '
            .'al directorio de listeners del módulo, por lo que el evento VoteCast no se '
            .'engancha en testing. El flag closure_notification_sent nunca se actualiza. '
            .'Pendiente de revisión humana: el wiring del listener queda fuera del scope '
            .'de Fase 2 (solo tests, no cambios de aplicación).'
        );
    }
}
