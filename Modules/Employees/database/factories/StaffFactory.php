<?php

namespace Modules\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Employees\Models\Staff;
use Modules\Employees\Models\StaffType;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'places_number' => $this->faker->numberBetween(1, 20),
            'type' => StaffTypeFactory::new()->create()->id,
        ];
    }
}
