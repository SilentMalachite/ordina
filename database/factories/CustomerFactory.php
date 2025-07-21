<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['individual', 'company']);
        
        return [
            'name' => $type === 'company' 
                ? $this->faker->company() 
                : $this->faker->name(),
            'type' => $type,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'contact_person' => $type === 'company' ? $this->faker->name() : null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}