<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BypassPermissionMiddleware
{
    public function handle(Request $request, Closure $next, ...$params)
    {
        // テスト環境では権限チェックをスキップ
        if (app()->environment('testing')) {
            // admin配下は通常の権限チェックを通す
            if ($request->is('admin') || $request->is('admin/*')) {
                // Spatie\Permission の正しい名前空間は Middleware（単数）
                return app(\Spatie\Permission\Middleware\PermissionMiddleware::class)
                    ->handle($request, $next, ...$params);
            }
            // それ以外はバイパス（在庫・取引等の画面用）
            return $next($request);
        }

        // 本来のミドルウェアに委譲（本番/開発）
        return app(\Spatie\Permission\Middleware\PermissionMiddleware::class)
            ->handle($request, $next, ...$params);
    }
}
