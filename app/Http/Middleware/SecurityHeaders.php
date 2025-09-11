<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HTTPS強制 (本番環境のみ)
        if (config('app.env') === 'production' && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        // セキュリティヘッダーを設定
        $this->setSecurityHeaders($response);

        return $response;
    }

    /**
     * セキュリティヘッダーを設定
     */
    private function setSecurityHeaders(Response $response): void
    {
        // HTTPS Strict Transport Security (HSTS)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // X-Frame-Options (クリックジャッキング対策)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options (MIMEタイプスニッフィング対策)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer-Policy (リファラーポリシー)
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy (機能アクセス制御)
        $response->headers->set('Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        // X-Permitted-Cross-Domain-Policies (Flashクロスドメインポリシー)
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Cross-Origin-Embedder-Policy (COEP)
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');

        // Cross-Origin-Opener-Policy (COOP)
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Cross-Origin-Resource-Policy (CORP)
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
    }
}



