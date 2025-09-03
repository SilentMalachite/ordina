<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Notification;
use Native\Laravel\Menu\Menu as MenuMenu;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Process;
use App\Services\DesktopNotificationService;
use App\Services\DesktopFileService;
use App\Services\DesktopWindowService;

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
            ->minHeight(600)
            ->center();

        $this->setupMenu();
        $this->startQueueWorker();
        $this->scheduleNotifications();
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
                ->separator()
                ->link('products/create', '新規商品登録', 'CmdOrCtrl+Shift+P')
                ->link('customers/create', '新規顧客登録', 'CmdOrCtrl+Shift+C')
                ->link('transactions/create', '新規取引登録', 'CmdOrCtrl+Shift+T')
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
     * Schedule desktop notifications
     */
    protected function scheduleNotifications(): void
    {
        // 起動時のウェルカム通知
        Notification::title('Ordina 起動完了')
            ->message('在庫管理システムが正常に起動しました。')
            ->show();
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
            'upload_max_filesize' => '50M',
            'post_max_size' => '50M',
            'max_file_uploads' => '20',
        ];
    }
}
