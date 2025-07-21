@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">取引一覧</h1>
        <a href="{{ route('transactions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            新規取引作成
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6 p-6">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">取引タイプ</label>
                <select name="type" id="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">全て</option>
                    <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>売上</option>
                    <option value="rental" {{ request('type') == 'rental' ? 'selected' : '' }}>貸出</option>
                </select>
            </div>
            
            <div>
                <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">顧客</label>
                <select name="customer_id" id="customer_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">全て</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">商品</label>
                <select name="product_id" id="product_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">全て</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    検索
                </button>
                <a href="{{ route('transactions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    リセット
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">取引日</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイプ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態</th>
                    <th class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->transaction_date->format('Y/m/d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($transaction->type === 'sale')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    売上
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    貸出
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('customers.show', $transaction->customer_id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $transaction->customer->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('products.show', $transaction->product_id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $transaction->product->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ¥{{ number_format($transaction->total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($transaction->type === 'rental')
                                @if($transaction->returned_at)
                                    <span class="text-green-600">返却済</span>
                                @elseif($transaction->expected_return_date < now())
                                    <span class="text-red-600">期限超過</span>
                                @else
                                    <span class="text-orange-600">貸出中</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('transactions.show', $transaction) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">詳細</a>
                            <a href="{{ route('transactions.edit', $transaction) }}" class="text-yellow-600 hover:text-yellow-900">編集</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            取引データがありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $transactions->links() }}
        </div>
    </div>

    <div class="mt-6 flex justify-center space-x-4">
        <a href="{{ route('transactions.sales') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            売上一覧
        </a>
        <a href="{{ route('transactions.rentals') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            貸出一覧
        </a>
    </div>
</div>
@endsection