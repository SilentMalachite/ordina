<?php

namespace Tests\Unit;

use App\Http\Requests\SyncDataRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SyncDataValidationTest extends TestCase
{
    /**
     * 有効な同期データが検証を通ることをテスト
     */
    public function test_valid_sync_data_passes_validation()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $validData = [
            'data' => [
                [
                    'table' => 'products',
                    'records' => [
                        [
                            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                            'product_code' => 'TEST001',
                            'name' => 'Test Product',
                            'stock_quantity' => 10,
                            'unit_price' => 100.00,
                            'selling_price' => 150.00,
                            'description' => 'Test description',
                            'updated_at' => now()->toISOString(),
                        ]
                    ]
                ]
            ]
        ];

        $request = new SyncDataRequest();
        $validator = Validator::make($validData, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * 無効なテーブル名が拒否されることをテスト
     */
    public function test_invalid_table_name_fails_validation()
    {
        $invalidData = [
            'data' => [
                [
                    'table' => 'invalid_table',
                    'records' => [
                        [
                            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                            'name' => 'Test Record',
                        ]
                    ]
                ]
            ]
        ];

        $request = new SyncDataRequest();
        $validator = Validator::make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('data.0.table', $validator->errors()->toArray());
    }

    /**
     * 必須フィールドが欠けている場合の検証テスト
     */
    public function test_missing_required_fields_fail_validation()
    {
        $invalidData = [
            'data' => [
                [
                    'table' => 'products',
                    'records' => [
                        [
                            // uuidが欠けている
                            'product_code' => 'TEST001',
                            'name' => 'Test Product',
                        ]
                    ]
                ]
            ]
        ];

        $request = new SyncDataRequest();
        $validator = Validator::make($invalidData, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('data.0.records.0.uuid', $validator->errors()->toArray());
    }
}
