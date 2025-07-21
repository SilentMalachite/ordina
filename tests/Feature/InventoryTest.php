<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\InventoryAdjustment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_inventory_index()
    {
        $user = User::factory()->create();
        Product::factory()->count(5)->create();
        
        $response = $this->actingAs($user)->get('/inventory');
        $response->assertStatus(200);
        $response->assertViewHas('products');
        $response->assertViewHas('lowStockCount');
    }

    public function test_user_can_create_inventory_adjustment()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        $response = $this->actingAs($user)->get('/inventory/adjustment/create');
        $response->assertStatus(200);
        
        $response = $this->actingAs($user)->post('/inventory/adjustment', [
            'product_id' => $product->id,
            'adjustment_type' => 'increase',
            'quantity' => 5,
            'reason' => 'テスト調整'
        ]);
        
        $response->assertRedirect(route('inventory.adjustments'));
        $this->assertDatabaseHas('inventory_adjustments', [
            'product_id' => $product->id,
            'quantity' => 5,
            'adjustment_type' => 'increase'
        ]);
        
        $product->refresh();
        $this->assertEquals(15, $product->stock_quantity);
    }

    public function test_user_can_view_adjustment_history()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        InventoryAdjustment::factory()->count(3)->create([
            'product_id' => $product->id,
            'user_id' => $user->id
        ]);
        
        $response = $this->actingAs($user)->get('/inventory/adjustments');
        $response->assertStatus(200);
        $response->assertViewHas('adjustments');
    }
}