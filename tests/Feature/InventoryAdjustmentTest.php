<?php

namespace Tests\Feature;

use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        $role = Role::create(['name' => '一般スタッフ']);
        $permission = Permission::create(['name' => 'inventory-adjust']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->actingAs($user);
    }

    /**
     * @test
     */
    public function 在庫増加調整が正しく記録される()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // 実行
        $response = $this->post(route('inventory.adjustment.store'), [
            'product_id' => $product->id,
            'adjustment_type' => 'increase',
            'quantity' => 5,
            'reason' => '入荷による在庫追加',
        ]);

        // 検証
        $response->assertRedirect(route('inventory.adjustments'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 15, // 10 + 5 = 15
        ]);
        
        $this->assertDatabaseHas('inventory_adjustments', [
            'product_id' => $product->id,
            'adjustment_type' => 'increase',
            'quantity' => 5,
            'previous_quantity' => 10,
            'new_quantity' => 15,
            'reason' => '入荷による在庫追加',
        ]);
    }

    /**
     * @test
     */
    public function 在庫減少調整が正しく記録される()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // 実行
        $response = $this->post(route('inventory.adjustment.store'), [
            'product_id' => $product->id,
            'adjustment_type' => 'decrease',
            'quantity' => 3,
            'reason' => '破損による廃棄',
        ]);

        // 検証
        $response->assertRedirect(route('inventory.adjustments'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 7, // 10 - 3 = 7
        ]);
        
        $this->assertDatabaseHas('inventory_adjustments', [
            'product_id' => $product->id,
            'adjustment_type' => 'decrease',
            'quantity' => 3,
            'previous_quantity' => 10,
            'new_quantity' => 7,
            'reason' => '破損による廃棄',
        ]);
    }

    /**
     * @test
     */
    public function 在庫不足の場合は減少調整できない()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 2]);

        // 実行
        $response = $this->post(route('inventory.adjustment.store'), [
            'product_id' => $product->id,
            'adjustment_type' => 'decrease',
            'quantity' => 5, // 在庫数を超える
            'reason' => '破損による廃棄',
        ]);

        // 検証
        $response->assertRedirect();
        $response->assertSessionHasErrors();
        
        // 在庫数が変わっていないことを確認
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 2,
        ]);
        
        // 調整記録が作成されていないことを確認
        $this->assertDatabaseMissing('inventory_adjustments', [
            'product_id' => $product->id,
        ]);
    }

    /**
     * @test
     */
    public function 一括在庫調整が正しく処理される()
    {
        // 準備
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 20]);

        // 実行
        $response = $this->post(route('inventory.bulk-adjustment.store'), [
            'adjustments' => [
                [
                    'product_id' => $product1->id,
                    'adjustment_type' => 'increase',
                    'quantity' => 5,
                ],
                [
                    'product_id' => $product2->id,
                    'adjustment_type' => 'decrease',
                    'quantity' => 3,
                ],
            ],
            'reason' => '月次棚卸調整',
        ]);

        // 検証
        $response->assertRedirect(route('inventory.adjustments'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'id' => $product1->id,
            'stock_quantity' => 15, // 10 + 5 = 15
        ]);
        
        $this->assertDatabaseHas('products', [
            'id' => $product2->id,
            'stock_quantity' => 17, // 20 - 3 = 17
        ]);
        
        // 両方の調整記録が作成されていることを確認
        $this->assertEquals(2, InventoryAdjustment::where('reason', '月次棚卸調整')->count());
    }
}
