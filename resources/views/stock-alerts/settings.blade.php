@extends('layouts.app')

@section('title', '在庫アラート設定')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">在庫アラート設定</h1>
                    <a href="{{ route('stock-alerts.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        戻る
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- 現在の設定 -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">現在の設定</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-blue-600">低在庫閾値</p>
                            <p class="text-xl font-bold text-blue-800">{{ $statistics['low_stock_threshold'] }}個以下</p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600">最終チェック</p>
                            <p class="text-xl font-bold text-blue-800">{{ $statistics['last_check'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600">総商品数</p>
                            <p class="text-xl font-bold text-blue-800">{{ $statistics['total_products'] }}種類</p>
                        </div>
                    </div>
                </div>

                <!-- 設定変更フォーム -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">設定変更</h2>
                    
                    <form method="POST" action="{{ route('stock-alerts.update-settings') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-2">
                                低在庫閾値 <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="low_stock_threshold" id="low_stock_threshold" 
                                   value="{{ old('low_stock_threshold', $statistics['low_stock_threshold']) }}"
                                   min="1" max="1000"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <p class="text-sm text-gray-500 mt-1">この数量以下の商品を低在庫として通知します</p>
                            @error('low_stock_threshold')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">注意事項</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>設定変更後、即座に新しい閾値でアラートが動作します</li>
                                            <li>低在庫商品の数が変わる可能性があります</li>
                                            <li>適切な閾値を設定して、業務に支障のないようにしてください</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            設定を更新
                        </button>
                    </form>
                </div>

                <!-- 最近のアラート履歴 -->
                @if(count($alert_history) > 0)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">最近のアラート履歴</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            @foreach($alert_history as $alert)
                                <div class="bg-white border border-gray-200 rounded-lg p-3">
                                    <div class="text-sm text-gray-600">
                                        {{ $alert }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection