<?php

namespace Modules\Thesis\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Thesis\Models\StudentStatus;

class ThesisStudentFactory extends Factory
{
    protected $model = \Modules\Thesis\Models\ThesisStudent::class;

    public function definition(): array
    {
        return [
            'id_uc' => $this->faker->unique()->numerify('########'),
            'name' => $this->faker->name(),
            'ci' => $this->faker->unique()->numerify('########'),
            'email' => $this->faker->unique()->safeEmail(),
            'status_id' => StudentStatus::factory(),
        ];
    }
}