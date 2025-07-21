@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">商品詳細</h1>
            <div class="space-x-2">
                <a href="{{ route('products.edit', $product) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                    編集
                </a>
                <a href="{{ route('products.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    一覧へ戻る
                </a>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">商品コード</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $product->product_code }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">商品名</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $product->name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">在庫数</h3>
                        <p class="mt-1 text-lg font-semibold {{ $product->stock_quantity <= 10 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $product->stock_quantity }}
                            @if($product->stock_quantity <= 10)
                                <span class="text-sm text-red-600">（低在庫）</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">単価</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">¥{{ number_format($product->unit_price) }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">売値</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">¥{{ number_format($product->selling_price) }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">利益率</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            {{ $product->unit_price > 0 ? round((($product->selling_price - $product->unit_price) / $product->unit_price) * 100, 1) : 0 }}%
                        </p>
                    </div>
                </div>
                @if($product->description)
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-500">説明</h3>
                        <p class="mt-1 text-gray-900">{{ $product->description }}</p>
                    </div>
                @endif
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">登録日時</h3>
                        <p class="mt-1 text-gray-900">{{ $product->created_at->format('Y年m月d日 H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">更新日時</h3>
                        <p class="mt-1 text-gray-900">{{ $product->updated_at->format('Y年m月d日 H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">最近の取引履歴</h2>
                @if($recentTransactions->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">取引日</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイプ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->transaction_date->format('Y/m/d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($transaction->type === 'sale')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">売上</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">貸出</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->customer->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ¥{{ number_format($transaction->total_amount) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">取引履歴はありません。</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection