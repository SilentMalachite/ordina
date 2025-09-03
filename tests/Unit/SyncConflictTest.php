<?php

namespace Tests\Unit;

use App\Models\SyncConflict;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncConflictTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SyncConflictの作成と基本機能のテスト
     */
    public function test_sync_conflict_creation()
    {
        $user = User::factory()->create();

        $conflictData = [
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => ['name' => 'Local Product', 'price' => 100],
            'server_data' => ['name' => 'Server Product', 'price' => 150],
            'conflict_reason' => 'Price differs between local and server',
            'user_id' => $user->id,
        ];

        $conflict = SyncConflict::create($conflictData);

        $this->assertInstanceOf(SyncConflict::class, $conflict);
        $this->assertEquals('products', $conflict->table_name);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $conflict->record_uuid);
        $this->assertEquals(['name' => 'Local Product', 'price' => 100], $conflict->local_data);
        $this->assertEquals(['name' => 'Server Product', 'price' => 150], $conflict->server_data);
        $this->assertEquals('pending', $conflict->status);
        $this->assertNull($conflict->resolved_at);
    }

    /**
     * 保留中の競合を取得するスコープのテスト
     */
    public function test_pending_scope()
    {
        $user = User::factory()->create();

        // 保留中の競合
        $pendingConflict = SyncConflict::create([
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => ['name' => 'Local Product'],
            'server_data' => ['name' => 'Server Product'],
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        // 解決済みの競合
        $resolvedConflict = SyncConflict::create([
            'table_name' => 'customers',
            'record_uuid' => '456e7890-e89b-12d3-a456-426614174001',
            'local_data' => ['name' => 'Local Customer'],
            'server_data' => ['name' => 'Server Customer'],
            'status' => 'resolved',
            'user_id' => $user->id,
        ]);

        $pendingConflicts = SyncConflict::pending()->get();

        $this->assertCount(1, $pendingConflicts);
        $this->assertEquals($pendingConflict->id, $pendingConflicts->first()->id);
    }

    /**
     * 解決済みの競合を取得するスコープのテスト
     */
    public function test_resolved_scope()
    {
        $user = User::factory()->create();

        // 保留中の競合
        SyncConflict::create([
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => ['name' => 'Local Product'],
            'server_data' => ['name' => 'Server Product'],
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        // 解決済みの競合
        $resolvedConflict = SyncConflict::create([
            'table_name' => 'customers',
            'record_uuid' => '456e7890-e89b-12d3-a456-426614174001',
            'local_data' => ['name' => 'Local Customer'],
            'server_data' => ['name' => 'Server Customer'],
            'status' => 'resolved',
            'user_id' => $user->id,
        ]);

        $resolvedConflicts = SyncConflict::resolved()->get();

        $this->assertCount(1, $resolvedConflicts);
        $this->assertEquals($resolvedConflict->id, $resolvedConflicts->first()->id);
    }

    /**
     * 競合を解決するメソッドのテスト
     */
    public function test_resolve_method()
    {
        $user = User::factory()->create();
        $resolver = User::factory()->create();

        $conflict = SyncConflict::create([
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => ['name' => 'Local Product'],
            'server_data' => ['name' => 'Server Product'],
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        $result = $conflict->resolve('local_wins', $resolver->id);

        $this->assertTrue($result);
        $this->assertEquals('resolved', $conflict->fresh()->status);
        $this->assertEquals('local_wins', $conflict->fresh()->resolution_strategy);
        $this->assertEquals($resolver->id, $conflict->fresh()->user_id);
        $this->assertNotNull($conflict->fresh()->resolved_at);
    }

    /**
     * 競合を無視するメソッドのテスト
     */
    public function test_ignore_method()
    {
        $user = User::factory()->create();
        $resolver = User::factory()->create();

        $conflict = SyncConflict::create([
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => ['name' => 'Local Product'],
            'server_data' => ['name' => 'Server Product'],
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        $result = $conflict->ignore($resolver->id);

        $this->assertTrue($result);
        $this->assertEquals('ignored', $conflict->fresh()->status);
        $this->assertEquals($resolver->id, $conflict->fresh()->user_id);
        $this->assertNotNull($conflict->fresh()->resolved_at);
    }

    /**
     * 競合データの差分を取得するメソッドのテスト
     */
    public function test_get_differences_method()
    {
        $user = User::factory()->create();

        $conflict = SyncConflict::create([
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => [
                'name' => 'Local Product',
                'price' => 100,
                'description' => 'Local description'
            ],
            'server_data' => [
                'name' => 'Server Product',
                'price' => 150,
                'category' => 'Electronics'
            ],
            'user_id' => $user->id,
        ]);

        $differences = $conflict->getDifferences();

        // nameの差分を確認
        $this->assertArrayHasKey('name', $differences);
        $this->assertEquals('Local Product', $differences['name']['local']);
        $this->assertEquals('Server Product', $differences['name']['server']);

        // priceの差分を確認
        $this->assertArrayHasKey('price', $differences);
        $this->assertEquals(100, $differences['price']['local']);
        $this->assertEquals(150, $differences['price']['server']);

        // descriptionはローカルにのみ存在
        $this->assertArrayHasKey('description', $differences);
        $this->assertEquals('Local description', $differences['description']['local']);
        $this->assertNull($differences['description']['server']);

        // categoryはサーバーにのみ存在
        $this->assertArrayHasKey('category', $differences);
        $this->assertNull($differences['category']['local']);
        $this->assertEquals('Electronics', $differences['category']['server']);
    }

    /**
     * 競合解決者とのリレーションのテスト
     */
    public function test_resolver_relationship()
    {
        $user = User::factory()->create();
        $resolver = User::factory()->create();

        $conflict = SyncConflict::create([
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => ['name' => 'Local Product'],
            'server_data' => ['name' => 'Server Product'],
            'user_id' => $user->id,
        ]);

        $conflict->resolve('local_wins', $resolver->id);

        $this->assertInstanceOf(User::class, $conflict->fresh()->resolver);
        $this->assertEquals($resolver->id, $conflict->fresh()->resolver->id);
    }

    /**
     * JSONデータのキャストが正しく動作することをテスト
     */
    public function test_json_data_casting()
    {
        $user = User::factory()->create();

        $localData = ['name' => 'Test Product', 'price' => 100];
        $serverData = ['name' => 'Server Product', 'price' => 150];

        $conflict = SyncConflict::create([
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => $localData,
            'server_data' => $serverData,
            'user_id' => $user->id,
        ]);

        // データベースから再取得してJSONデコードが正しく動作することを確認
        $freshConflict = $conflict->fresh();

        $this->assertIsArray($freshConflict->local_data);
        $this->assertIsArray($freshConflict->server_data);
        $this->assertEquals($localData, $freshConflict->local_data);
        $this->assertEquals($serverData, $freshConflict->server_data);
    }

    /**
     * datetimeのキャストが正しく動作することをテスト
     */
    public function test_datetime_casting()
    {
        $user = User::factory()->create();

        $conflict = SyncConflict::create([
            'table_name' => 'products',
            'record_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'local_data' => ['name' => 'Test Product'],
            'server_data' => ['name' => 'Server Product'],
            'user_id' => $user->id,
        ]);

        $this->assertNull($conflict->resolved_at);

        $conflict->resolve('local_wins', $user->id);

        $this->assertInstanceOf(\Carbon\Carbon::class, $conflict->fresh()->resolved_at);
    }
}
