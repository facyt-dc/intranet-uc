<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemCategory;
use Modules\Inventory\Models\ItemStatus;
use Modules\Inventory\Models\Location;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'serial_number' => $this->faker->unique()->bothify('SN-#####-???'),
            'description' => $this->faker->sentence(),
            'acquisition_date' => $this->faker->date(),
            'cost' => $this->faker->randomFloat(2, 10, 5000),
            'quantity' => $this->faker->numberBetween(1, 20),
            'category_id' => ItemCategory::factory(),
            'current_status_id' => ItemStatus::factory(),
            'current_location_id' => Location::factory(),
        ];
    }
}
