<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // テスト環境ではパーミッション系ミドルウェアをバイパス
        if ($this->app->environment('testing')) {
            $router = $this->app['router'];
            // permission のみバイパス（role系は従来どおり制御）
            $router->aliasMiddleware('permission', \App\Http\Middleware\BypassPermissionMiddleware::class);
        }
    }
}
