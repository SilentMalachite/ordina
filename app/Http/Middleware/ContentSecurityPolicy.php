<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // CSPが有効な場合のみヘッダーを設定
        if (config('ordina.security.csp_enabled', true)) {
            $this->setContentSecurityPolicy($response, $request);
        }

        return $response;
    }

    /**
     * Content Security Policyヘッダーを設定
     */
    private function setContentSecurityPolicy(Response $response, Request $request): void
    {
        $csp = [];

        // デフォルトソース
        $csp[] = "default-src 'self'";

        // スクリプトソース
        $csp[] = "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com";

        // スタイルソース
        $csp[] = "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com";

        // フォントソース
        $csp[] = "font-src 'self' https://fonts.gstatic.com";

        // 画像ソース
        $csp[] = "img-src 'self' data: https:";

        // 接続ソース
        $csp[] = "connect-src 'self'";

        // オブジェクトソース
        $csp[] = "object-src 'none'";

        // ベースURI
        $csp[] = "base-uri 'self'";

        // フォームアクション
        $csp[] = "form-action 'self'";

        // フレームソース
        $csp[] = "frame-src 'none'";

        // CSPレポートURI
        $csp[] = "report-uri /csp-report";

        $cspHeader = implode('; ', $csp);

        if (config('ordina.security.csp_report_only', false)) {
            // Report-Onlyモード
            $response->headers->set('Content-Security-Policy-Report-Only', $cspHeader);
        } else {
            // 強制モード
            $response->headers->set('Content-Security-Policy', $cspHeader);
        }
    }
}

