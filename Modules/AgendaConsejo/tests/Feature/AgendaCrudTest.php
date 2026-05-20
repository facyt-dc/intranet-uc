<?php

namespace Modules\AgendaConsejo\Tests\Feature;

use App\Models\User;
use Modules\AgendaConsejo\Models\Agenda;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgendaCrudTest extends TestCase
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

    /** @test */
    public function test_director_can_list_agendas(): void
    {
        $response = $this->actingAs($this->director)
            ->get(route('agendas.index'));

        $response->assertOk();
    }

    /** @test */
    public function test_director_can_create_agenda(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('agendas.store'), [
                'name' => 'Consejo Ordinario de Prueba',
                'date' => now()->addDays(7)->toDateString(),
                'participants' => [$this->counselor->id],
            ]);

        $response->assertRedirect(route('agendas.show', Agenda::first()));
        $this->assertDatabaseHas('agendas', [
            'name' => 'Consejo Ordinario de Prueba',
            'director_id' => $this->director->id,
        ]);
    }

    /** @test */
    public function test_counselor_cannot_create_agenda(): void
    {
        $response = $this->actingAs($this->counselor)
            ->post(route('agendas.store'), [
                'name' => 'Intento no autorizado',
                'date' => now()->addDays(3)->toDateString(),
                'participants' => [$this->counselor->id],
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function test_director_can_update_agenda(): void
    {
        $agenda = Agenda::create([
            'name'        => 'Consejo Inicial',
            'date'        => now()->addDays(5)->toDateString(),
            'director_id' => $this->director->id,
            'status'      => 'Programado',
        ]);
        $agenda->participants()->sync([$this->counselor->id]);

        $response = $this->actingAs($this->director)
            ->put(route('agendas.update', $agenda), [
                'name'         => 'Consejo Actualizado',
                'date'         => now()->addDays(10)->toDateString(),
                'participants' => [$this->counselor->id],
            ]);

        $response->assertRedirect(route('agendas.index'));
        $this->assertDatabaseHas('agendas', ['name' => 'Consejo Actualizado']);
    }

    /** @test */
    public function test_director_can_delete_agenda(): void
    {
        $agenda = Agenda::create([
            'name'        => 'Consejo a Eliminar',
            'date'        => now()->addDays(5)->toDateString(),
            'director_id' => $this->director->id,
            'status'      => 'Programado',
        ]);

        $response = $this->actingAs($this->director)
            ->delete(route('agendas.destroy', $agenda));

        $response->assertRedirect(route('agendas.index'));
        $this->assertDatabaseMissing('agendas', ['id' => $agenda->id]);
    }
}
