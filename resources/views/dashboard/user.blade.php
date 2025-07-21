<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                ダッシュボード
            </h2>
            <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                一般ユーザー
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 統計カード -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                    @if($stats['low_stock_products'] > 0)
                        <div class="mt-3 bg-yellow-400 bg-opacity-30 rounded-lg p-2">
                            <p class="text-sm font-medium">要注意商品があります</p>
                        </div>
                    @endif
                </div>

                <!-- 最新取引数 -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">最新取引件数</p>
                            <p class="text-3xl font-bold">{{ $stats['recent_transactions']->count() }}</p>
                        </div>
                        <div class="bg-green-400 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- メイン機能エリア -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- クイックアクション -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">クイックアクション</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <a href="/products/create" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                            <div class="bg-blue-500 rounded-full p-2 mr-4">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">商品を登録</p>
                                <p class="text-sm text-gray-600">新しい商品を追加</p>
                            </div>
                        </a>
                        
                        <a href="/transactions/create" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                            <div class="bg-green-500 rounded-full p-2 mr-4">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">売上・貸出を記録</p>
                                <p class="text-sm text-gray-600">取引を記録</p>
                            </div>
                        </a>

                        <a href="/products?low_stock=1" class="flex items-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition duration-200">
                            <div class="bg-yellow-500 rounded-full p-2 mr-4">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">在庫不足を確認</p>
                                <p class="text-sm text-gray-600">要注意商品を表示</p>
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
                                    <p class="text-sm text-gray-600">{{ $transaction->created_at->format('m/d H:i') }}</p>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-gray-500">まだ取引がありません</p>
                                <p class="text-sm text-gray-400 mt-1">商品の売上や貸出を記録してみましょう</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 基本機能アクセス -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-6">メニュー</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <a href="/products" class="flex flex-col items-center p-6 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                        <div class="bg-blue-500 rounded-full p-4 mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-800 text-center">商品一覧</h4>
                        <p class="text-sm text-gray-600 text-center mt-2">商品の検索・確認</p>
                    </a>

                    <a href="/transactions" class="flex flex-col items-center p-6 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                        <div class="bg-green-500 rounded-full p-4 mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-800 text-center">取引履歴</h4>
                        <p class="text-sm text-gray-600 text-center mt-2">売上・貸出の履歴</p>
                    </a>

                    <a href="/customers" class="flex flex-col items-center p-6 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200">
                        <div class="bg-purple-500 rounded-full p-4 mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-800 text-center">顧客管理</h4>
                        <p class="text-sm text-gray-600 text-center mt-2">顧客情報の管理</p>
                    </a>

                    <a href="/reports" class="flex flex-col items-center p-6 bg-orange-50 hover:bg-orange-100 rounded-lg transition duration-200">
                        <div class="bg-orange-500 rounded-full p-4 mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3h4v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-800 text-center">レポート</h4>
                        <p class="text-sm text-gray-600 text-center mt-2">Excel形式での出力</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>