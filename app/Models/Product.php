<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSyncableFields;

class Product extends Model
{
    use HasFactory, HasSyncableFields;

    protected $fillable = [
        'product_code',
        'name',
        'stock_quantity',
        'unit_price',
        'selling_price',
        'description',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function inventoryAdjustments()
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function latestTransaction()
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }
}
