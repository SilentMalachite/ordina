<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'last_used_at',
        'user_id',
        'revoked',
    ];

    protected $casts = [
        'abilities' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    protected $hidden = [
        'token',
    ];

    /**
     * トークン所有者
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 有効なトークンのみ取得するスコープ
     */
    public function scopeValid($query)
    {
        return $query->where('revoked', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * 期限切れのトークンを取得するスコープ
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * 取り消されたトークンを取得するスコープ
     */
    public function scopeRevoked($query)
    {
        return $query->where('revoked', true);
    }

    /**
     * 新しいトークンを生成
     */
    public static function generate(User $user, string $name, array $abilities = ['*'], ?Carbon $expiresAt = null): self
    {
        $plainToken = Str::random(40);
        $hashedToken = hash('sha256', $plainToken);

        return static::create([
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'user_id' => $user->id,
            'revoked' => false, // 明示的にfalseを設定
        ]);
    }

    /**
     * トークン文字列からトークンを検索（検証用）
     */
    public static function findByToken(string $token): ?self
    {
        $hashedToken = hash('sha256', $token);

        return static::where('token', $hashedToken)->first();
    }

    /**
     * トークンが有効かどうかをチェック
     */
    public function isValid(): bool
    {
        if ($this->revoked) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * 指定された権限を持っているかチェック
     */
    public function can(string $ability): bool
    {
        if (in_array('*', $this->abilities ?? [])) {
            return true;
        }

        return in_array($ability, $this->abilities ?? []);
    }

    /**
     * トークンを使用したことを記録
     */
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * トークンを取り消し
     */
    public function revoke(): bool
    {
        return $this->update(['revoked' => true]);
    }

    /**
     * トークンを再有効化
     */
    public function unrevoke(): bool
    {
        return $this->update(['revoked' => false]);
    }

    /**
     * 有効期限を延長
     */
    public function extendExpiry(?Carbon $expiresAt): bool
    {
        return $this->update(['expires_at' => $expiresAt]);
    }

    /**
     * トークンを再生成（セキュリティのため）
     */
    public function regenerate(): string
    {
        $plainToken = Str::random(40);
        $hashedToken = hash('sha256', $plainToken);

        $this->update(['token' => $hashedToken]);

        return $plainToken;
    }

    /**
     * トークンの残り有効期間を取得
     */
    public function getRemainingTime(): ?int
    {
        if (!$this->expires_at) {
            return null; // 無期限
        }

        return now()->diffInSeconds($this->expires_at, false);
    }

    /**
     * トークンが期限切れ間近かチェック（デフォルト24時間）
     */
    public function isExpiringSoon(int $hours = 24): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->diffInHours(now()) <= $hours;
    }
}
