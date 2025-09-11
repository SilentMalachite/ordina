<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Services\DesktopNotificationService;

class SendLowStockNotification
{
    /**
     * Handle the event.
     */
    public function handle(LowStockDetected $event): void
    {
        $product = $event->product;
        app(DesktopNotificationService::class)->sendLowStockAlert($product);
    }
}
