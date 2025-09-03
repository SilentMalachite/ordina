@extends('layouts.app')

@section('title', '在庫アラート')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">在庫アラート</h1>
                    <div class="space-x-2">
                        <a href="{{ route('stock-alerts.settings') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            設定
                        </a>
                        <a href="{{ route('stock-alerts.history') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            履歴
                        </a>
                        <form method="POST" action="{{ route('stock-alerts.run-check') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                チェック実行
                            </button>
                        </form>
                    </div>
                </div>

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

                <!-- 統計情報 -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800">総商品数</h4>
                        <p class="text-2xl font-bold text-blue-600">{{ $statistics['total_products'] }}種類</p>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-yellow-800">低在庫商品</h4>
                        <p class="text-2xl font-bold text-yellow-600">{{ $statistics['low_stock_products'] }}種類</p>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-red-800">在庫切れ商品</h4>
                        <p class="text-2xl font-bold text-red-600">{{ $statistics['out_of_stock_products'] }}種類</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-800">アラート閾値</h4>
                        <p class="text-2xl font-bold text-gray-600">{{ $statistics['low_stock_threshold'] }}個以下</p>
                    </div>
                </div>

                <!-- 在庫切れ商品 -->
                @if(count($out_of_stock_products) > 0)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-red-600">⚠️ 在庫切れ商品</h2>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead class="bg-red-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">商品コード</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">商品名</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">在庫数</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">単価</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($out_of_stock_products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $product['product_code'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $product['name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">
                                            {{ $product['stock_quantity'] }}個
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            ¥{{ number_format($product['unit_price']) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="{{ route('products.show', $product['id']) }}" 
                                               class="text-blue-600 hover:text-blue-900">詳細</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- 低在庫商品 -->
                @if(count($low_stock_products) > 0)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-yellow-600">⚠️ 低在庫商品</h2>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead class="bg-yellow-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 uppercase tracking-wider">商品コード</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 uppercase tracking-wider">商品名</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 uppercase tracking-wider">在庫数</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 uppercase tracking-wider">単価</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 uppercase tracking-wider">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($low_stock_products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $product['product_code'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $product['name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 font-bold">
                                            {{ $product['stock_quantity'] }}個
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            ¥{{ number_format($product['unit_price']) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="{{ route('products.show', $product['id']) }}" 
                                               class="text-blue-600 hover:text-blue-900">詳細</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- 正常な商品 -->
                @if(count($out_of_stock_products) == 0 && count($low_stock_products) == 0)
                <div class="text-center py-8">
                    <div class="text-green-500 text-6xl mb-4">✅</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">在庫状況は正常です</h3>
                    <p class="text-gray-500">現在、在庫アラートの対象となる商品はありません。</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection