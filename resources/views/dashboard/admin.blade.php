<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                管理者ダッシュボード
            </h2>
            <div class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                管理者
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 統計カード -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- 商品数 -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">総商品数</p>
                            <p class="text-3xl font-bold">{{ $stats['total_products'] }}</p>
                        </div>
                        <div class="bg-blue-400 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- 顧客数 -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">顧客数</p>
                            <p class="text-3xl font-bold">{{ $stats['total_customers'] }}</p>
                        </div>
                        <div class="bg-green-400 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- 在庫不足商品 -->
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">在庫不足商品</p>
                            <p class="text-3xl font-bold">{{ $stats['low_stock_products'] }}</p>
                        </div>
                        <div class="bg-yellow-400 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- 総売上 -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">総売上</p>
                            <p class="text-3xl font-bold">¥{{ number_format($stats['total_revenue']) }}</p>
                        </div>
                        <div class="bg-purple-400 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 管理者専用機能 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- クイックアクション -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">管理者機能</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <a href="/admin/users" class="flex items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition duration-200">
                            <div class="bg-red-500 rounded-full p-2 mr-4">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">ユーザー管理</p>
                                <p class="text-sm text-gray-600">ユーザーの追加・編集・削除</p>
                            </div>
                        </a>
                        
                        <a href="/admin/data" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                            <div class="bg-blue-500 rounded-full p-2 mr-4">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">データ管理</p>
                                <p class="text-sm text-gray-600">バックアップ・復元・削除</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- 最近の取引 -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">最近の取引</h3>
                    <div class="space-y-4">
                        @if($stats['recent_transactions']->count() > 0)
                            @foreach($stats['recent_transactions'] as $transaction)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">{{ $transaction->product->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $transaction->customer->name ?? '不明' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-gray-800">{{ $transaction->quantity }}個</p>
                                    <p class="text-sm text-gray-600">{{ $transaction->created_at->format('m/d') }}</p>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-gray-500 text-center py-4">まだ取引がありません</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 基本機能アクセス -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-6">基本機能</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="/products" class="flex flex-col items-center p-6 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                        <div class="bg-blue-500 rounded-full p-4 mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-800 text-center">商品管理</h4>
                        <p class="text-sm text-gray-600 text-center mt-2">商品の登録・編集・削除</p>
                    </a>

                    <a href="/transactions" class="flex flex-col items-center p-6 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                        <div class="bg-green-500 rounded-full p-4 mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-800 text-center">売上・貸出管理</h4>
                        <p class="text-sm text-gray-600 text-center mt-2">取引の登録・確認</p>
                    </a>

                    <a href="/reports" class="flex flex-col items-center p-6 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200">
                        <div class="bg-purple-500 rounded-full p-4 mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3h4v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-800 text-center">レポート出力</h4>
                        <p class="text-sm text-gray-600 text-center mt-2">Excel形式での出力</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>