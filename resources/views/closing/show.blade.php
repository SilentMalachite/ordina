@extends('layouts.app')

@section('title', '締め処理プレビュー')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">締め処理プレビュー</h1>
                    <a href="{{ route('closing.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        戻る
                    </a>
                </div>

                <!-- 期間情報 -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">締め期間</h3>
                    <p class="text-blue-700">
                        {{ $data['period']['start']->format('Y年m月d日') }} ～ 
                        {{ $data['period']['end']->format('Y年m月d日') }}
                    </p>
                </div>

                <!-- サマリー情報 -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-green-800">売上件数</h4>
                        <p class="text-2xl font-bold text-green-600">{{ $data['totals']['sales_count'] }}件</p>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-green-800">売上金額</h4>
                        <p class="text-2xl font-bold text-green-600">¥{{ number_format($data['totals']['sales_amount']) }}</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800">貸出件数</h4>
                        <p class="text-2xl font-bold text-blue-600">{{ $data['totals']['rentals_count'] }}件</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800">貸出金額</h4>
                        <p class="text-2xl font-bold text-blue-600">¥{{ number_format($data['totals']['rentals_amount']) }}</p>
                    </div>
                </div>

                <!-- 商品別サマリー -->
                @if(count($data['product_summary']) > 0)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">商品別サマリー</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品名</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">売上数量</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">売上金額</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出数量</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出金額</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($data['product_summary'] as $summary)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $summary['product']->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $summary['sales_quantity'] }}個
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ¥{{ number_format($summary['sales_amount']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $summary['rentals_quantity'] }}個
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ¥{{ number_format($summary['rentals_amount']) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- 顧客別サマリー -->
                @if(count($data['customer_summary']) > 0)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">顧客別サマリー</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">顧客名</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">売上件数</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">売上金額</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出件数</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">貸出金額</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($data['customer_summary'] as $summary)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $summary['customer']->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $summary['sales_count'] }}件
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ¥{{ number_format($summary['sales_amount']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $summary['rentals_count'] }}件
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ¥{{ number_format($summary['rentals_amount']) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- 実行ボタン -->
                <div class="flex justify-center space-x-4">
                    <form method="POST" action="{{ route('closing.process') }}" class="inline">
                        @csrf
                        <input type="hidden" name="closing_date_id" value="{{ request('closing_date_id') }}">
                        <input type="hidden" name="closing_date" value="{{ request('closing_date') }}">
                        <input type="hidden" name="confirmation" value="1">
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('締め処理を実行しますか？この操作は取り消せません。')">
                            締め処理を実行
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection