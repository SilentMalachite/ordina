<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // 認証されていない場合はログインページにリダイレクト
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 統一された権限チェック
        if (!$this->permissionService->checkPermission($permission)) {
            abort(403, 'この操作を実行する権限がありません。');
        }

        return $next($request);
    }
}