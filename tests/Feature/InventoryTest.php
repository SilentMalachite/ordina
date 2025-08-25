<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\InventoryAdjustment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => '一般スタッフ']);
        $permissions = [
            'inventory-list',
            'inventory-adjust',
        ];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        $role->syncPermissions($permissions);

        $this->user = User::factory()->create();
        $this->user->assignRole('一般スタッフ');
    }

    public function test_authenticated_user_can_access_inventory_index()
    {
        Product::factory()->count(5)->create();
        
        $response = $this->actingAs($this->user)->get('/inventory');
        $response->assertStatus(200);
        $response->assertViewHas('products');
        $response->assertViewHas('lowStockCount');
    }

    public function test_user_can_create_inventory_adjustment()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        $response = $this->actingAs($this->user)->get('/inventory/adjustment/create');
        $response->assertStatus(200);
        
        $response = $this->actingAs($this->user)->post('/inventory/adjustment', [
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
        $product = Product::factory()->create();
        
        InventoryAdjustment::factory()->count(3)->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id
        ]);
        
        $response = $this->actingAs($this->user)->get('/inventory/adjustments');
        $response->assertStatus(200);
        $response->assertViewHas('adjustments');
    }
}