@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">貸出レポート</h1>
        <a href="{{ route('reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            レポート一覧へ
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('reports.rentals') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">状態</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>全て</option>
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>貸出中</option>
                    <option value="returned" {{ $status == 'returned' ? 'selected' : '' }}>返却済</option>
                </select>
            </div>
            
            <div>
                <label for="group_by" class="block text-sm font-medium text-gray-700 mb-1">集計単位</label>
                <select name="group_by" id="group_by" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="daily" {{ $groupBy == 'daily' ? 'selected' : '' }}>日次</option>
                    <option value="weekly" {{ $groupBy == 'weekly' ? 'selected' : '' }}>週次</option>
                    <option value="monthly" {{ $groupBy == 'monthly' ? 'selected' : '' }}>月次</option>
                    <option value="yearly" {{ $groupBy == 'yearly' ? 'selected' : '' }}>年次</option>
                </select>
            </div>
            
            <div>
                <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">顧客</label>
                <select name="customer_id" id="customer_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">全て</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    集計
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <p class="text-sm text-blue-600 font-medium">貸出件数</p>
            <p class="text-2xl font-bold text-blue-800">{{ $transactions->count() }}件</p>
        </div>
        <div class="bg-orange-50 rounded-lg p-4">
            <p class="text-sm text-orange-600 font-medium">現在貸出中</p>
            <p class="text-2xl font-bold text-orange-800">{{ $transactions->whereNull('returned_at')->count() }}件</p>
        </div>
        <div class="bg-green-50 rounded-lg p-4">
            <p class="text-sm text-green-600 font-medium">返却済み</p>
            <p class="text-2xl font-bold text-green-800">{{ $transactions->whereNotNull('returned_at')->count() }}件</p>
        </div>
        <div class="bg-red-50 rounded-lg p-4">
            <p class="text-sm text-red-600 font-medium">期限超過</p>
            <p class="text-2xl font-bold text-red-800">
                {{ $transactions->whereNull('returned_at')->where('expected_return_date', '<', now())->count() }}件
            </p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4">
            <p class="text-sm text-purple-600 font-medium">貸出総額</p>
            <p class="text-2xl font-bold text-purple-800">¥{{ number_format($transactions->sum('total_amount')) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-800 text-white px-6 py-4">
                <h2 class="text-lg font-bold">期間別貸出</h2>
            </div>
            <div class="p-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">期間</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">件数</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($groupedData as $period => $data)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $period }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['count'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['total_quantity'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    ¥{{ number_format($data['total_amount']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-800 text-white px-6 py-4">
                <h2 class="text-lg font-bold">商品別貸出 TOP10</h2>
            </div>
            <div class="p-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">件数</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出中</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($productSummary->take(10) as $productName => $data)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $productName }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['count'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['total_quantity'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $activeCount = $transactions->where('product.name', $productName)
                                            ->whereNull('returned_at')->count();
                                    @endphp
                                    {{ $activeCount }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="bg-red-600 text-white px-6 py-4">
            <h2 class="text-lg font-bold">期限超過の貸出</h2>
        </div>
        <div class="p-4">
            @php
                $overdueRentals = $transactions->whereNull('returned_at')
                    ->where('expected_return_date', '<', now())
                    ->sortBy('expected_return_date');
            @endphp
            @if($overdueRentals->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出日</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">返却予定日</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">超過日数</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($overdueRentals->take(20) as $rental)
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
                                    {{ $rental->customer->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->product->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $rental->quantity }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($overdueRentals->count() > 20)
                    <p class="text-sm text-gray-500 mt-2">他 {{ $overdueRentals->count() - 20 }} 件の期限超過があります</p>
                @endif
            @else
                <p class="text-gray-500">期限超過の貸出はありません。</p>
            @endif
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-gray-800 text-white px-6 py-4">
            <h2 class="text-lg font-bold">顧客別貸出 TOP10</h2>
        </div>
        <div class="p-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出件数</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出中</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">返却済</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customerSummary->take(10) as $customerName => $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $customerName }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['count'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transactions->where('customer.name', $customerName)->whereNull('returned_at')->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transactions->where('customer.name', $customerName)->whereNotNull('returned_at')->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                ¥{{ number_format($data['total_amount']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection