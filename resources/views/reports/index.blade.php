@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">レポート</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('reports.sales') }}" class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-green-600 mb-2">売上レポート</h2>
                    <p class="text-gray-600">期間別、顧客別、商品別の売上データを確認</p>
                    <p class="text-sm text-gray-500 mt-2">日次・週次・月次・年次レポート</p>
                </div>
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <span class="text-sm text-gray-500">CSVエクスポート対応</span>
            </div>
        </a>

        <a href="{{ route('reports.rentals') }}" class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-blue-600 mb-2">貸出レポート</h2>
                    <p class="text-gray-600">貸出状況と返却状況の確認</p>
                    <p class="text-sm text-gray-500 mt-2">期限超過・返却済み・貸出中の管理</p>
                </div>
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <span class="text-sm text-gray-500">返却期限管理・アラート機能</span>
            </div>
        </a>

        <a href="{{ route('reports.inventory') }}" class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-orange-600 mb-2">在庫レポート</h2>
                    <p class="text-gray-600">現在の在庫状況と在庫価値の確認</p>
                    <p class="text-sm text-gray-500 mt-2">低在庫商品の一覧・在庫推移</p>
                </div>
                <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <span class="text-sm text-gray-500">CSVエクスポート対応</span>
            </div>
        </a>

        <a href="{{ route('reports.customers') }}" class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-purple-600 mb-2">顧客レポート</h2>
                    <p class="text-gray-600">顧客別の売上・貸出実績</p>
                    <p class="text-sm text-gray-500 mt-2">優良顧客分析・取引履歴</p>
                </div>
                <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <span class="text-sm text-gray-500">顧客ランキング・傾向分析</span>
            </div>
        </a>
    </div>

    @if($closingDates->count() > 0)
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">締め日設定</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($closingDates as $closingDate)
                    <div class="bg-gray-50 rounded p-3 text-center">
                        <p class="text-sm text-gray-600">毎月</p>
                        <p class="font-semibold text-xl">{{ $closingDate->day_of_month }}日</p>
                        @if($closingDate->is_active)
                            <span class="text-xs text-green-600">有効</span>
                        @else
                            <span class="text-xs text-gray-400">無効</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-8 bg-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">レポート機能について</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• 各レポートは期間を指定して集計できます</li>
            <li>• CSV形式でのダウンロードが可能です</li>
            <li>• 締め日を基準とした集計にも対応しています</li>
            <li>• グラフ表示で視覚的に把握できます</li>
        </ul>
    </div>
</div>
@endsection