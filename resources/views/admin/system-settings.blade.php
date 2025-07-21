@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">システム設定</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 在庫管理設定 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">在庫管理設定</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            低在庫警告しきい値
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   value="{{ $settings['low_stock_threshold'] }}" 
                                   class="w-20 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   readonly>
                            <span class="text-sm text-gray-600">個以下で警告表示</span>
                        </div>
                        <p class="text-gray-500 text-sm mt-1">
                            在庫数がこの値以下になった場合、ダッシュボードで警告が表示されます
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 締め日設定 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">締め日設定</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            デフォルト締め日
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   value="{{ $settings['default_closing_day'] }}" 
                                   class="w-20 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   readonly>
                            <span class="text-sm text-gray-600">日</span>
                        </div>
                        <p class="text-gray-500 text-sm mt-1">
                            レポート作成時のデフォルト締め日として使用されます
                        </p>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('admin.closing-dates') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm underline">
                            締め日設定を管理 →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- バックアップ設定 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">バックアップ設定</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            自動バックアップ頻度
                        </label>
                        <div class="flex items-center space-x-2">
                            <select class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" disabled>
                                <option value="daily" {{ $settings['backup_frequency'] == 'daily' ? 'selected' : '' }}>毎日</option>
                                <option value="weekly" {{ $settings['backup_frequency'] == 'weekly' ? 'selected' : '' }}>毎週</option>
                                <option value="monthly" {{ $settings['backup_frequency'] == 'monthly' ? 'selected' : '' }}>毎月</option>
                                <option value="manual" {{ $settings['backup_frequency'] == 'manual' ? 'selected' : '' }}>手動のみ</option>
                            </select>
                        </div>
                        <p class="text-gray-500 text-sm mt-1">
                            現在は手動バックアップのみサポートしています
                        </p>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('admin.data-management') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm underline">
                            データ管理画面へ →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ユーザー管理設定 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">ユーザー管理</h2>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-2">
                            ユーザーの作成、編集、削除を管理できます
                        </p>
                        <a href="{{ route('admin.users') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                            ユーザー管理画面
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- システム情報 -->
    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">システム情報</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="font-medium text-gray-800 mb-2">アプリケーション</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><strong>名前:</strong> Ordina</p>
                        <p><strong>バージョン:</strong> 1.0.0</p>
                        <p><strong>環境:</strong> {{ app()->environment() }}</p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="font-medium text-gray-800 mb-2">Laravel</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><strong>バージョン:</strong> {{ app()->version() }}</p>
                        <p><strong>PHP:</strong> {{ PHP_VERSION }}</p>
                        <p><strong>タイムゾーン:</strong> {{ config('app.timezone') }}</p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="font-medium text-gray-800 mb-2">NativePHP</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><strong>アプリID:</strong> {{ config('nativephp.app_id') }}</p>
                        <p><strong>バージョン:</strong> {{ config('nativephp.version') }}</p>
                        <p><strong>更新機能:</strong> {{ config('nativephp.updater.enabled') ? '有効' : '無効' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 注意事項 -->
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">設定の変更について</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>
                        現在のバージョンでは、一部の設定項目は表示のみで変更はできません。
                        将来のアップデートで設定変更機能が追加される予定です。
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection