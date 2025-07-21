<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClosingDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_month',
        'description',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
