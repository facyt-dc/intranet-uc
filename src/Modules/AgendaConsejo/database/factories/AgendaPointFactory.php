<?php

namespace Modules\AgendaConsejo\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\AgendaConsejo\Models\Agenda;
use Modules\AgendaConsejo\Models\AgendaPoint;

class AgendaPointFactory extends Factory
{
    protected $model = AgendaPoint::class;

    public function definition(): array
    {
        return [
            'agenda_id' => Agenda::factory(),
            'description' => $this->faker->paragraph(),
            'requested_by_user_id' => User::factory(),
            'status' => 'Abierto para Votación',
            'min_votes_to_close' => 1,
            'order' => 0,
        ];
    }
}
