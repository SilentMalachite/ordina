<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryAdjustment>
 */
class InventoryAdjustmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $adjustmentType = $this->faker->randomElement(['increase', 'decrease']);
        $quantity = $this->faker->numberBetween(1, 20);
        $previousQuantity = $this->faker->numberBetween(10, 100);
        
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'adjustment_type' => $adjustmentType,
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $adjustmentType === 'increase' 
                ? $previousQuantity + $quantity 
                : $previousQuantity - $quantity,
            'reason' => $this->faker->randomElement(['棚卸し', '返品', '破損', '仕入れ', '調整']),
        ];
    }
}