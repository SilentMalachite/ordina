<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Native\Laravel\Facades\Notification;

class SyncFromServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected ?string $lastSyncTimestamp;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, ?string $lastSyncTimestamp = null)
    {
        $this->userId = $userId;
        $this->lastSyncTimestamp = $lastSyncTimestamp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting server to local sync for user: ' . $this->userId);

        try {
            // サーバーのエンドポイント（環境設定から取得）
            $serverUrl = config('sync.server_url', 'https://api.ordina.example.com');
            $apiToken = config('sync.api_token');

            // サーバーから更新データを取得
            $response = Http::withToken($apiToken)
                ->timeout(30)
                ->get($serverUrl . '/api/sync/pull', [
                    'last_sync' => $this->lastSyncTimestamp,
                    'user_id' => $this->userId
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (empty($data['updates'])) {
                    Log::info('No updates from server');
                    return;
                }
                
                // トランザクション内で更新を適用
                DB::transaction(function() use ($data) {
                    $this->applyUpdates($data['updates']);
                });
                
                Log::info('Server sync completed successfully', [
                    'total_updates' => count($data['updates'] ?? [])
                ]);
                
                // 成功通知
                Notification::title('サーバー同期完了')
                    ->message('サーバーからの更新が適用されました')
                    ->show();
                    
            } else {
                throw new \Exception('Server returned error: ' . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error('Server sync failed: ' . $e->getMessage());
            
            // エラー通知
            Notification::title('サーバー同期エラー')
                ->message('サーバーからのデータ取得中にエラーが発生しました: ' . $e->getMessage())
                ->show();
                
            // ジョブを再試行
            $this->release(300); // 5分後に再試行
        }
    }

    /**
     * サーバーからの更新を適用
     */
    private function applyUpdates(array $updates): void
    {
        foreach ($updates as $update) {
            $table = $update['table'];
            $records = $update['records'];
            
            foreach ($records as $record) {
                $this->applyRecord($table, $record);
            }
        }
    }

    /**
     * 個別レコードの適用
     */
    private function applyRecord(string $table, array $recordData): void
    {
        $uuid = $recordData['uuid'];
        
        switch ($table) {
            case 'products':
                $model = Product::where('uuid', $uuid)->first();
                if ($model) {
                    // ローカルで変更されていない場合のみ更新
                    if (!$model->is_dirty) {
                        $model->fill($recordData);
                        $model->is_dirty = false;
                        $model->last_synced_at = now();
                        $model->save();
                    } else {
                        // 競合を記録
                        $this->recordConflict($table, $uuid, $model->toArray(), $recordData);
                    }
                } else {
                    // 新規レコードとして作成
                    $model = new Product($recordData);
                    $model->is_dirty = false;
                    $model->last_synced_at = now();
                    $model->save();
                }
                break;
                
            case 'customers':
                $model = Customer::where('uuid', $uuid)->first();
                if ($model) {
                    if (!$model->is_dirty) {
                        $model->fill($recordData);
                        $model->is_dirty = false;
                        $model->last_synced_at = now();
                        $model->save();
                    } else {
                        $this->recordConflict($table, $uuid, $model->toArray(), $recordData);
                    }
                } else {
                    $model = new Customer($recordData);
                    $model->is_dirty = false;
                    $model->last_synced_at = now();
                    $model->save();
                }
                break;
                
            case 'transactions':
                $model = Transaction::where('uuid', $uuid)->first();
                if ($model) {
                    if (!$model->is_dirty) {
                        $model->fill($recordData);
                        $model->is_dirty = false;
                        $model->last_synced_at = now();
                        $model->save();
                    } else {
                        $this->recordConflict($table, $uuid, $model->toArray(), $recordData);
                    }
                } else {
                    $model = new Transaction($recordData);
                    $model->is_dirty = false;
                    $model->last_synced_at = now();
                    $model->save();
                }
                break;
                
            case 'inventory_adjustments':
                $model = InventoryAdjustment::where('uuid', $uuid)->first();
                if ($model) {
                    if (!$model->is_dirty) {
                        $model->fill($recordData);
                        $model->is_dirty = false;
                        $model->last_synced_at = now();
                        $model->save();
                    } else {
                        $this->recordConflict($table, $uuid, $model->toArray(), $recordData);
                    }
                } else {
                    $model = new InventoryAdjustment($recordData);
                    $model->is_dirty = false;
                    $model->last_synced_at = now();
                    $model->save();
                }
                break;
        }
    }

    /**
     * 競合を記録
     */
    private function recordConflict(string $table, string $uuid, array $localData, array $serverData): void
    {
        Log::warning('Conflict detected during server sync', [
            'table' => $table,
            'uuid' => $uuid
        ]);
        
        // 競合イベントを発火（フロントエンドで処理）
        // 実際の実装では、WebSocketやプッシュ通知を使用
        session()->push('sync_conflicts', [
            'table' => $table,
            'uuid' => $uuid,
            'local_data' => $localData,
            'server_data' => $serverData,
            'detected_at' => now()->toIso8601String()
        ]);
    }
}