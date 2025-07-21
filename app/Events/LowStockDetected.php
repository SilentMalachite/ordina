<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Product $product;
    public int $threshold;

    /**
     * Create a new event instance.
     */
    public function __construct(Product $product, int $threshold = 10)
    {
        $this->product = $product;
        $this->threshold = $threshold;
    }
}
