<?php

namespace Modules\Thesis\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ThesisTeacherFactory extends Factory
{
    protected $model = \Modules\Thesis\Models\ThesisTeacher::class;

    public function definition(): array
    {
        return [
            'id_uc' => $this->faker->unique()->numerify('########'),
            'name' => $this->faker->name(),
            'ci' => $this->faker->unique()->numerify('########'),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}