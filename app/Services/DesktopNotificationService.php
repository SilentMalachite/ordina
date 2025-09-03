<?php

namespace App\Services;

use Native\Laravel\Facades\Notification;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;

class DesktopNotificationService
{
    /**
     * 低在庫アラートを送信
     */
    public function sendLowStockAlert(Product $product): void
    {
        if ($product->stock_quantity <= 10) {
            Notification::title('低在庫アラート')
                ->message("商品「{$product->name}」の在庫が少なくなっています。\n現在の在庫数: {$product->stock_quantity}個")
                ->show();
        }
    }

    /**
     * 取引完了通知を送信
     */
    public function sendTransactionNotification(Transaction $transaction): void
    {
        $type = $transaction->type === 'sale' ? '売上' : '貸出';
        
        Notification::title('取引完了')
            ->message("{$type}取引が完了しました。\n商品: {$transaction->product->name}\n金額: ¥" . number_format($transaction->total_amount))
            ->show();
    }

    /**
     * 日次サマリー通知を送信
     */
    public function sendDailySummary(): void
    {
        $today = Carbon::today();
        
        // 今日の売上統計
        $todaySales = Transaction::where('type', 'sale')
            ->whereDate('transaction_date', $today)
            ->sum('total_amount');
        
        $todayTransactions = Transaction::whereDate('transaction_date', $today)->count();
        
        // 低在庫商品数
        $lowStockCount = Product::where('stock_quantity', '<=', 10)->count();
        
        $message = "本日の取引件数: {$todayTransactions}件\n";
        $message .= "本日の売上: ¥" . number_format($todaySales) . "\n";
        
        if ($lowStockCount > 0) {
            $message .= "⚠️ 低在庫商品: {$lowStockCount}種類";
        } else {
            $message .= "✅ 在庫状況: 正常";
        }
        
        Notification::title('日次サマリー')
            ->message($message)
            ->show();
    }

    /**
     * バックアップ完了通知を送信
     */
    public function sendBackupNotification(string $filename): void
    {
        Notification::title('バックアップ完了')
            ->message("データバックアップが正常に完了しました。\nファイル: {$filename}")
            ->show();
    }

    /**
     * エラー通知を送信
     */
    public function sendErrorNotification(string $title, string $message): void
    {
        Notification::title($title)
            ->message($message)
            ->show();
    }

    /**
     * 成功通知を送信
     */
    public function sendSuccessNotification(string $title, string $message): void
    {
        Notification::title($title)
            ->message($message)
            ->show();
    }

    /**
     * 定期通知を設定
     */
    public function scheduleNotifications(): void
    {
        // 毎日午前9時に日次サマリーを送信
        // 実際の実装では、Laravelのスケジューラーを使用
    }
}