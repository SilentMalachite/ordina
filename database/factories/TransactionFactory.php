<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['sale', 'rental']);
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->numberBetween(100, 10000);
        $transactionDate = $this->faker->dateTimeBetween('-1 month', 'now');
        
        $data = [
            'type' => $type,
            'customer_id' => Customer::factory(),
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $quantity * $unitPrice,
            'transaction_date' => $transactionDate,
            'notes' => $this->faker->optional()->sentence(),
        ];
        
        if ($type === 'rental') {
            $data['expected_return_date'] = $this->faker->dateTimeBetween($transactionDate, '+1 month');
            $data['returned_at'] = $this->faker->optional(0.3)->dateTimeBetween($transactionDate, 'now');
        }
        
        return $data;
    }
    
    /**
     * Indicate that the transaction is a sale.
     */
    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sale',
            'expected_return_date' => null,
            'returned_at' => null,
        ]);
    }
    
    /**
     * Indicate that the transaction is a rental.
     */
    public function rental(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'rental',
            'expected_return_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }
    
    /**
     * Indicate that the rental has been returned.
     */
    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'rental',
            'returned_at' => now(),
        ]);
    }
    
    /**
     * Indicate that the rental is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'rental',
            'expected_return_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'returned_at' => null,
        ]);
    }
}