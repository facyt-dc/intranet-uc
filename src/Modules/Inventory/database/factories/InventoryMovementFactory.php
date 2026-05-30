<?php

namespace Modules\Inventory\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\MovementType;

class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'user_id' => User::factory(),
            'movement_type_id' => MovementType::factory(),
            'description' => $this->faker->sentence(),
            'details' => ['nota' => $this->faker->word()],
            'movement_date' => now(),
        ];
    }
}
