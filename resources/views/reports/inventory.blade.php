@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">在庫レポート</h1>
        <div class="space-x-2">
            <a href="{{ route('reports.export.inventory') }}" 
               class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                CSV出力
            </a>
            <a href="{{ route('reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                レポート一覧へ
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('reports.inventory') }}" class="flex items-end gap-4">
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="low_stock_only" value="1" 
                        {{ $lowStockOnly ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-600">低在庫商品のみ表示</span>
                </label>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                フィルター
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <p class="text-sm text-blue-600 font-medium">総商品数</p>
            <p class="text-2xl font-bold text-blue-800">{{ $totalProducts }}品</p>
        </div>
        <div class="bg-green-50 rounded-lg p-4">
            <p class="text-sm text-green-600 font-medium">在庫総価値</p>
            <p class="text-2xl font-bold text-green-800">¥{{ number_format($totalStockValue) }}</p>
        </div>
        <div class="bg-orange-50 rounded-lg p-4">
            <p class="text-sm text-orange-600 font-medium">低在庫商品</p>
            <p class="text-2xl font-bold text-orange-800">{{ $lowStockCount }}品</p>
            <p class="text-xs text-orange-600">在庫10以下</p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4">
            <p class="text-sm text-purple-600 font-medium">平均在庫数</p>
            <p class="text-2xl font-bold text-purple-800">
                {{ $totalProducts > 0 ? number_format($products->avg('stock_quantity'), 1) : 0 }}
            </p>
        </div>
    </div>

    @if($lowStockCount > 0 && !$lowStockOnly)
        <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-orange-700">
                        {{ $lowStockCount }}品の商品が低在庫状態です。
                        <a href="{{ route('reports.inventory', ['low_stock_only' => 1]) }}" class="font-medium underline">
                            低在庫商品のみ表示
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品コード</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品名</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">在庫数</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">単価</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">売値</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">在庫価値</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最近の取引</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                    <tr class="{{ $product->stock_quantity <= 10 ? 'bg-orange-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->product_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $product->stock_quantity <= 10 ? 'text-orange-600 font-bold' : 'text-gray-900' }}">
                            {{ $product->stock_quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ¥{{ number_format($product->unit_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ¥{{ number_format($product->selling_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            ¥{{ number_format($product->stock_quantity * $product->unit_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($product->transactions->count() > 0)
                                <div>
                                    @foreach($product->transactions->take(3) as $transaction)
                                        <div class="text-xs">
                                            {{ $transaction->transaction_date->format('m/d') }}
                                            {{ $transaction->type === 'sale' ? '売' : '貸' }}
                                            {{ $transaction->quantity }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">取引なし</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($product->stock_quantity == 0)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    在庫切れ
                                </span>
                            @elseif($product->stock_quantity <= 10)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                    低在庫
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    正常
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            商品データがありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">在庫状態の内訳</h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">正常在庫（11以上）</span>
                    <span class="font-semibold">{{ $products->where('stock_quantity', '>', 10)->count() }}品</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">低在庫（1～10）</span>
                    <span class="font-semibold text-orange-600">
                        {{ $products->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count() }}品
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">在庫切れ（0）</span>
                    <span class="font-semibold text-red-600">{{ $products->where('stock_quantity', 0)->count() }}品</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">在庫価値TOP5</h3>
            <div class="space-y-2">
                @foreach($products->sortByDesc(function($p) { return $p->stock_quantity * $p->unit_price; })->take(5) as $product)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ Str::limit($product->name, 30) }}</span>
                        <span class="font-semibold">¥{{ number_format($product->stock_quantity * $product->unit_price) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection