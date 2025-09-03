<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $ability  必要な権限
     */
    public function handle(Request $request, Closure $next, ?string $ability = null): Response
    {
        // Authorizationヘッダーからトークンを取得
        $token = $this->extractTokenFromRequest($request);

        if (!$token) {
            Log::warning('API request without token', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API token is required'
            ], 401);
        }

        // トークンの検証
        $apiToken = ApiToken::findByToken($token);

        if (!$apiToken) {
            Log::warning('Invalid API token used', [
                'token_hash' => hash('sha256', $token),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid API token'
            ], 401);
        }

        // トークンの有効性チェック
        if (!$apiToken->isValid()) {
            $reason = $apiToken->revoked ? 'revoked' : 'expired';

            Log::warning('Invalid API token state', [
                'token_id' => $apiToken->id,
                'reason' => $reason,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'error' => 'Unauthorized',
                'message' => "API token is {$reason}"
            ], 401);
        }

        // 権限チェック（指定されている場合）
        if ($ability && !$apiToken->can($ability)) {
            Log::warning('API token lacks required ability', [
                'token_id' => $apiToken->id,
                'required_ability' => $ability,
                'token_abilities' => $apiToken->abilities,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // トークンの使用を記録
        $apiToken->recordUsage();

        // リクエストにトークン情報を追加
        $request->merge([
            'api_token' => $apiToken,
            'api_user' => $apiToken->user,
        ]);

        Log::info('Valid API token used', [
            'token_id' => $apiToken->id,
            'user_id' => $apiToken->user_id,
            'ability' => $ability,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);

        return $next($request);
    }

    /**
     * リクエストからトークンを抽出
     */
    private function extractTokenFromRequest(Request $request): ?string
    {
        // AuthorizationヘッダーからBearerトークンを取得
        $authHeader = $request->header('Authorization');

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // クエリパラメータから取得（開発・テスト用）
        if ($request->has('api_token')) {
            return $request->api_token;
        }

        // フォームパラメータから取得
        if ($request->has('token')) {
            return $request->token;
        }

        return null;
    }
}
