@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">顧客詳細</h1>
            <div class="space-x-2">
                <a href="{{ route('customers.edit', $customer) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                    編集
                </a>
                <a href="{{ route('customers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    一覧へ戻る
                </a>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">名前</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $customer->name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">タイプ</h3>
                        <p class="mt-1">
                            @if($customer->type === 'individual')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    個人
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    法人
                                </span>
                            @endif
                        </p>
                    </div>
                    @if($customer->contact_person)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">担当者</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $customer->contact_person }}</p>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">メールアドレス</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $customer->email ?? '-' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">電話番号</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $customer->phone ?? '-' }}</p>
                    </div>
                    @if($customer->address)
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">住所</h3>
                            <p class="mt-1 text-gray-900">{{ $customer->address }}</p>
                        </div>
                    @endif
                    @if($customer->notes)
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">備考</h3>
                            <p class="mt-1 text-gray-900">{{ $customer->notes }}</p>
                        </div>
                    @endif
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">登録日時</h3>
                        <p class="mt-1 text-gray-900">{{ $customer->created_at->format('Y年m月d日 H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">更新日時</h3>
                        <p class="mt-1 text-gray-900">{{ $customer->updated_at->format('Y年m月d日 H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">取引統計</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-4 rounded">
                        <p class="text-sm text-gray-500">総取引数</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_transactions'] }}件</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded">
                        <p class="text-sm text-gray-500">売上総額</p>
                        <p class="text-2xl font-bold text-green-600">¥{{ number_format($statistics['total_sales']) }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm text-gray-500">貸出件数</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $statistics['total_rentals'] }}件</p>
                    </div>
                    <div class="bg-orange-50 p-4 rounded">
                        <p class="text-sm text-gray-500">未返却数</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $statistics['pending_returns'] }}件</p>
                    </div>
                </div>
                @if($statistics['pending_returns'] > 0)
                    <div class="mt-4 text-center">
                        <a href="{{ route('customers.rentals', $customer) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                            貸出状況を確認 →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">最近の取引履歴</h2>
                @if($transactions->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">取引日</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイプ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">金額</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transactions as $transaction)
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
                                        {{ $transaction->product->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ¥{{ number_format($transaction->total_amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($transaction->type === 'rental')
                                            @if($transaction->returned_at)
                                                <span class="text-green-600">返却済</span>
                                            @else
                                                <span class="text-orange-600">貸出中</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                @else
                    <p class="text-gray-500">取引履歴はありません。</p>
                @endif
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <a href="{{ route('transactions.create') }}?customer_id={{ $customer->id }}" 
               class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                新規取引を作成
            </a>
        </div>
    </div>
</div>
@endsection