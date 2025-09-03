<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ApiRateLimiter;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiMonitoringController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:system-manage')->only(['index', 'resetLimits', 'blockClient']);
    }

    /**
     * API監視ダッシュボードを表示
     */
    public function index(Request $request)
    {
        // Rate limiting統計を取得
        $rateLimitStats = ApiRateLimiter::getStats();

        // APIトークンの使用統計
        $tokenStats = $this->getApiTokenStats();

        // 最近のAPIリクエストログ（ログファイルから取得）
        $recentApiLogs = $this->getRecentApiLogs();

        // ブロックされたクライアント
        $blockedClients = Cache::get('blocked_clients', []);

        return view('api-monitoring.index', compact(
            'rateLimitStats',
            'tokenStats',
            'recentApiLogs',
            'blockedClients'
        ));
    }

    /**
     * 特定のクライアントのRate limitをリセット
     */
    public function resetLimits(Request $request)
    {
        $request->validate([
            'client_identifier' => 'required|string',
            'type' => 'required|string|in:sync,search,general',
        ]);

        $clientIdentifier = $request->client_identifier;
        $type = $request->type;

        if (ApiRateLimiter::resetLimit($clientIdentifier, $type)) {
            Log::info('Rate limit reset', [
                'client' => $clientIdentifier,
                'type' => $type,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('success', 'Rate limitをリセットしました。');
        }

        return redirect()->back()
            ->with('error', 'Rate limitのリセットに失敗しました。');
    }

    /**
     * クライアントをブロック
     */
    public function blockClient(Request $request)
    {
        $request->validate([
            'client_identifier' => 'required|string',
            'reason' => 'required|string|max:255',
            'duration_hours' => 'nullable|integer|min:1|max:168', // 最大1週間
        ]);

        $clientIdentifier = $request->client_identifier;
        $reason = $request->reason;
        $durationHours = $request->duration_hours ?? 24; // デフォルト24時間

        $blockedClients = Cache::get('blocked_clients', []);
        $blockedClients[$clientIdentifier] = [
            'reason' => $reason,
            'blocked_at' => now(),
            'expires_at' => now()->addHours($durationHours),
            'blocked_by' => auth()->id(),
        ];

        Cache::put('blocked_clients', $blockedClients, $durationHours * 3600);

        // 既存のRate limitもリセット
        foreach (['sync', 'search', 'general'] as $type) {
            ApiRateLimiter::resetLimit($clientIdentifier, $type);
        }

        Log::warning('Client blocked', [
            'client' => $clientIdentifier,
            'reason' => $reason,
            'duration_hours' => $durationHours,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', "クライアントを{$durationHours}時間ブロックしました。");
    }

    /**
     * クライアントのブロックを解除
     */
    public function unblockClient(Request $request)
    {
        $request->validate([
            'client_identifier' => 'required|string',
        ]);

        $clientIdentifier = $request->client_identifier;
        $blockedClients = Cache::get('blocked_clients', []);

        if (isset($blockedClients[$clientIdentifier])) {
            unset($blockedClients[$clientIdentifier]);
            Cache::put('blocked_clients', $blockedClients);

            Log::info('Client unblocked', [
                'client' => $clientIdentifier,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->with('success', 'クライアントのブロックを解除しました。');
        }

        return redirect()->back()
            ->with('error', '指定されたクライアントはブロックされていません。');
    }

    /**
     * APIトークンの使用統計を取得
     */
    private function getApiTokenStats(): array
    {
        return [
            'total_tokens' => ApiToken::count(),
            'active_tokens' => ApiToken::valid()->count(),
            'expired_tokens' => ApiToken::expired()->count(),
            'revoked_tokens' => ApiToken::revoked()->count(),
            'recently_used' => ApiToken::whereNotNull('last_used_at')
                ->where('last_used_at', '>=', now()->subHours(24))
                ->count(),
            'expiring_soon' => ApiToken::valid()
                ->get()
                ->filter(function ($token) {
                    return $token->isExpiringSoon(24);
                })
                ->count(),
        ];
    }

    /**
     * 最近のAPIリクエストログを取得
     */
    private function getRecentApiLogs(): array
    {
        // LaravelのログファイルからAPI関連のログを抽出
        // 実際の運用では、より効率的なログ集計システムを実装することを推奨

        $logs = [];

        try {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $logContent = file_get_contents($logPath);
                $lines = explode("\n", $logContent);

                // 最近のAPI関連ログを抽出（最大50件）
                $apiLogs = [];
                foreach (array_reverse($lines) as $line) {
                    if (strpos($line, 'API token') !== false ||
                        strpos($line, 'Rate limit') !== false ||
                        strpos($line, 'api.rate-limit') !== false) {
                        $apiLogs[] = $line;
                        if (count($apiLogs) >= 50) {
                            break;
                        }
                    }
                }

                $logs = $apiLogs;
            }
        } catch (\Exception $e) {
            Log::error('Failed to read API logs', ['error' => $e->getMessage()]);
        }

        return $logs;
    }

    /**
     * API使用状況レポートを生成
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'period' => 'required|string|in:hour,day,week,month',
        ]);

        $period = $request->period;

        // 期間に応じた統計を生成
        $stats = $this->generatePeriodStats($period);

        return response()->json([
            'success' => true,
            'period' => $period,
            'stats' => $stats,
            'generated_at' => now(),
        ]);
    }

    /**
     * 期間別の統計を生成
     */
    private function generatePeriodStats(string $period): array
    {
        $startDate = match ($period) {
            'hour' => now()->subHour(),
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            default => now()->subDay(),
        };

        return [
            'period_start' => $startDate,
            'period_end' => now(),
            'api_tokens_created' => ApiToken::where('created_at', '>=', $startDate)->count(),
            'api_tokens_used' => ApiToken::where('last_used_at', '>=', $startDate)->count(),
            'rate_limit_exceeded' => $this->countRateLimitExceeded($startDate),
        ];
    }

    /**
     * Rate limit超過回数をカウント
     */
    private function countRateLimitExceeded(\Carbon\Carbon $startDate): int
    {
        // ログファイルからRate limit超過のカウント
        // 実際の運用では、別途ログ集計テーブルを使用することを推奨
        $count = 0;

        try {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $logContent = file_get_contents($logPath);
                $count = substr_count($logContent, 'Rate limit exceeded');
            }
        } catch (\Exception $e) {
            // エラーが発生しても0を返す
        }

        return $count;
    }
}
