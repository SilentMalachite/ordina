<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Events\LowStockDetected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        $role = Role::create(['name' => '一般スタッフ']);
        $permissions = [
            'transaction-create',
            'transaction-edit',
        ];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        $this->actingAs($user);
    }

    /**
     * @test
     */
    public function 商品販売で在庫数が減少し取引が記録される()
    {
        // 準備
        Event::fake([LowStockDetected::class]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $customer = Customer::factory()->create();

        // 実行
        $response = $this->post('/transactions', [
            'type' => 'sale',
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'quantity' => 3,
            'unit_price' => 1000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        // 検証
        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 7, // 10 - 3 = 7
        ]);
        
        $this->assertDatabaseHas('transactions', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'type' => 'sale',
            'quantity' => 3,
            'unit_price' => 1000,
            'total_amount' => 3000,
        ]);
    }

    /**
     * @test
     */
    public function 在庫不足の場合は販売できない()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 2]);
        $customer = Customer::factory()->create();

        // 実行
        $response = $this->post('/transactions', [
            'type' => 'sale',
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'quantity' => 5, // 在庫数を超える
            'unit_price' => 1000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        // 検証
        $response->assertRedirect();
        $response->assertSessionHasErrors();
        
        // 在庫数が変わっていないことを確認
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 2,
        ]);
        
        // 取引が記録されていないことを確認
        $this->assertDatabaseMissing('transactions', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * @test
     */
    public function 貸出商品の返却で在庫が戻る()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'rental',
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'quantity' => 2,
            'returned_at' => null,
        ]);

        // 実行
        $response = $this->post("/transactions/{$transaction->id}/return");

        // 検証
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 7, // 5 + 2 = 7
        ]);
        
        $this->assertNotNull($transaction->fresh()->returned_at);
    }

    /**
     * @test
     */
    public function 取引更新時の在庫数調整が正しく行われる()
    {
        // 準備
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'type' => 'sale',
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'quantity' => 3,
            'unit_price' => 1000,
        ]);
        
        // 在庫を減らす
        $product->decrement('stock_quantity', 3);

        // 実行（数量を3から5に変更）
        $response = $this->put("/transactions/{$transaction->id}", [
            'type' => 'sale',
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'quantity' => 5,
            'unit_price' => 1000,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        // 検証
        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 5, // 10 - 5 = 5
        ]);
        
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'quantity' => 5,
        ]);
    }
}
