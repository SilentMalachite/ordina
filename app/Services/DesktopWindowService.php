<?php

namespace App\Services;

use Native\Laravel\Facades\Window;

class DesktopWindowService
{
    /**
     * 新しいウィンドウを開く
     */
    public function openNewWindow(string $url, string $title = 'Ordina', array $options = []): void
    {
        $defaultOptions = [
            'width' => 800,
            'height' => 600,
            'minWidth' => 400,
            'minHeight' => 300,
            'resizable' => true,
            'maximizable' => true,
            'minimizable' => true,
            'closable' => true,
        ];

        $options = array_merge($defaultOptions, $options);

        Window::open($url)
            ->title($title)
            ->width($options['width'])
            ->height($options['height'])
            ->minWidth($options['minWidth'])
            ->minHeight($options['minHeight'])
            ->resizable($options['resizable'])
            ->maximizable($options['maximizable'])
            ->minimizable($options['minimizable'])
            ->closable($options['closable']);
    }

    /**
     * 商品詳細ウィンドウを開く
     */
    public function openProductWindow(int $productId): void
    {
        $this->openNewWindow(
            "/products/{$productId}",
            '商品詳細',
            [
                'width' => 600,
                'height' => 500,
                'minWidth' => 500,
                'minHeight' => 400
            ]
        );
    }

    /**
     * 顧客詳細ウィンドウを開く
     */
    public function openCustomerWindow(int $customerId): void
    {
        $this->openNewWindow(
            "/customers/{$customerId}",
            '顧客詳細',
            [
                'width' => 600,
                'height' => 500,
                'minWidth' => 500,
                'minHeight' => 400
            ]
        );
    }

    /**
     * 取引詳細ウィンドウを開く
     */
    public function openTransactionWindow(int $transactionId): void
    {
        $this->openNewWindow(
            "/transactions/{$transactionId}",
            '取引詳細',
            [
                'width' => 700,
                'height' => 600,
                'minWidth' => 600,
                'minHeight' => 500
            ]
        );
    }

    /**
     * レポートウィンドウを開く
     */
    public function openReportWindow(string $reportType): void
    {
        $urls = [
            'sales' => '/reports/sales',
            'inventory' => '/reports/inventory',
            'customers' => '/reports/customers',
            'rentals' => '/reports/rentals'
        ];

        $titles = [
            'sales' => '売上レポート',
            'inventory' => '在庫レポート',
            'customers' => '顧客レポート',
            'rentals' => '貸出レポート'
        ];

        if (isset($urls[$reportType])) {
            $this->openNewWindow(
                $urls[$reportType],
                $titles[$reportType],
                [
                    'width' => 1000,
                    'height' => 700,
                    'minWidth' => 800,
                    'minHeight' => 600
                ]
            );
        }
    }

    /**
     * 管理者ウィンドウを開く
     */
    public function openAdminWindow(string $section = 'dashboard'): void
    {
        $urls = [
            'dashboard' => '/admin',
            'users' => '/admin/users',
            'data' => '/admin/data-management',
            'settings' => '/admin/system-settings',
            'logs' => '/admin/system-logs'
        ];

        $titles = [
            'dashboard' => '管理者ダッシュボード',
            'users' => 'ユーザー管理',
            'data' => 'データ管理',
            'settings' => 'システム設定',
            'logs' => 'システムログ'
        ];

        if (isset($urls[$section])) {
            $this->openNewWindow(
                $urls[$section],
                $titles[$section],
                [
                    'width' => 1200,
                    'height' => 800,
                    'minWidth' => 1000,
                    'minHeight' => 700
                ]
            );
        }
    }

    /**
     * メインウィンドウを最大化
     */
    public function maximizeMainWindow(): void
    {
        Window::current()->maximize();
    }

    /**
     * メインウィンドウを最小化
     */
    public function minimizeMainWindow(): void
    {
        Window::current()->minimize();
    }

    /**
     * メインウィンドウを閉じる
     */
    public function closeMainWindow(): void
    {
        Window::current()->close();
    }

    /**
     * ウィンドウの位置を中央に設定
     */
    public function centerWindow(): void
    {
        Window::current()->center();
    }

    /**
     * ウィンドウのサイズを設定
     */
    public function resizeWindow(int $width, int $height): void
    {
        Window::current()->resize($width, $height);
    }

    /**
     * ウィンドウの位置を設定
     */
    public function moveWindow(int $x, int $y): void
    {
        Window::current()->move($x, $y);
    }
}