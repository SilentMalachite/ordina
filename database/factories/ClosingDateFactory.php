<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClosingDate>
 */
class ClosingDateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_of_month' => $this->faker->numberBetween(1, 28),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'updated_by' => null,
        ];
    }
}