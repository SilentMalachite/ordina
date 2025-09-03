<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * リクエスト制限の設定
     */
    private const LIMITS = [
        'sync' => [
            'max_attempts' => 100,  // 1時間あたりの最大リクエスト数
            'decay_minutes' => 60,  // 制限のリセット間隔（分）
        ],
        'search' => [
            'max_attempts' => 300,  // 1時間あたりの最大リクエスト数
            'decay_minutes' => 60,
        ],
        'general' => [
            'max_attempts' => 1000, // 1時間あたりの最大リクエスト数
            'decay_minutes' => 60,
        ],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\Http\Foundation\Response)  $next
     * @param  string|null  $type  Rate limitタイプ（sync, search, general）
     */
    public function handle(Request $request, Closure $next, ?string $type = 'general'): Response
    {
        $clientIdentifier = $this->getClientIdentifier($request);

        if (!$this->checkRateLimit($clientIdentifier, $type)) {
            Log::warning('Rate limit exceeded', [
                'client' => $clientIdentifier,
                'type' => $type,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'APIリクエスト制限を超えました。しばらく時間をおいてから再度お試しください。',
                'retry_after' => $this->getRetryAfter($clientIdentifier, $type),
            ], 429, [
                'Retry-After' => $this->getRetryAfter($clientIdentifier, $type),
                'X-RateLimit-Type' => $type,
            ]);
        }

        // 成功したリクエストを記録
        $this->recordRequest($clientIdentifier, $type);

        $response = $next($request);

        // レスポンスヘッダーにRate limit情報を追加
        $response->headers->set('X-RateLimit-Type', $type);
        $response->headers->set('X-RateLimit-Remaining', $this->getRemainingRequests($clientIdentifier, $type));

        return $response;
    }

    /**
     * クライアント識別子を取得
     */
    private function getClientIdentifier(Request $request): string
    {
        // APIトークンがある場合はトークンを使用
        $apiToken = $request->get('api_token') ?? $request->bearerToken();
        if ($apiToken) {
            return 'token_' . hash('sha256', $apiToken);
        }

        // IPアドレスを使用（開発・テスト用）
        return 'ip_' . $request->ip();
    }

    /**
     * Rate limitをチェック
     */
    private function checkRateLimit(string $clientIdentifier, string $type): bool
    {
        $key = $this->getCacheKey($clientIdentifier, $type);
        $attempts = Cache::get($key, 0);

        return $attempts < $this->getMaxAttempts($type);
    }

    /**
     * リクエストを記録
     */
    private function recordRequest(string $clientIdentifier, string $type): void
    {
        $key = $this->getCacheKey($clientIdentifier, $type);
        $decaySeconds = $this->getDecaySeconds($type);

        Cache::put($key, Cache::get($key, 0) + 1, $decaySeconds);
    }

    /**
     * 残りのリクエスト数を取得
     */
    private function getRemainingRequests(string $clientIdentifier, string $type): int
    {
        $key = $this->getCacheKey($clientIdentifier, $type);
        $attempts = Cache::get($key, 0);
        $maxAttempts = $this->getMaxAttempts($type);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * リトライまでの時間を取得（秒）
     */
    private function getRetryAfter(string $clientIdentifier, string $type): int
    {
        $key = $this->getCacheKey($clientIdentifier, $type);

        // Cache TTLを取得（LaravelのCacheでは直接取得できないため、デフォルト値を返す）
        return $this->getDecaySeconds($type);
    }

    /**
     * キャッシュキーを生成
     */
    private function getCacheKey(string $clientIdentifier, string $type): string
    {
        return "rate_limit:{$type}:{$clientIdentifier}";
    }

    /**
     * 最大試行回数を取得
     */
    private function getMaxAttempts(string $type): int
    {
        return self::LIMITS[$type]['max_attempts'] ?? self::LIMITS['general']['max_attempts'];
    }

    /**
     * 制限のリセット間隔を取得（秒）
     */
    private function getDecaySeconds(string $type): int
    {
        return (self::LIMITS[$type]['decay_minutes'] ?? self::LIMITS['general']['decay_minutes']) * 60;
    }

    /**
     * Rate limit統計を取得（管理用）
     */
    public static function getStats(string $clientIdentifier = null): array
    {
        $stats = [];

        foreach (array_keys(self::LIMITS) as $type) {
            if ($clientIdentifier) {
                $key = "rate_limit:{$type}:{$clientIdentifier}";
                $attempts = Cache::get($key, 0);
                $stats[$type] = [
                    'attempts' => $attempts,
                    'remaining' => max(0, self::LIMITS[$type]['max_attempts'] - $attempts),
                    'limit' => self::LIMITS[$type]['max_attempts'],
                ];
            } else {
                // 全クライアントの統計を集計（パフォーマンスに注意）
                $stats[$type] = [
                    'limit' => self::LIMITS[$type]['max_attempts'],
                    'decay_minutes' => self::LIMITS[$type]['decay_minutes'],
                ];
            }
        }

        return $stats;
    }

    /**
     * Rate limitをリセット（管理用）
     */
    public static function resetLimit(string $clientIdentifier, string $type): bool
    {
        $key = "rate_limit:{$type}:{$clientIdentifier}";
        return Cache::forget($key);
    }
}
