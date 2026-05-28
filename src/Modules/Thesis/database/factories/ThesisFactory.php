<?php

namespace Modules\Thesis\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ThesisFactory extends Factory
{
    protected $model = \Modules\Thesis\Models\Thesis::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->sentence(4),
            'date' => $this->faker->date(),
            'is_active' => true,
        ];
    }
}