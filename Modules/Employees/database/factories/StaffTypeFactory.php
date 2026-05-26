<?php

namespace Modules\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Employees\Models\StaffType;

class StaffTypeFactory extends Factory
{
    protected $model = StaffType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
        ];
    }
}
