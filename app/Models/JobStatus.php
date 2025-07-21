<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_name',
        'job_id',
        'status',
        'progress',
        'output',
        'meta',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * ジョブのステータス定数
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * ジョブを所有するユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ジョブが保留中かどうか
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * ジョブが処理中かどうか
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * ジョブが完了したかどうか
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * ジョブが失敗したかどうか
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * ジョブが終了したかどうか（完了または失敗）
     */
    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED]);
    }
}
