<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_code' => 'P' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->word() . '商品',
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'unit_price' => $this->faker->numberBetween(100, 10000),
            'selling_price' => $this->faker->numberBetween(200, 15000),
            'description' => $this->faker->sentence(),
        ];
    }
}