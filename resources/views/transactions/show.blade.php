@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">取引詳細</h1>
            <div class="space-x-2">
                <a href="{{ route('transactions.edit', $transaction) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                    編集
                </a>
                <a href="{{ route('transactions.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    一覧へ戻る
                </a>
            </div>
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

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">取引タイプ</h3>
                        <p class="mt-1">
                            @if($transaction->type === 'sale')
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    売上
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    貸出
                                </span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">取引日</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            {{ $transaction->transaction_date->format('Y年m月d日') }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">顧客</h3>
                        <p class="mt-1 text-lg text-gray-900">
                            <a href="{{ route('customers.show', $transaction->customer_id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $transaction->customer->name }}
                            </a>
                            @if($transaction->customer->type === 'company')
                                <span class="text-sm text-gray-500">(法人)</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">商品</h3>
                        <p class="mt-1">
                            <a href="{{ route('products.show', $transaction->product_id) }}" class="text-blue-600 hover:text-blue-800 text-lg">
                                {{ $transaction->product->name }}
                            </a>
                            <br>
                            <span class="text-sm text-gray-500">
                                商品コード: {{ $transaction->product->product_code }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">数量</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            {{ $transaction->quantity }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">単価</h3>
                        <p class="mt-1 text-lg text-gray-900">
                            ¥{{ number_format($transaction->unit_price) }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">合計金額</h3>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            ¥{{ number_format($transaction->total_amount) }}
                        </p>
                    </div>

                    @if($transaction->type === 'rental')
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">返却状態</h3>
                            <p class="mt-1">
                                @if($transaction->returned_at)
                                    <span class="text-green-600 font-semibold">
                                        返却済み ({{ $transaction->returned_at->format('Y/m/d') }})
                                    </span>
                                @else
                                    <span class="text-orange-600 font-semibold">貸出中</span>
                                    @if($transaction->expected_return_date && $transaction->expected_return_date < now())
                                        <span class="text-red-600 text-sm">(期限超過)</span>
                                    @endif
                                @endif
                            </p>
                        </div>

                        @if($transaction->expected_return_date)
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">返却予定日</h3>
                                <p class="mt-1 text-lg {{ $transaction->expected_return_date < now() && !$transaction->returned_at ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ $transaction->expected_return_date->format('Y年m月d日') }}
                                </p>
                            </div>
                        @endif
                    @endif

                    @if($transaction->notes)
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500">備考</h3>
                            <p class="mt-1 text-gray-900">{{ $transaction->notes }}</p>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">担当者</h3>
                        <p class="mt-1 text-gray-900">{{ $transaction->user->name }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500">登録日時</h3>
                        <p class="mt-1 text-gray-900">{{ $transaction->created_at->format('Y年m月d日 H:i') }}</p>
                    </div>
                </div>

                @if($transaction->type === 'rental' && !$transaction->returned_at)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <form action="{{ route('transactions.return', $transaction) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('この商品を返却済みにしますか？')">
                                返却処理
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-bold"
                    onclick="return confirm('本当にこの取引を削除しますか？削除すると在庫数が元に戻ります。')">
                    この取引を削除
                </button>
            </form>
        </div>
    </div>
</div>
@endsection