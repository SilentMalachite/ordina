<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_transactions_index()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        Transaction::factory()->count(5)->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id
        ]);
        
        $response = $this->actingAs($user)->get('/transactions');
        $response->assertStatus(200);
        $response->assertViewHas('transactions');
    }

    public function test_user_can_create_sale_transaction()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 100,
            'unit_price' => 1000,
            'selling_price' => 1500
        ]);
        
        $response = $this->actingAs($user)->get('/transactions/create');
        $response->assertStatus(200);
        
        $transactionData = [
            'type' => 'sale',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 1500,
            'transaction_date' => now()->format('Y-m-d'),
            'notes' => 'テスト売上'
        ];
        
        $response = $this->actingAs($user)->post('/transactions', $transactionData);
        
        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'type' => 'sale',
            'customer_id' => $customer->id,
            'quantity' => 5,
            'total_amount' => 7500
        ]);
        
        // Check stock was decreased
        $product->refresh();
        $this->assertEquals(95, $product->stock_quantity);
    }

    public function test_user_can_create_rental_transaction()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 50,
            'unit_price' => 500
        ]);
        
        $transactionData = [
            'type' => 'rental',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 500,
            'transaction_date' => now()->format('Y-m-d'),
            'expected_return_date' => now()->addDays(7)->format('Y-m-d'),
            'notes' => 'テスト貸出'
        ];
        
        $response = $this->actingAs($user)->post('/transactions', $transactionData);
        
        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'type' => 'rental',
            'customer_id' => $customer->id,
            'quantity' => 3,
            'total_amount' => 1500
        ]);
        
        // Check stock was decreased
        $product->refresh();
        $this->assertEquals(47, $product->stock_quantity);
    }

    public function test_user_can_return_rental_item()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 50]);
        
        $rental = Transaction::factory()->create([
            'type' => 'rental',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 5,
            'returned_at' => null
        ]);
        
        $response = $this->actingAs($user)->post("/transactions/{$rental->id}/return");
        
        $response->assertRedirect();
        
        $rental->refresh();
        $this->assertNotNull($rental->returned_at);
        
        // Check stock was restored
        $product->refresh();
        $this->assertEquals(55, $product->stock_quantity);
    }
}