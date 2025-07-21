<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSyncableFields;

class InventoryAdjustment extends Model
{
    use HasFactory, HasSyncableFields;

    protected $fillable = [
        'product_id',
        'user_id',
        'adjustment_type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reason',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
