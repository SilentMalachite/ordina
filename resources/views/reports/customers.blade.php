@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">顧客レポート</h1>
        <a href="{{ route('reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            レポート一覧へ
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('reports.customers') }}" class="flex items-end gap-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                集計
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-purple-50 rounded-lg p-4">
            <p class="text-sm text-purple-600 font-medium">アクティブ顧客数</p>
            <p class="text-2xl font-bold text-purple-800">
                {{ $customerStats->filter(function($stat) { return $stat['sales_count'] + $stat['rentals_count'] > 0; })->count() }}人
            </p>
        </div>
        <div class="bg-green-50 rounded-lg p-4">
            <p class="text-sm text-green-600 font-medium">売上総額</p>
            <p class="text-2xl font-bold text-green-800">
                ¥{{ number_format($customerStats->sum('total_sales')) }}
            </p>
        </div>
        <div class="bg-blue-50 rounded-lg p-4">
            <p class="text-sm text-blue-600 font-medium">貸出総額</p>
            <p class="text-2xl font-bold text-blue-800">
                ¥{{ number_format($customerStats->sum('total_rentals')) }}
            </p>
        </div>
        <div class="bg-orange-50 rounded-lg p-4">
            <p class="text-sm text-orange-600 font-medium">現在貸出中</p>
            <p class="text-2xl font-bold text-orange-800">
                {{ $customerStats->sum('active_rentals') }}件
            </p>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-gray-800 text-white px-6 py-4">
            <h2 class="text-lg font-bold">顧客別取引実績</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客名</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイプ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">売上件数</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">売上金額</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出件数</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出金額</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出中</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">合計金額</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customerStats as $stat)
                        @php
                            $totalAmount = $stat['total_sales'] + $stat['total_rentals'];
                            $totalTransactions = $stat['sales_count'] + $stat['rentals_count'];
                        @endphp
                        <tr class="{{ $totalTransactions == 0 ? 'bg-gray-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="{{ route('customers.show', $stat['customer']->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $stat['customer']->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($stat['customer']->type === 'company')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        法人
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        個人
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $stat['sales_count'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                ¥{{ number_format($stat['total_sales']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $stat['rentals_count'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                                ¥{{ number_format($stat['total_rentals']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($stat['active_rentals'] > 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                        {{ $stat['active_rentals'] }}件
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                ¥{{ number_format($totalAmount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="{{ route('transactions.index', ['customer_id' => $stat['customer']->id]) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                    取引履歴
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">売上TOP5</h3>
            <div class="space-y-3">
                @foreach($customerStats->sortByDesc('total_sales')->take(5) as $index => $stat)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-lg font-bold text-gray-400 mr-3">{{ $index + 1 }}</span>
                            <span class="text-sm text-gray-600">{{ Str::limit($stat['customer']->name, 20) }}</span>
                        </div>
                        <span class="font-semibold text-green-600">¥{{ number_format($stat['total_sales']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">貸出TOP5</h3>
            <div class="space-y-3">
                @foreach($customerStats->sortByDesc('total_rentals')->take(5) as $index => $stat)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-lg font-bold text-gray-400 mr-3">{{ $index + 1 }}</span>
                            <span class="text-sm text-gray-600">{{ Str::limit($stat['customer']->name, 20) }}</span>
                        </div>
                        <span class="font-semibold text-blue-600">¥{{ number_format($stat['total_rentals']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">取引総額TOP5</h3>
            <div class="space-y-3">
                @foreach($customerStats->sortByDesc(function($stat) { return $stat['total_sales'] + $stat['total_rentals']; })->take(5) as $index => $stat)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-lg font-bold text-gray-400 mr-3">{{ $index + 1 }}</span>
                            <span class="text-sm text-gray-600">{{ Str::limit($stat['customer']->name, 20) }}</span>
                        </div>
                        <span class="font-semibold text-purple-600">
                            ¥{{ number_format($stat['total_sales'] + $stat['total_rentals']) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 bg-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">顧客分析のポイント</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• 期間を指定して顧客の取引傾向を分析できます</li>
            <li>• 売上と貸出の両方から優良顧客を特定できます</li>
            <li>• 現在貸出中の商品がある顧客を確認できます</li>
            <li>• 顧客タイプ（個人・法人）別の分析も可能です</li>
        </ul>
    </div>
</div>
@endsection