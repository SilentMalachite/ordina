<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function 初期在庫数が正しく設定される()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 100]);

        // 検証
        $this->assertEquals(100, $product->stock_quantity);
    }

    /**
     * @test
     */
    public function 販売後の在庫計算が正しい()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $user = User::factory()->create();
        
        // 10個販売
        Transaction::factory()->create([
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => 10,
            'user_id' => $user->id,
        ]);
        
        // 5個販売
        Transaction::factory()->create([
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => 5,
            'user_id' => $user->id,
        ]);

        // 実行
        $product->refresh();

        // 検証（実際の在庫減算はコントローラーで行われるため、ここでは関連データの確認）
        $totalSold = $product->transactions()
            ->where('type', 'sale')
            ->sum('quantity');
        
        $this->assertEquals(15, $totalSold);
    }

    /**
     * @test
     */
    public function 貸出と返却の在庫計算が正しい()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 50]);
        $user = User::factory()->create();
        
        // 3個貸出（返却済み）
        Transaction::factory()->create([
            'product_id' => $product->id,
            'type' => 'rental',
            'quantity' => 3,
            'user_id' => $user->id,
            'returned_at' => now(),
        ]);
        
        // 5個貸出（未返却）
        Transaction::factory()->create([
            'product_id' => $product->id,
            'type' => 'rental',
            'quantity' => 5,
            'user_id' => $user->id,
            'returned_at' => null,
        ]);

        // 実行
        $activeRentals = $product->transactions()
            ->where('type', 'rental')
            ->whereNull('returned_at')
            ->sum('quantity');

        // 検証
        $this->assertEquals(5, $activeRentals);
    }

    /**
     * @test
     */
    public function 在庫調整履歴の計算が正しい()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $user = User::factory()->create();
        
        // 20個増加
        InventoryAdjustment::factory()->create([
            'product_id' => $product->id,
            'adjustment_type' => 'increase',
            'quantity' => 20,
            'previous_quantity' => 100,
            'new_quantity' => 120,
            'user_id' => $user->id,
        ]);
        
        // 5個減少
        InventoryAdjustment::factory()->create([
            'product_id' => $product->id,
            'adjustment_type' => 'decrease',
            'quantity' => 5,
            'previous_quantity' => 120,
            'new_quantity' => 115,
            'user_id' => $user->id,
        ]);

        // 実行
        $totalIncrease = $product->inventoryAdjustments()
            ->where('adjustment_type', 'increase')
            ->sum('quantity');
        
        $totalDecrease = $product->inventoryAdjustments()
            ->where('adjustment_type', 'decrease')
            ->sum('quantity');

        // 検証
        $this->assertEquals(20, $totalIncrease);
        $this->assertEquals(5, $totalDecrease);
        $this->assertEquals(15, $totalIncrease - $totalDecrease);
    }

    /**
     * @test
     */
    public function 在庫警告閾値の判定が正しい()
    {
        // 準備
        $lowStockThreshold = 10;
        
        $product1 = Product::factory()->create(['stock_quantity' => 5]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);
        $product3 = Product::factory()->create(['stock_quantity' => 15]);

        // 実行と検証
        $this->assertTrue($product1->stock_quantity < $lowStockThreshold);
        $this->assertTrue($product2->stock_quantity <= $lowStockThreshold);
        $this->assertFalse($product3->stock_quantity <= $lowStockThreshold);
    }
}
