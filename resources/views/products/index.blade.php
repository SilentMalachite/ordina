@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">商品管理</h1>
        @can('product-create')
            <a href="{{ route('products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                商品を登録
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <form method="GET" action="{{ route('products.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="商品コード・商品名で検索" 
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    検索
                </button>
                <a href="{{ route('products.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    クリア
                </a>
            </form>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品コード</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品名</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">在庫数</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">単価</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">売値</th>
                    <th class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $product->product_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $product->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($product->stock_quantity <= 10)
                                <span class="text-red-600 font-bold">{{ $product->stock_quantity }}</span>
                            @else
                                {{ $product->stock_quantity }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ¥{{ number_format($product->unit_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ¥{{ number_format($product->selling_price) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">詳細</a>
                            @can('product-edit')
                                <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">編集</a>
                            @endcan
                            @can('product-delete')
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                        onclick="return confirm('本当に削除しますか？')">削除</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            商品が登録されていません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection