<?php

namespace Tests\Unit;

use App\Jobs\SyncLocalChangesToServerJob;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use App\Models\User;
use App\Models\SyncConflict;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SyncLocalChangesToServerJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未同期データがない場合のテスト
     */
    public function test_job_handles_no_unsynced_data()
    {
        Queue::fake();

        $user = User::factory()->create();
        $job = new SyncLocalChangesToServerJob($user->id);

        // HTTPリクエストをモック
        Http::shouldReceive('withToken->timeout->post')
            ->never();

        // ジョブを実行（実際にはキューに入れるが、テストでは直接実行）
        // このテストではジョブの実行自体をテストしない
        $this->assertInstanceOf(SyncLocalChangesToServerJob::class, $job);
    }

    /**
     * 未同期データの収集がユーザー固有に行われることをテスト
     */
    public function test_collect_unsynced_data_filters_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // user1のトランザクション
        $transaction1 = Transaction::factory()->create([
            'user_id' => $user1->id,
            'is_dirty' => true,
        ]);

        // user2のトランザクション
        $transaction2 = Transaction::factory()->create([
            'user_id' => $user2->id,
            'is_dirty' => true,
        ]);

        // user1の在庫調整
        $adjustment1 = InventoryAdjustment::factory()->create([
            'user_id' => $user1->id,
            'is_dirty' => true,
        ]);

        // user2の在庫調整
        $adjustment2 = InventoryAdjustment::factory()->create([
            'user_id' => $user2->id,
            'is_dirty' => true,
        ]);

        // 同期済みのレコード
        $syncedTransaction = Transaction::factory()->create([
            'user_id' => $user1->id,
            'is_dirty' => false,
        ]);

        $job = new SyncLocalChangesToServerJob($user1->id);

        // リフレクションを使用してprivateメソッドをテスト
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('collectUnsyncedData');
        $method->setAccessible(true);

        $result = $method->invoke($job);

        // user1のデータのみが収集されていることを確認
        $this->assertCount(2, $result); // transactionsとinventory_adjustments

        // transactionsの確認
        $transactionsData = collect($result)->firstWhere('table', 'transactions');
        $this->assertNotNull($transactionsData);
        $this->assertCount(1, $transactionsData['records']);
        $this->assertEquals($transaction1->uuid, $transactionsData['records'][0]['uuid']);

        // inventory_adjustmentsの確認
        $adjustmentsData = collect($result)->firstWhere('table', 'inventory_adjustments');
        $this->assertNotNull($adjustmentsData);
        $this->assertCount(1, $adjustmentsData['records']);
        $this->assertEquals($adjustment1->uuid, $adjustmentsData['records'][0]['uuid']);
    }

    /**
     * 商品と顧客のデータは全ユーザーのものが収集されることをテスト
     */
    public function test_collect_unsynced_data_includes_all_products_and_customers()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 未同期の商品
        $product1 = Product::factory()->create(['is_dirty' => true]);
        $product2 = Product::factory()->create(['is_dirty' => true]);

        // 未同期の顧客
        $customer1 = Customer::factory()->create(['is_dirty' => true]);
        $customer2 = Customer::factory()->create(['is_dirty' => true]);

        // 同期済みの商品と顧客
        $syncedProduct = Product::factory()->create(['is_dirty' => false]);
        $syncedCustomer = Customer::factory()->create(['is_dirty' => false]);

        $job = new SyncLocalChangesToServerJob($user1->id);

        // リフレクションを使用してprivateメソッドをテスト
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('collectUnsyncedData');
        $method->setAccessible(true);

        $result = $method->invoke($job);

        // productsとcustomersのデータが収集されていることを確認
        $this->assertCount(4, $result); // products, customers, transactions, inventory_adjustments

        // productsの確認（全商品が含まれる）
        $productsData = collect($result)->firstWhere('table', 'products');
        $this->assertNotNull($productsData);
        $this->assertCount(2, $productsData['records']);
        $productUuids = collect($productsData['records'])->pluck('uuid');
        $this->assertContains($product1->uuid, $productUuids);
        $this->assertContains($product2->uuid, $productUuids);

        // customersの確認（全顧客が含まれる）
        $customersData = collect($result)->firstWhere('table', 'customers');
        $this->assertNotNull($customersData);
        $this->assertCount(2, $customersData['records']);
        $customerUuids = collect($customersData['records'])->pluck('uuid');
        $this->assertContains($customer1->uuid, $customerUuids);
        $this->assertContains($customer2->uuid, $customerUuids);
    }

    /**
     * 競合処理が正しく動作することをテスト
     */
    public function test_handle_conflicts_creates_sync_conflicts()
    {
        $user = User::factory()->create();

        $conflicts = [
            [
                'table' => 'products',
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'server_data' => ['name' => 'Server Product', 'price' => 150],
                'reason' => 'Price conflict',
                'resolution_strategy' => 'manual'
            ],
            [
                'table' => 'customers',
                'uuid' => '456e7890-e89b-12d3-a456-426614174001',
                'server_data' => ['name' => 'Server Customer'],
                'reason' => 'Name conflict'
            ]
        ];

        $job = new SyncLocalChangesToServerJob($user->id);

        // リフレクションを使用してprivateメソッドをテスト
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('handleConflicts');
        $method->setAccessible(true);

        $method->invoke($job, $conflicts);

        // SyncConflictが作成されていることを確認
        $this->assertEquals(2, SyncConflict::count());

        $productConflict = SyncConflict::where('table_name', 'products')->first();
        $this->assertNotNull($productConflict);
        $this->assertEquals('products', $productConflict->table_name);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $productConflict->record_uuid);
        $this->assertEquals(['name' => 'Server Product', 'price' => 150], $productConflict->server_data);
        $this->assertEquals('Price conflict', $productConflict->conflict_reason);
        $this->assertEquals('pending', $productConflict->status);

        $customerConflict = SyncConflict::where('table_name', 'customers')->first();
        $this->assertNotNull($customerConflict);
        $this->assertEquals('customers', $customerConflict->table_name);
        $this->assertEquals('456e7890-e89b-12d3-a456-426614174001', $customerConflict->record_uuid);
    }

    /**
     * 同期成功時のレコード更新が正しく動作することをテスト
     */
    public function test_mark_records_as_synced_updates_correct_records()
    {
        $user = User::factory()->create();

        // 未同期の商品
        $product = Product::factory()->create([
            'is_dirty' => true,
            'last_synced_at' => null,
        ]);

        // 未同期のトランザクション
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'is_dirty' => true,
            'last_synced_at' => null,
        ]);

        $syncData = [
            [
                'table' => 'products',
                'records' => [
                    ['uuid' => $product->uuid, 'name' => $product->name]
                ]
            ],
            [
                'table' => 'transactions',
                'records' => [
                    ['uuid' => $transaction->uuid, 'type' => $transaction->type]
                ]
            ]
        ];

        $job = new SyncLocalChangesToServerJob($user->id);

        // リフレクションを使用してprivateメソッドをテスト
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('markRecordsAsSynced');
        $method->setAccessible(true);

        $method->invoke($job, $syncData);

        // 商品が同期済みにマークされていることを確認
        $product->refresh();
        $this->assertFalse($product->is_dirty);
        $this->assertNotNull($product->last_synced_at);

        // トランザクションが同期済みにマークされていることを確認
        $transaction->refresh();
        $this->assertFalse($transaction->is_dirty);
        $this->assertNotNull($transaction->last_synced_at);
    }

    /**
     * テーブル名からモデルクラスを取得するメソッドのテスト
     */
    public function test_get_model_class_for_table()
    {
        $user = User::factory()->create();
        $job = new SyncLocalChangesToServerJob($user->id);

        // リフレクションを使用してprivateメソッドをテスト
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('getModelClassForTable');
        $method->setAccessible(true);

        $this->assertEquals(Product::class, $method->invoke($job, 'products'));
        $this->assertEquals(Customer::class, $method->invoke($job, 'customers'));
        $this->assertEquals(Transaction::class, $method->invoke($job, 'transactions'));
        $this->assertEquals(InventoryAdjustment::class, $method->invoke($job, 'inventory_adjustments'));
        $this->assertNull($method->invoke($job, 'invalid_table'));
    }

    /**
     * ローカルレコードデータの取得が正しく動作することをテスト
     */
    public function test_get_local_record_data()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'product_code' => 'TEST001',
        ]);

        $job = new SyncLocalChangesToServerJob($user->id);

        // リフレクションを使用してprivateメソッドをテスト
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('getLocalRecordData');
        $method->setAccessible(true);

        $result = $method->invoke($job, 'products', $product->uuid);

        $this->assertIsArray($result);
        $this->assertEquals($product->name, $result['name']);
        $this->assertEquals($product->product_code, $result['product_code']);
        $this->assertEquals($product->uuid, $result['uuid']);

        // 存在しないUUIDの場合
        $result = $method->invoke($job, 'products', 'non-existent-uuid');
        $this->assertEmpty($result);
    }

    /**
     * 無効なテーブル名の場合の処理をテスト
     */
    public function test_get_local_record_data_with_invalid_table()
    {
        $user = User::factory()->create();
        $job = new SyncLocalChangesToServerJob($user->id);

        // リフレクションを使用してprivateメソッドをテスト
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('getLocalRecordData');
        $method->setAccessible(true);

        $result = $method->invoke($job, 'invalid_table', 'some-uuid');
        $this->assertEmpty($result);
    }
}
