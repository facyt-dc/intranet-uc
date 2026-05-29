<?php

namespace Modules\Maintenance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Maintenance\Models\Equipment;

class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'brand' => $this->faker->company(),
            'model' => $this->faker->bothify('Model-##??'),
        ];
    }
}
