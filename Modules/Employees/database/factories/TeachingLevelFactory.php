<?php

namespace Modules\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Employees\Models\TeachingLevel;
use Modules\Employees\Models\TimeUnit;

class TeachingLevelFactory extends Factory
{
    protected $model = TeachingLevel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'time' => $this->faker->numberBetween(1, 24),
            'time_unit' => TimeUnitFactory::new()->create()->id,
            'previous_level' => null,
        ];
    }
}
