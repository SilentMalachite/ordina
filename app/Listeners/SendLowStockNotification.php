<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use Native\Laravel\Facades\Notification;

class SendLowStockNotification
{
    /**
     * Handle the event.
     */
    public function handle(LowStockDetected $event): void
    {
        $product = $event->product;
        
        Notification::title('在庫僅少アラート')
            ->message("「{$product->name}」の在庫が残り {$product->stock_quantity} 個です。")
            ->show();
    }
}
