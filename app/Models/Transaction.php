<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

    /**
     * この貸し出し取引を返却済みにする
     *
     * @return bool
     * @throws \Exception
     */
    public function returnItem(): bool
    {
        if ($this->type !== 'rental') {
            throw new \Exception('この取引は貸し出しではありません。');
        }

        if ($this->returned_at) {
            throw new \Exception('この商品は既に返却済みです。');
        }

        try {
            DB::transaction(function() {
                $this->update(['returned_at' => now()]);

                $this->product->increment('stock_quantity', $this->quantity);
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('商品返却処理中にエラーが発生しました: ' . $e->getMessage());
            throw $e;
        }
    }
}
