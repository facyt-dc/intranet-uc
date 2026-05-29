<?php

namespace Modules\Maintenance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Maintenance\Models\MaintenanceStage;

class MaintenanceStageFactory extends Factory
{
    protected $model = MaintenanceStage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'sequence' => $this->faker->numberBetween(1, 10),
            'is_final_stage' => false,
        ];
    }

    public function finalStage(): static
    {
        return $this->state(['is_final_stage' => true]);
    }
}
