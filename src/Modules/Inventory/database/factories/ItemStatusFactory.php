<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\ItemStatus;

class ItemStatusFactory extends Factory
{
    protected $model = ItemStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'is_operational' => $this->faker->boolean(),
        ];
    }
}
