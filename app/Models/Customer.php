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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
