<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\InventoryAdjustment;
use App\Models\ClosingDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SafeDataDeletionService
{
    /**
     * 安全なデータ削除を実行
     *
     * @param string $dataType
     * @param int $userId
     * @return array
     */
    public function deleteData(string $dataType, int $userId): array
    {
        try {
            DB::beginTransaction();

            $deletedCounts = [];
            $backupData = [];

            switch ($dataType) {
                case 'transactions':
                    $deletedCounts = $this->deleteTransactions($backupData);
                    break;
                    
                case 'products':
                    $deletedCounts = $this->deleteProducts($backupData);
                    break;
                    
                case 'customers':
                    $deletedCounts = $this->deleteCustomers($backupData);
                    break;
                    
                case 'all':
                    $deletedCounts = $this->deleteAllData($backupData);
                    break;
                    
                default:
                    throw new \InvalidArgumentException("無効なデータタイプ: {$dataType}");
            }

            // バックアップデータを保存
            if (!empty($backupData)) {
                $this->createBackup($backupData, $dataType, $userId);
            }

            // 削除ログを記録
            $this->logDeletion($dataType, $deletedCounts, $userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'データが安全に削除されました。',
                'deleted_counts' => $deletedCounts,
                'backup_created' => !empty($backupData)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('データ削除中にエラーが発生しました', [
                'data_type' => $dataType,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'データ削除中にエラーが発生しました: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 取引データの削除
     */
    private function deleteTransactions(array &$backupData): array
    {
        // バックアップデータを取得
        $backupData['transactions'] = Transaction::with(['product', 'customer', 'user'])->get()->toArray();
        $backupData['inventory_adjustments'] = InventoryAdjustment::with(['product', 'user'])->get()->toArray();

        // 在庫数をリセット
        Product::query()->update(['stock_quantity' => 0]);

        // データを削除
        $transactionCount = Transaction::count();
        $adjustmentCount = InventoryAdjustment::count();

        Transaction::truncate();
        InventoryAdjustment::truncate();

        return [
            'transactions' => $transactionCount,
            'inventory_adjustments' => $adjustmentCount
        ];
    }

    /**
     * 商品データの削除
     */
    private function deleteProducts(array &$backupData): array
    {
        // バックアップデータを取得
        $backupData['products'] = Product::all()->toArray();
        $backupData['transactions'] = Transaction::with(['product', 'customer', 'user'])->get()->toArray();
        $backupData['inventory_adjustments'] = InventoryAdjustment::with(['product', 'user'])->get()->toArray();

        // データを削除
        $productCount = Product::count();
        $transactionCount = Transaction::count();
        $adjustmentCount = InventoryAdjustment::count();

        Transaction::truncate();
        InventoryAdjustment::truncate();
        Product::truncate();

        return [
            'products' => $productCount,
            'transactions' => $transactionCount,
            'inventory_adjustments' => $adjustmentCount
        ];
    }

    /**
     * 顧客データの削除
     */
    private function deleteCustomers(array &$backupData): array
    {
        // バックアップデータを取得
        $backupData['customers'] = Customer::all()->toArray();
        $backupData['transactions'] = Transaction::with(['product', 'customer', 'user'])->get()->toArray();

        // データを削除
        $customerCount = Customer::count();
        $transactionCount = Transaction::count();

        Transaction::truncate();
        Customer::truncate();

        return [
            'customers' => $customerCount,
            'transactions' => $transactionCount
        ];
    }

    /**
     * 全データの削除
     */
    private function deleteAllData(array &$backupData): array
    {
        // バックアップデータを取得
        $backupData['products'] = Product::all()->toArray();
        $backupData['customers'] = Customer::all()->toArray();
        $backupData['transactions'] = Transaction::with(['product', 'customer', 'user'])->get()->toArray();
        $backupData['inventory_adjustments'] = InventoryAdjustment::with(['product', 'user'])->get()->toArray();
        $backupData['closing_dates'] = ClosingDate::all()->toArray();

        // データを削除
        $counts = [
            'products' => Product::count(),
            'customers' => Customer::count(),
            'transactions' => Transaction::count(),
            'inventory_adjustments' => InventoryAdjustment::count(),
            'closing_dates' => ClosingDate::count()
        ];

        Transaction::truncate();
        InventoryAdjustment::truncate();
        Product::truncate();
        Customer::truncate();
        ClosingDate::truncate();

        return $counts;
    }

    /**
     * バックアップデータを作成
     */
    private function createBackup(array $data, string $dataType, int $userId): void
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = storage_path("app/backups/deletion_backup_{$dataType}_{$timestamp}.json");
        
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }
        
        $backupData = [
            'created_at' => now()->toISOString(),
            'deletion_type' => $dataType,
            'deleted_by_user_id' => $userId,
            'data' => $data
        ];
        
        file_put_contents($backupPath, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 削除ログを記録
     */
    private function logDeletion(string $dataType, array $deletedCounts, int $userId): void
    {
        Log::info('データ削除が実行されました', [
            'data_type' => $dataType,
            'deleted_counts' => $deletedCounts,
            'deleted_by_user_id' => $userId,
            'deleted_at' => now()->toISOString()
        ]);
    }

    /**
     * 削除前のデータ統計を取得
     */
    public function getDataStatistics(): array
    {
        return [
            'products_count' => Product::count(),
            'customers_count' => Customer::count(),
            'transactions_count' => Transaction::count(),
            'inventory_adjustments_count' => InventoryAdjustment::count(),
            'closing_dates_count' => ClosingDate::count(),
            'database_size' => $this->getDatabaseSize()
        ];
    }

    /**
     * データベースサイズを取得
     */
    private function getDatabaseSize(): int
    {
        $dbPath = database_path('ordina.sqlite');
        
        if (file_exists($dbPath)) {
            return filesize($dbPath);
        }
        
        return 0;
    }
}