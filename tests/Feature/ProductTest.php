<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_products_index()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        Product::factory()->count(5)->create();
        
        $response = $this->actingAs($user)->get('/products');
        $response->assertStatus(200);
        $response->assertViewHas('products');
    }

    public function test_user_can_create_product()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        
        $response = $this->actingAs($user)->get('/products/create');
        $response->assertStatus(200);
        
        $productData = [
            'product_code' => 'TEST001',
            'name' => 'テスト商品',
            'stock_quantity' => 100,
            'unit_price' => 1000,
            'selling_price' => 1500,
            'description' => 'テスト用商品です'
        ];
        
        $response = $this->actingAs($user)->post('/products', $productData);
        
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'product_code' => 'TEST001',
            'name' => 'テスト商品',
            'stock_quantity' => 100,
            'unit_price' => 1000,
            'selling_price' => 1500
        ]);
    }

    public function test_user_can_update_product()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        $product = Product::factory()->create([
            'product_code' => 'TEST001',
            'name' => 'テスト商品',
            'stock_quantity' => 100
        ]);
        
        $response = $this->actingAs($user)->get("/products/{$product->id}/edit");
        $response->assertStatus(200);
        
        $updateData = [
            'product_code' => 'TEST001',
            'name' => '更新されたテスト商品',
            'stock_quantity' => 150,
            'unit_price' => 1200,
            'selling_price' => 1800,
            'description' => '更新されたテスト用商品です'
        ];
        
        $response = $this->actingAs($user)->put("/products/{$product->id}", $updateData);
        
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => '更新されたテスト商品',
            'stock_quantity' => 150
        ]);
    }

    public function test_user_can_delete_product_without_transactions()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        $product = Product::factory()->create();
        
        $response = $this->actingAs($user)->delete("/products/{$product->id}");
        
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_user_can_delete_product_with_transactions()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        Transaction::factory()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'user_id' => $user->id
        ]);
        
        $response = $this->actingAs($user)->delete("/products/{$product->id}");
        
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_product_search_functionality()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        Product::factory()->create(['product_code' => 'ABC123', 'name' => '商品A']);
        Product::factory()->create(['product_code' => 'DEF456', 'name' => '商品B']);
        
        $response = $this->actingAs($user)->get('/products/search?q=ABC');
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals('ABC123', $data[0]['product_code']);
    }

    public function test_product_validation_rules()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        
        $invalidData = [
            'product_code' => '', // Required
            'name' => '', // Required
            'stock_quantity' => -1, // Must be >= 0
            'unit_price' => -100, // Must be >= 0
            'selling_price' => -200 // Must be >= 0
        ];
        
        $response = $this->actingAs($user)->post('/products', $invalidData);
        $response->assertSessionHasErrors(['product_code', 'name', 'stock_quantity', 'unit_price', 'selling_price']);
    }

    public function test_product_code_uniqueness()
    {
        $user = $this->createUserWithRole('一般スタッフ');
        Product::factory()->create(['product_code' => 'UNIQUE001']);
        
        $duplicateData = [
            'product_code' => 'UNIQUE001', // Duplicate
            'name' => '重複商品',
            'stock_quantity' => 100,
            'unit_price' => 1000,
            'selling_price' => 1500
        ];
        
        $response = $this->actingAs($user)->post('/products', $duplicateData);
        $response->assertSessionHasErrors(['product_code']);
    }
}