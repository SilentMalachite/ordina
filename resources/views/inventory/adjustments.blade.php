@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">在庫調整履歴</h1>
        <a href="{{ route('inventory.adjustment.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            新規調整
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <form method="GET" action="{{ route('inventory.adjustments') }}" class="grid grid-cols-1 md:grid-cols-4 gap-2">
                <select name="product_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">すべての商品</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->product_code }} - {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                    placeholder="開始日" 
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                    placeholder="終了日" 
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        検索
                    </button>
                    <a href="{{ route('inventory.adjustments') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        クリア
                    </a>
                </div>
            </form>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日時</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">調整タイプ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">調整前→調整後</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">理由</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">担当者</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($adjustments as $adjustment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->created_at->format('Y/m/d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->product->product_code }}<br>
                            <span class="text-xs text-gray-500">{{ $adjustment->product->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($adjustment->adjustment_type === 'increase')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    増加
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    減少
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->previous_quantity }} → {{ $adjustment->new_quantity }}
                            @if($adjustment->new_quantity <= 10)
                                <span class="text-red-600 text-xs">（低在庫）</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ Str::limit($adjustment->reason, 30) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->user->name }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            調整履歴がありません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $adjustments->links() }}
        </div>
    </div>
</div>
@endsection