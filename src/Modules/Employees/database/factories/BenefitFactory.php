<?php

namespace Modules\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Employees\Models\Benefit;
use Modules\Employees\Models\TimeUnit;

class BenefitFactory extends Factory
{
    protected $model = Benefit::class;

    public function definition(): array
    {
        $timeUnit = TimeUnitFactory::new()->create();
        $timeUnit2 = TimeUnitFactory::new()->create();

        return [
            'name' => $this->faker->unique()->words(2, true),
            'time_between_use' => $this->faker->numberBetween(1, 30),
            'time_between_use_unit' => $timeUnit->id,
            'time_lapse' => $this->faker->numberBetween(1, 30),
            'time_lapse_unit' => $timeUnit2->id,
        ];
    }
}
