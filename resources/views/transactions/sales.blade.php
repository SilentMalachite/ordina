@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">売上一覧</h1>
        <div>
            <span class="text-2xl font-bold text-green-600">売上総額: ¥{{ number_format($totalSales) }}</span>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">取引日</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品コード</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">単価</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                    <th class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($sales as $sale)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sale->transaction_date->format('Y/m/d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('customers.show', $sale->customer_id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $sale->customer->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('products.show', $sale->product_id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $sale->product->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sale->product->product_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sale->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ¥{{ number_format($sale->unit_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            ¥{{ number_format($sale->total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('transactions.show', $sale) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">詳細</a>
                            <a href="{{ route('transactions.edit', $sale) }}" class="text-yellow-600 hover:text-yellow-900">編集</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            売上データがありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($sales->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="6" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                            ページ小計:
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-900">
                            ¥{{ number_format($sales->sum('total_amount')) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $sales->links() }}
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('transactions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            取引一覧へ戻る
        </a>
        <a href="{{ route('reports.sales') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded ml-4">
            売上レポート
        </a>
    </div>
</div>
@endsection