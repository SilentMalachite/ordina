@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">低在庫アラート</h1>
        <a href="{{ route('inventory.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            在庫一覧へ
        </a>
    </div>

    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong>警告:</strong> 以下の商品は在庫が10個以下になっています。早急な補充が必要です。
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品コード</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品名</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">現在の在庫数</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">単価</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最終取引日</th>
                    <th class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($lowStockProducts as $product)
                    <tr class="bg-yellow-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $product->product_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->stock_quantity <= 5)
                                <span class="text-red-600 font-bold text-lg">{{ $product->stock_quantity }}</span>
                                <span class="text-red-600 text-xs">（危険）</span>
                            @else
                                <span class="text-orange-600 font-bold text-lg">{{ $product->stock_quantity }}</span>
                                <span class="text-orange-600 text-xs">（要注意）</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ¥{{ number_format($product->unit_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($product->transactions->count() > 0)
                                {{ $product->transactions->first()->transaction_date->format('Y/m/d') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('inventory.adjustment.create') }}?product_id={{ $product->id }}" 
                               class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs">
                                在庫調整
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            低在庫の商品はありません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($lowStockProducts->count() > 0)
        <div class="mt-6 bg-white shadow-md rounded-lg overflow-hidden p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">補充推奨数量</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($lowStockProducts as $product)
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <div>
                            <span class="font-medium">{{ $product->name }}</span>
                            <span class="text-sm text-gray-500 ml-2">（現在: {{ $product->stock_quantity }}）</span>
                        </div>
                        <div class="text-right">
                            <span class="text-green-600 font-bold">
                                推奨補充数: {{ max(30 - $product->stock_quantity, 20) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection