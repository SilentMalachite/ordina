<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncDataRequest;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use App\Jobs\SyncLocalChangesToServerJob;
use App\Jobs\SyncFromServerJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SyncController extends Controller
{
    /**
     * ヘルスチェック用エンドポイント
     */
    public function healthCheck()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * 同期プロセスの開始
     */
    public function start(Request $request)
    {
        try {
            $userId = Auth::id();
            
            // Push同期（ローカル→サーバー）
            $pushResult = $this->performPushSync($userId);
            
            // Pull同期（サーバー→ローカル）
            $pullResult = $this->performPullSync($userId);
            
            return response()->json([
                'success' => true,
                'push' => $pushResult,
                'pull' => $pullResult,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Sync start error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'データ同期中にエラーが発生しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ローカルの変更をサーバーに送信（Push）
     */
    public function push(SyncDataRequest $request)
    {
        // SyncDataRequestで検証済みのデータを取得

        $results = [];
        $conflicts = [];

        DB::beginTransaction();
        
        try {
            foreach ($request->data as $tableData) {
                $table = $tableData['table'];
                $records = $tableData['records'];
                
                foreach ($records as $record) {
                    $result = $this->processRecord($table, $record);
                    
                    if ($result['status'] === 'conflict') {
                        $conflicts[] = $result;
                    } else {
                        $results[] = $result;
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'processed' => count($results),
                'conflicts' => $conflicts,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Push sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'データ送信中にエラーが発生しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * サーバーから最新データを取得（Pull）
     */
    public function pull(Request $request)
    {
        $request->validate([
            'since' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'tables' => 'nullable|array',
            'tables.*' => 'string|in:products,customers,transactions,inventory_adjustments'
        ]);

        $since = $request->since ? Carbon::parse($request->since) : Carbon::now()->subDays(30);
        $tables = $request->tables ?? ['products', 'customers', 'transactions', 'inventory_adjustments'];
        
        $data = [];
        
        try {
            foreach ($tables as $table) {
                $modelClass = $this->getModelClass($table);
                
                // 指定日時以降に更新されたレコードを取得
                $records = $modelClass::updatedSince($since)
                    ->select('*')
                    ->get()
                    ->toArray();
                
                if (count($records) > 0) {
                    $data[$table] = $records;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Pull sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'データ取得中にエラーが発生しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Push同期の実行
     */
    private function performPushSync($userId)
    {
        $unsyncedCounts = [
            'products' => Product::unsyncedRecords()->count(),
            'customers' => Customer::unsyncedRecords()->count(),
            'transactions' => Transaction::unsyncedRecords()->count(),
            'inventory_adjustments' => InventoryAdjustment::unsyncedRecords()->count()
        ];
        
        // 非同期ジョブとして実行（NativePHP環境では同期的に実行される可能性あり）
        if (array_sum($unsyncedCounts) > 0) {
            SyncLocalChangesToServerJob::dispatchSync($userId);
        }
        
        return [
            'unsynced_counts' => $unsyncedCounts,
            'status' => array_sum($unsyncedCounts) > 0 ? 'syncing' : 'no_changes'
        ];
    }

    /**
     * Pull同期の実行
     */
    private function performPullSync($userId)
    {
        // 最後の同期時刻を取得（簡単のため、全テーブルで共通の時刻を使用）
        $lastSyncTime = $this->getLastSyncTime();
        
        // 非同期ジョブとして実行
        SyncFromServerJob::dispatchSync($userId, $lastSyncTime);
        
        return [
            'last_sync_time' => $lastSyncTime,
            'status' => 'syncing'
        ];
    }

    /**
     * レコードの処理（競合解決を含む）
     */
    private function processRecord($table, $recordData)
    {
        $modelClass = $this->getModelClass($table);
        $uuid = $recordData['uuid'];
        
        // 既存レコードの検索
        $existingRecord = $modelClass::where('uuid', $uuid)->first();
        
        if ($existingRecord) {
            // 更新の場合
            $localTimestamp = Carbon::parse($recordData['updated_at']);
            $serverTimestamp = $existingRecord->updated_at;
            
            // 競合チェック（サーバー側も更新されている場合）
            if ($existingRecord->is_dirty && $serverTimestamp > $localTimestamp) {
                return [
                    'status' => 'conflict',
                    'table' => $table,
                    'uuid' => $uuid,
                    'local_data' => $recordData,
                    'server_data' => $existingRecord->toArray(),
                    'resolution_strategy' => 'manual' // 手動解決が必要
                ];
            }
            
            // 競合がない場合は更新
            $existingRecord->fill($recordData);
            $existingRecord->is_dirty = false;
            $existingRecord->last_synced_at = now();
            $existingRecord->save();
            
            return [
                'status' => 'updated',
                'table' => $table,
                'uuid' => $uuid
            ];
        } else {
            // 新規作成
            $recordData['is_dirty'] = false;
            $recordData['last_synced_at'] = now();
            $modelClass::create($recordData);
            
            return [
                'status' => 'created',
                'table' => $table,
                'uuid' => $uuid
            ];
        }
    }

    /**
     * テーブル名からモデルクラスを取得
     */
    private function getModelClass($table)
    {
        $models = [
            'products' => Product::class,
            'customers' => Customer::class,
            'transactions' => Transaction::class,
            'inventory_adjustments' => InventoryAdjustment::class
        ];
        
        return $models[$table] ?? null;
    }

    /**
     * 最後の同期時刻を取得
     */
    private function getLastSyncTime()
    {
        // 各テーブルの最新同期時刻から最も古いものを取得
        $lastSyncTimes = [];
        
        $tables = ['products', 'customers', 'transactions', 'inventory_adjustments'];
        foreach ($tables as $table) {
            $modelClass = $this->getModelClass($table);
            $lastSync = $modelClass::whereNotNull('last_synced_at')
                ->max('last_synced_at');
            
            if ($lastSync) {
                $lastSyncTimes[] = $lastSync;
            }
        }
        
        return $lastSyncTimes ? min($lastSyncTimes) : Carbon::now()->subDays(30);
    }

    /**
     * 競合解決エンドポイント
     */
    public function resolveConflict(Request $request)
    {
        $request->validate([
            'conflict' => 'required|array',
            'conflict.table' => 'required|string',
            'conflict.uuid' => 'required|string',
            'conflict.local_data' => 'required|array',
            'conflict.server_data' => 'required|array',
            'resolution' => 'required|in:local,server'
        ]);

        try {
            DB::transaction(function() use ($request) {
                $conflict = $request->conflict;
                $resolution = $request->resolution;
                $table = $conflict['table'];
                $uuid = $conflict['uuid'];
                
                switch ($table) {
                    case 'products':
                        $model = Product::where('uuid', $uuid)->firstOrFail();
                        break;
                    case 'customers':
                        $model = Customer::where('uuid', $uuid)->firstOrFail();
                        break;
                    case 'transactions':
                        $model = Transaction::where('uuid', $uuid)->firstOrFail();
                        break;
                    case 'inventory_adjustments':
                        $model = InventoryAdjustment::where('uuid', $uuid)->firstOrFail();
                        break;
                    default:
                        throw new \Exception('Unknown table: ' . $table);
                }
                
                // 選択された解決方法に基づいて更新
                if ($resolution === 'server') {
                    // サーバーのデータを使用
                    $model->fill($conflict['server_data']);
                    $model->is_dirty = false;
                    $model->last_synced_at = now();
                    $model->save();
                    
                    Log::info('Conflict resolved using server data', [
                        'table' => $table,
                        'uuid' => $uuid
                    ]);
                } else {
                    // ローカルのデータを保持（is_dirtyをtrueに維持して次回同期で送信）
                    $model->is_dirty = true;
                    $model->save();
                    
                    Log::info('Conflict resolved using local data', [
                        'table' => $table,
                        'uuid' => $uuid
                    ]);
                }
            });
            
            return response()->json([
                'success' => true,
                'message' => '競合が解決されました'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Conflict resolution error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '競合解決中にエラーが発生しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}