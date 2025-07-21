@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">貸出一覧</h1>
        <div class="text-sm">
            <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded mr-2">
                貸出中: {{ $activeRentals->count() }}件
            </span>
            <span class="px-3 py-1 bg-red-100 text-red-800 rounded">
                期限超過: {{ $overdueRentals->count() }}件
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if($overdueRentals->count() > 0)
        <div class="mb-8 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-red-600 text-white px-6 py-4">
                <h2 class="text-xl font-bold">期限超過の貸出</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出日</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">返却予定日</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">超過日数</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                        <th class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($overdueRentals as $rental)
                        <tr class="bg-red-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $rental->transaction_date->format('Y/m/d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">
                                {{ $rental->expected_return_date->format('Y/m/d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">
                                {{ $rental->expected_return_date->diffInDays(now()) }}日
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="{{ route('customers.show', $rental->customer_id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $rental->customer->name }}
                                </a>
                                @if($rental->customer->phone)
                                    <br><span class="text-xs text-gray-500">{{ $rental->customer->phone }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $rental->product->name }}
                                <br><span class="text-xs text-gray-500">{{ $rental->product->product_code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $rental->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('transactions.show', $rental) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">詳細</a>
                                <form action="{{ route('transactions.return', $rental) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 font-medium"
                                        onclick="return confirm('この商品を返却済みにしますか？')">
                                        返却処理
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-blue-600 text-white px-6 py-4">
            <h2 class="text-xl font-bold">現在の貸出</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出日</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">返却予定日</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態</th>
                    <th class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($activeRentals as $rental)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $rental->transaction_date->format('Y/m/d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $rental->expected_return_date ? $rental->expected_return_date->format('Y/m/d') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('customers.show', $rental->customer_id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $rental->customer->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('products.show', $rental->product_id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $rental->product->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $rental->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ¥{{ number_format($rental->total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($rental->expected_return_date < now())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    期限超過
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    貸出中
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('transactions.show', $rental) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">詳細</a>
                            <form action="{{ route('transactions.return', $rental) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 font-medium"
                                    onclick="return confirm('この商品を返却済みにしますか？')">
                                    返却処理
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            現在貸出中の商品はありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('transactions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            取引一覧へ戻る
        </a>
        <a href="{{ route('reports.rentals') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded ml-4">
            貸出レポート
        </a>
    </div>
</div>
@endsection