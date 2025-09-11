<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncConflict extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_name',
        'record_uuid',
        'local_data',
        'server_data',
        'resolution_strategy',
        'status',
        'user_id',
        'resolved_at',
        'conflict_reason',
    ];

    protected $casts = [
        'local_data' => 'array',
        'server_data' => 'array',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * 競合を解決したユーザー
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 保留中の競合を取得するスコープ
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * 解決済みの競合を取得するスコープ
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * 競合を解決する
     */
    public function resolve(string $strategy, int $userId): bool
    {
        $this->update([
            'resolution_strategy' => $strategy,
            'status' => 'resolved',
            'user_id' => $userId,
            'resolved_at' => now(),
        ]);

        return true;
    }

    /**
     * 競合を無視する
     */
    public function ignore(int $userId): bool
    {
        $this->update([
            'status' => 'ignored',
            'user_id' => $userId,
            'resolved_at' => now(),
        ]);

        return true;
    }

    /**
     * 競合データの差分を取得
     */
    public function getDifferences(): array
    {
        $localData = $this->local_data ?? [];
        $serverData = $this->server_data ?? [];

        $differences = [];

        foreach ($localData as $key => $localValue) {
            if (!array_key_exists($key, $serverData) || $localValue !== $serverData[$key]) {
                $differences[$key] = [
                    'local' => $localValue,
                    'server' => $serverData[$key] ?? null,
                ];
            }
        }

        // サーバーにしかないフィールドも差分に含める
        foreach ($serverData as $key => $serverValue) {
            if (!array_key_exists($key, $localData)) {
                $differences[$key] = [
                    'local' => null,
                    'server' => $serverValue,
                ];
            }
        }

        return $differences;
    }
}
