<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSyncableFields;

class Customer extends Model
{
    use HasFactory, HasSyncableFields;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'type',
        'notes',
    ];

    /**
     * コンストラクタ
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // HasSyncableFieldsトレイトの初期化メソッドを呼び出し
        if (method_exists($this, 'initializeHasSyncableFields')) {
            $this->initializeHasSyncableFields();
        }
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
