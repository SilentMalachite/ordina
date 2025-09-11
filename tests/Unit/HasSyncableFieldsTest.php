<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use App\Traits\HasSyncableFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HasSyncableFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用テーブルを作成
        \Schema::create('test_syncable_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->uuid('uuid')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_dirty')->default(false);
            $table->timestamps();
        });
    }

    /**
     * テスト用のモデルクラス
     */
    private function createTestModel()
    {
        return new class extends Model {
            use HasSyncableFields;

            protected $table = 'test_syncable_models';
            protected $fillable = ['name', 'uuid', 'last_synced_at', 'is_dirty'];

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);

                // HasSyncableFieldsトレイトの初期化メソッドを呼び出し
                if (method_exists($this, 'initializeHasSyncableFields')) {
                    $this->initializeHasSyncableFields();
                }
            }
        };
    }

    /**
     * 新規作成時にUUIDが自動生成されることをテスト
     */
    public function test_uuid_is_generated_on_creation()
    {
        $model = $this->createTestModel();
        $model->fill(['name' => 'Test Product']);
        $model->save();

        $this->assertNotNull($model->uuid);
        $this->assertTrue(Str::isUuid($model->uuid));
    }

    /**
     * 新規作成時にis_dirtyがtrueに設定されることをテスト
     */
    public function test_is_dirty_set_to_true_on_creation()
    {
        $model = $this->createTestModel();
        $model->fill(['name' => 'Test Product']);
        $model->save();

        $this->assertTrue($model->is_dirty);
    }

    /**
     * 更新時に同期フィールド以外が変更された場合is_dirtyがtrueになることをテスト
     */
    public function test_is_dirty_set_when_non_sync_fields_updated()
    {
        $model = $this->createTestModel();
        $model->fill(['name' => 'Test Product']);
        $model->save();

        // 同期完了としてマーク
        $model->markAsSynced();
        $freshModel = $model->fresh();
        $this->assertEquals(0, $freshModel->is_dirty, "Expected is_dirty to be 0, but got: " . $freshModel->is_dirty);

        // 同期フィールド以外を変更
        $model->name = 'Updated Product';
        $model->save();
        $this->assertEquals(1, $model->fresh()->is_dirty);
    }

    /**
     * 同期フィールドのみが変更された場合is_dirtyが変更されないことをテスト
     */
    public function test_is_dirty_not_set_when_only_sync_fields_updated()
    {
        $model = $this->createTestModel();
        $model->fill(['name' => 'Test Product']);
        $model->save();

        // 同期完了としてマーク
        $model->markAsSynced();
        $this->assertFalse($model->fresh()->is_dirty);

        // 同期フィールドのみを変更
        $model->update(['last_synced_at' => now()]);
        $this->assertFalse($model->fresh()->is_dirty);
    }

    /**
     * markAsSyncedメソッドが正しく動作することをテスト
     */
    public function test_mark_as_synced()
    {
        $model = $this->createTestModel();
        $model->fill(['name' => 'Test Product']);
        $model->save();

        $this->assertTrue($model->is_dirty);
        $this->assertNull($model->last_synced_at);

        $model->markAsSynced();

        $this->assertFalse($model->fresh()->is_dirty);
        $this->assertNotNull($model->fresh()->last_synced_at);
    }

    /**
     * 未同期レコードのスコープが正しく動作することをテスト
     */
    public function test_unsynced_records_scope()
    {
        $modelClass = $this->createTestModel();

        // 同期済みレコード
        $syncedModel = $modelClass->newInstance();
        $syncedModel->fill(['name' => 'Synced Product']);
        $syncedModel->save();
        $syncedModel->markAsSynced();

        // 未同期レコード
        $unsyncedModel = $modelClass->newInstance();
        $unsyncedModel->fill(['name' => 'Unsynced Product']);
        $unsyncedModel->save();

        $unsyncedRecords = $modelClass::unsyncedRecords()->get();

        $this->assertCount(1, $unsyncedRecords);
        $this->assertEquals('Unsynced Product', $unsyncedRecords->first()->name);
    }

    /**
     * 指定日時以降に更新されたレコードのスコープが正しく動作することをテスト
     */
    public function test_updated_since_scope()
    {
        $modelClass = $this->createTestModel();

        // 古いレコード
        $oldModel = $modelClass->newInstance();
        $oldModel->fill(['name' => 'Old Product']);
        $oldModel->save();
        $oldModel->update(['updated_at' => now()->subDays(2)]);

        // 新しいレコード
        $newModel = $modelClass->newInstance();
        $newModel->fill(['name' => 'New Product']);
        $newModel->save();

        $recentRecords = $modelClass::updatedSince(now()->subDay())->get();

        $this->assertCount(1, $recentRecords);
        $this->assertEquals('New Product', $recentRecords->first()->name);
    }

    /**
     * ProductモデルでHasSyncableFieldsが正しく動作することをテスト
     */
    public function test_product_model_has_syncable_fields()
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->uuid);
        $this->assertTrue($product->is_dirty);
        $this->assertTrue(Str::isUuid($product->uuid));

        // fillableに同期フィールドが含まれていることを確認
        $this->assertContains('uuid', $product->getFillable());
        $this->assertContains('last_synced_at', $product->getFillable());
        $this->assertContains('is_dirty', $product->getFillable());
    }

    /**
     * TransactionモデルでHasSyncableFieldsが正しく動作することをテスト
     */
    public function test_transaction_model_has_syncable_fields()
    {
        $transaction = Transaction::factory()->create();

        $this->assertNotNull($transaction->uuid);
        $this->assertTrue($transaction->is_dirty);
        $this->assertTrue(Str::isUuid($transaction->uuid));

        // fillableに同期フィールドが含まれていることを確認
        $this->assertContains('uuid', $transaction->getFillable());
        $this->assertContains('last_synced_at', $transaction->getFillable());
        $this->assertContains('is_dirty', $transaction->getFillable());
    }
}
