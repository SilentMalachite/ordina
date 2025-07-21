<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSyncableFields;

class Transaction extends Model
{
    use HasFactory, HasSyncableFields;

    protected $fillable = [
        'product_id',
        'customer_id',
        'user_id',
        'type',
        'quantity',
        'unit_price',
        'total_amount',
        'transaction_date',
        'expected_return_date',
        'returned_at',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'transaction_date' => 'date',
        'expected_return_date' => 'date',
        'returned_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
