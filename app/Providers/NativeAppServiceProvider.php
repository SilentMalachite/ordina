<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Menu\Menu as MenuMenu;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Process;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open()
            ->title('Ordina - 在庫管理システム')
            ->width(1200)
            ->height(800)
            ->minWidth(800)
            ->minHeight(600);

        $this->setupMenu();
        $this->startQueueWorker();
    }

    /**
     * Setup the application menu
     */
    protected function setupMenu(): void
    {
        Menu::new()
            ->appMenu(
                MenuMenu::new('Ordina')
                    ->about()
                    ->separator()
                    ->hide()
                    ->hideOthers()
                    ->showAll()
                    ->separator()
                    ->quit()
            )
            ->submenu('ファイル', MenuMenu::new()
                ->link('dashboard', 'ダッシュボード', 'CmdOrCtrl+D')
                ->separator()
                ->link('products', '商品管理', 'CmdOrCtrl+P')
                ->link('customers', '顧客管理', 'CmdOrCtrl+C')
                ->link('transactions', '取引管理', 'CmdOrCtrl+T')
                ->link('inventory', '在庫管理', 'CmdOrCtrl+I')
                ->separator()
                ->link('reports', 'レポート', 'CmdOrCtrl+R')
                ->link('import', 'インポート', 'CmdOrCtrl+Shift+I')
            )
            ->submenu('管理', MenuMenu::new()
                ->link('admin', '管理者ダッシュボード', 'CmdOrCtrl+Shift+A')
                ->separator()
                ->link('admin/users', 'ユーザー管理', 'CmdOrCtrl+U')
                ->link('admin/data-management', 'データ管理', 'CmdOrCtrl+Shift+D')
                ->link('admin/closing-dates', '締め日設定', 'CmdOrCtrl+Shift+C')
                ->separator()
                ->link('admin/system-settings', 'システム設定', 'CmdOrCtrl+,')
                ->link('admin/system-logs', 'システムログ', 'CmdOrCtrl+Shift+L')
            )
            ->submenu('表示', MenuMenu::new()
                ->reload()
                ->forceReload()
                ->toggleDevTools()
                ->separator()
                ->resetZoom()
                ->zoomIn()
                ->zoomOut()
                ->separator()
                ->togglefullscreen()
            )
            ->submenu('ウィンドウ', MenuMenu::new()
                ->minimize()
                ->close()
            )
            ->register();
    }

    /**
     * Start the queue worker in the background
     */
    protected function startQueueWorker(): void
    {
        Process::run('php artisan queue:work --sleep=3 --tries=3 --max-time=3600');
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'max_execution_time' => '0',
            'max_input_time' => '0',
        ];
    }
}
