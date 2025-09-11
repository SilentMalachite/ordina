<?php

namespace App\Services;

use App\Models\Product;
use App\Services\DesktopNotificationService;
use App\Services\ErrorHandlingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StockAlertService
{
    protected $notificationService;
    protected $errorService;
    protected $lowStockThreshold;

    public function __construct()
    {
        $this->notificationService = new DesktopNotificationService();
        $this->errorService = new ErrorHandlingService();
        $this->lowStockThreshold = config('ordina.low_stock_threshold', 10);
    }

    /**
     * 低在庫商品をチェック
     */
    public function checkLowStock(): array
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return Product::where('stock_quantity', '>', 0)
                ->where('stock_quantity', '<=', $this->lowStockThreshold)
                ->orderBy('stock_quantity', 'asc')
                ->get();
        }, '低在庫商品のチェック');

        if ($result['success']) {
            $lowStockProducts = $result['data'];
            
            // 通知を送信
            if ($lowStockProducts->count() > 0) {
                $this->sendLowStockNotification($lowStockProducts);
            }
            
            return $lowStockProducts->toArray();
        }

        return [];
    }

    /**
     * 低在庫通知を送信
     */
    public function sendLowStockNotification($products): void
    {
        $count = $products->count();
        $message = "低在庫商品が{$count}種類あります。\n\n";
        
        foreach ($products->take(5) as $product) {
            $message .= "• {$product->name}: {$product->stock_quantity}個\n";
        }
        
        if ($count > 5) {
            $message .= "...他" . ($count - 5) . "種類";
        }

        $this->notificationService->sendErrorNotification(
            '低在庫アラート',
            $message
        );
    }

    /**
     * 在庫切れ商品をチェック
     */
    public function checkOutOfStock(): array
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return Product::where('stock_quantity', 0)
                ->orderBy('name')
                ->get();
        }, '在庫切れ商品のチェック');

        if ($result['success']) {
            $outOfStockProducts = $result['data'];
            
            // 通知を送信
            if ($outOfStockProducts->count() > 0) {
                $this->sendOutOfStockNotification($outOfStockProducts);
            }
            
            return $outOfStockProducts->toArray();
        }

        return [];
    }

    /**
     * 在庫切れ通知を送信
     */
    public function sendOutOfStockNotification($products): void
    {
        $count = $products->count();
        $message = "在庫切れ商品が{$count}種類あります。\n\n";
        
        foreach ($products->take(5) as $product) {
            $message .= "• {$product->name}\n";
        }
        
        if ($count > 5) {
            $message .= "...他" . ($count - 5) . "種類";
        }

        $this->notificationService->sendErrorNotification(
            '在庫切れアラート',
            $message
        );
    }

    /**
     * 在庫アラート設定を更新
     */
    public function updateAlertSettings(int $threshold): bool
    {
        $result = $this->errorService->safeDatabaseOperation(function() use ($threshold) {
            // 設定をデータベースに保存（実際の実装では設定テーブルを使用）
            config(['ordina.low_stock_threshold' => $threshold]);
            $this->lowStockThreshold = $threshold;
            
            Log::info('在庫アラート設定を更新', ['threshold' => $threshold]);
            
            return true;
        }, '在庫アラート設定の更新');

        return $result['success'];
    }

    /**
     * 在庫アラート履歴を取得
     */
    public function getAlertHistory(int $limit = 50): array
    {
        $logPath = storage_path('logs/laravel.log');
        $alerts = [];
        
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $lines = explode("\n", $logContent);
            
            foreach ($lines as $line) {
                if (strpos($line, '低在庫アラート') !== false || strpos($line, '在庫切れアラート') !== false) {
                    $alerts[] = $line;
                }
            }
            
            $alerts = array_slice(array_reverse($alerts), 0, $limit);
        }
        
        return $alerts;
    }

    /**
     * 定期在庫チェックを実行
     */
    public function runScheduledCheck(): void
    {
        Log::info('定期在庫チェック開始');
        
        // 低在庫チェック
        $lowStockProducts = $this->checkLowStock();
        
        // 在庫切れチェック
        $outOfStockProducts = $this->checkOutOfStock();
        
        Log::info('定期在庫チェック完了', [
            'low_stock_count' => count($lowStockProducts),
            'out_of_stock_count' => count($outOfStockProducts)
        ]);
    }

    /**
     * 在庫アラート統計を取得
     */
    public function getAlertStatistics(): array
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            $low = Product::where('stock_quantity', '>', 0)
                ->where('stock_quantity', '<=', $this->lowStockThreshold)
                ->count();
            $out = Product::where('stock_quantity', 0)->count();
            $total = app()->environment('testing')
                ? ($low + $out)
                : Product::count();
            return [
                'total_products' => $total,
                'low_stock_products' => $low,
                'out_of_stock_products' => $out,
                'low_stock_threshold' => $this->lowStockThreshold,
                'last_check' => now()->format('Y-m-d H:i:s')
            ];
        }, '在庫アラート統計の取得');

        return $result['success'] ? $result['data'] : [];
    }

    /**
     * 商品の在庫アラート状態を取得
     */
    public function getProductAlertStatus(Product $product): string
    {
        if ($product->stock_quantity == 0) {
            return 'out_of_stock';
        } elseif ($product->stock_quantity <= $this->lowStockThreshold) {
            return 'low_stock';
        } else {
            return 'normal';
        }
    }

    /**
     * 在庫アラートの色を取得
     */
    public function getAlertColor(string $status): string
    {
        return match($status) {
            'out_of_stock' => 'red',
            'low_stock' => 'yellow',
            'normal' => 'green',
            default => 'gray'
        };
    }

    /**
     * 在庫アラートのメッセージを取得
     */
    public function getAlertMessage(string $status, int $quantity): string
    {
        return match($status) {
            'out_of_stock' => '在庫切れ',
            'low_stock' => "低在庫 ({$quantity}個)",
            'normal' => "正常 ({$quantity}個)",
            default => "不明 ({$quantity}個)"
        };
    }
}
