<?php

namespace Modules\AgendaConsejo\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgendaValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $director;
    protected User $counselor;

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
    }

    public function test_agenda_name_is_required(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('agendas.store'), [
                'date' => now()->addDays(7)->toDateString(),
                'participants' => [$this->counselor->id],
            ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('agendas', 0);
    }

    public function test_agenda_name_max_length(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('agendas.store'), [
                'name' => str_repeat('a', 256),
                'date' => now()->addDays(7)->toDateString(),
                'participants' => [$this->counselor->id],
            ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('agendas', 0);
    }

    public function test_agenda_date_is_required(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('agendas.store'), [
                'name' => 'Consejo sin fecha',
                'participants' => [$this->counselor->id],
            ]);

        $response->assertSessionHasErrors(['date']);
        $this->assertDatabaseCount('agendas', 0);
    }

    public function test_agenda_date_must_be_valid_format(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('agendas.store'), [
                'name' => 'Consejo fecha inválida',
                'date' => 'no-es-una-fecha',
                'participants' => [$this->counselor->id],
            ]);

        $response->assertSessionHasErrors(['date']);
        $this->assertDatabaseCount('agendas', 0);
    }

    public function test_agenda_date_must_be_today_or_future(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('agendas.store'), [
                'name' => 'Consejo en el pasado',
                'date' => now()->subDays(1)->toDateString(),
                'participants' => [$this->counselor->id],
            ]);

        $response->assertSessionHasErrors(['date']);
        $this->assertDatabaseCount('agendas', 0);
    }

    public function test_agenda_requires_at_least_one_participant(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('agendas.store'), [
                'name' => 'Consejo sin participantes',
                'date' => now()->addDays(7)->toDateString(),
                'participants' => [],
            ]);

        $response->assertSessionHasErrors(['participants']);
        $this->assertDatabaseCount('agendas', 0);
    }

    public function test_agenda_participants_must_be_counselors(): void
    {
        $nonCounselor = User::factory()->create();

        $response = $this->actingAs($this->director)
            ->post(route('agendas.store'), [
                'name' => 'Consejo con no-counselor',
                'date' => now()->addDays(7)->toDateString(),
                'participants' => [$nonCounselor->id],
            ]);

        $response->assertSessionHasErrors(['participants.0']);
        $this->assertDatabaseCount('agendas', 0);
    }
}
