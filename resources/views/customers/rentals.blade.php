@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">{{ $customer->name }} - 貸出状況</h1>
            <a href="{{ route('customers.show', $customer) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                顧客詳細へ戻る
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if($activeRentals->count() > 0)
            <div class="mb-8 bg-white shadow-md rounded-lg overflow-hidden">
                <div class="bg-orange-500 text-white px-6 py-4">
                    <h2 class="text-xl font-bold">現在貸出中の商品</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出日</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">返却予定日</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">経過日数</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態</th>
                            <th class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($activeRentals as $rental)
                            <tr class="{{ $rental->expected_return_date < now() ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $rental->product->product_code }}<br>
                                    <span class="text-xs text-gray-500">{{ $rental->product->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->transaction_date->format('Y/m/d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->expected_return_date ? $rental->expected_return_date->format('Y/m/d') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->transaction_date->diffInDays(now()) }}日
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
                                    <form action="{{ route('customers.return-item', [$customer, $rental]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs"
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
        @else
            <div class="mb-8 bg-white shadow-md rounded-lg overflow-hidden p-6">
                <p class="text-gray-500 text-center">現在貸出中の商品はありません。</p>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-600 text-white px-6 py-4">
                <h2 class="text-xl font-bold">返却済み履歴</h2>
            </div>
            @if($rentalHistory->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出日</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">返却日</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出期間</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rentalHistory as $rental)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $rental->product->product_code }}<br>
                                    <span class="text-xs text-gray-500">{{ $rental->product->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->transaction_date->format('Y/m/d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->returned_at->format('Y/m/d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->transaction_date->diffInDays($rental->returned_at) }}日間
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ¥{{ number_format($rental->total_amount) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $rentalHistory->links() }}
                </div>
            @else
                <div class="p-6">
                    <p class="text-gray-500 text-center">返却済みの履歴はありません。</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection