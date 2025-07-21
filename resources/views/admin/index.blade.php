@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">管理者ダッシュボード</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-2xl font-bold text-gray-800">{{ $stats['total_users'] }}</div>
                <div class="text-sm text-gray-600">総ユーザー数</div>
                <div class="text-xs text-gray-500 mt-2">管理者: {{ $stats['admin_users'] }}人</div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-2xl font-bold text-gray-800">{{ $stats['total_products'] }}</div>
                <div class="text-sm text-gray-600">総商品数</div>
                <div class="text-xs text-red-500 mt-2">低在庫: {{ $stats['low_stock_products'] }}商品</div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-2xl font-bold text-gray-800">{{ $stats['total_customers'] }}</div>
                <div class="text-sm text-gray-600">総顧客数</div>
                <div class="text-xs text-blue-500 mt-2">アクティブ貸出: {{ $stats['active_rentals'] }}件</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">管理メニュー</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.users') }}" class="block p-3 bg-gray-100 hover:bg-gray-200 rounded">
                        ユーザー管理
                    </a>
                    <a href="{{ route('roles.index') }}" class="block p-3 bg-gray-100 hover:bg-gray-200 rounded">
                        ロール管理
                    </a>
                    <a href="{{ route('admin.data-management') }}" class="block p-3 bg-gray-100 hover:bg-gray-200 rounded">
                        データ管理
                    </a>
                    <a href="{{ route('admin.closing-dates') }}" class="block p-3 bg-gray-100 hover:bg-gray-200 rounded">
                        締め日設定
                    </a>
                    <a href="{{ route('admin.system-logs') }}" class="block p-3 bg-gray-100 hover:bg-gray-200 rounded">
                        システムログ
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">最近の活動</h2>
                @if($recentActivities['new_users']->count() > 0)
                    <div class="mb-4">
                        <h3 class="font-medium text-gray-700 mb-2">新規ユーザー</h3>
                        <ul class="text-sm space-y-1">
                            @foreach($recentActivities['new_users'] as $user)
                                <li>{{ $user->name }} ({{ $user->created_at->diffForHumans() }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($recentActivities['recent_transactions']->count() > 0)
                    <div>
                        <h3 class="font-medium text-gray-700 mb-2">最近の取引</h3>
                        <ul class="text-sm space-y-1">
                            @foreach($recentActivities['recent_transactions'] as $transaction)
                                <li>
                                    {{ $transaction->product->name }} - 
                                    {{ $transaction->customer->name }}
                                    ({{ $transaction->created_at->diffForHumans() }})
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection