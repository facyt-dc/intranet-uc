<?php

namespace Modules\Maintenance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Maintenance\Models\MaintenanceRequest;
use Modules\Maintenance\Models\MaintenanceStage;
use App\Models\User;

class MaintenanceRequestFactory extends Factory
{
    protected $model = MaintenanceRequest::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['preventive', 'corrective']),
            'user_id' => User::factory(),
            'stage_id' => MaintenanceStage::factory(),
            'technician_id' => null,
            'equipment_id' => null,
            'is_archived' => false,
        ];
    }
}
