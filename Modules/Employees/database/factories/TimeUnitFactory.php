<?php

namespace Modules\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Employees\Models\TimeUnit;

class TimeUnitFactory extends Factory
{
    protected $model = TimeUnit::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
