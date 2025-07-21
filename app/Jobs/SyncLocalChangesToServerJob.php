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
use Native\Laravel\Facades\Notification;

class SyncLocalChangesToServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting local to server sync for user: ' . $this->userId);

        $syncData = $this->collectUnsyncedData();
        
        if (empty($syncData)) {
            Log::info('No unsynced data found');
            return;
        }

        try {
            // サーバーのエンドポイント（環境設定から取得）
            $serverUrl = config('sync.server_url', 'https://api.ordina.example.com');
            $apiToken = config('sync.api_token');

            // データをサーバーに送信
            $response = Http::withToken($apiToken)
                ->timeout(30)
                ->post($serverUrl . '/api/sync/push', [
                    'data' => $syncData
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // 同期成功したレコードをマーク
                $this->markRecordsAsSynced($syncData);
                
                // 競合があった場合の処理
                if (!empty($result['conflicts'])) {
                    $this->handleConflicts($result['conflicts']);
                }
                
                Log::info('Sync completed successfully', [
                    'processed' => $result['processed'] ?? 0,
                    'conflicts' => count($result['conflicts'] ?? [])
                ]);
                
                // 成功通知
                Notification::title('データ同期完了')
                    ->message('ローカルの変更がサーバーに同期されました')
                    ->show();
                    
            } else {
                throw new \Exception('Server returned error: ' . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error('Sync failed: ' . $e->getMessage());
            
            // エラー通知
            Notification::title('同期エラー')
                ->message('データ同期中にエラーが発生しました: ' . $e->getMessage())
                ->show();
                
            // ジョブを再試行
            $this->release(300); // 5分後に再試行
        }
    }

    /**
     * 未同期データを収集
     */
    private function collectUnsyncedData(): array
    {
        $data = [];
        
        // 商品の未同期データ
        $unsyncedProducts = Product::unsyncedRecords()->get();
        if ($unsyncedProducts->isNotEmpty()) {
            $data[] = [
                'table' => 'products',
                'records' => $unsyncedProducts->toArray()
            ];
        }
        
        // 顧客の未同期データ
        $unsyncedCustomers = Customer::unsyncedRecords()->get();
        if ($unsyncedCustomers->isNotEmpty()) {
            $data[] = [
                'table' => 'customers',
                'records' => $unsyncedCustomers->toArray()
            ];
        }
        
        // 取引の未同期データ
        $unsyncedTransactions = Transaction::unsyncedRecords()->get();
        if ($unsyncedTransactions->isNotEmpty()) {
            $data[] = [
                'table' => 'transactions',
                'records' => $unsyncedTransactions->toArray()
            ];
        }
        
        // 在庫調整の未同期データ
        $unsyncedAdjustments = InventoryAdjustment::unsyncedRecords()->get();
        if ($unsyncedAdjustments->isNotEmpty()) {
            $data[] = [
                'table' => 'inventory_adjustments',
                'records' => $unsyncedAdjustments->toArray()
            ];
        }
        
        return $data;
    }

    /**
     * 同期済みとしてマーク
     */
    private function markRecordsAsSynced(array $syncData): void
    {
        foreach ($syncData as $tableData) {
            $table = $tableData['table'];
            $records = $tableData['records'];
            $uuids = array_column($records, 'uuid');
            
            switch ($table) {
                case 'products':
                    Product::whereIn('uuid', $uuids)->update([
                        'is_dirty' => false,
                        'last_synced_at' => now()
                    ]);
                    break;
                    
                case 'customers':
                    Customer::whereIn('uuid', $uuids)->update([
                        'is_dirty' => false,
                        'last_synced_at' => now()
                    ]);
                    break;
                    
                case 'transactions':
                    Transaction::whereIn('uuid', $uuids)->update([
                        'is_dirty' => false,
                        'last_synced_at' => now()
                    ]);
                    break;
                    
                case 'inventory_adjustments':
                    InventoryAdjustment::whereIn('uuid', $uuids)->update([
                        'is_dirty' => false,
                        'last_synced_at' => now()
                    ]);
                    break;
            }
        }
    }

    /**
     * 競合の処理
     */
    private function handleConflicts(array $conflicts): void
    {
        // 競合情報をセッションまたはデータベースに保存
        // ユーザーに競合解決UIを表示するため
        
        foreach ($conflicts as $conflict) {
            Log::warning('Sync conflict detected', [
                'table' => $conflict['table'],
                'uuid' => $conflict['uuid'],
                'resolution_strategy' => $conflict['resolution_strategy']
            ]);
        }
        
        // 競合があることを通知
        Notification::title('同期の競合が発生')
            ->message(count($conflicts) . '件の競合が検出されました。確認してください。')
            ->show();
    }
}