@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">商品登録</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-form-with-loading action="{{ route('products.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            
            <div class="mb-4">
                <label for="product_code" class="block text-gray-700 text-sm font-bold mb-2">
                    商品コード <span class="text-red-500">*</span>
                </label>
                <input type="text" name="product_code" id="product_code" value="{{ old('product_code') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('product_code') border-red-500 @enderror"
                    required>
                @error('product_code')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                    商品名 <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
                    required>
                @error('name')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">
                    在庫数 <span class="text-red-500">*</span>
                </label>
                <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('stock_quantity') border-red-500 @enderror"
                    min="0" required>
                @error('stock_quantity')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="unit_price" class="block text-gray-700 text-sm font-bold mb-2">
                    単価 <span class="text-red-500">*</span>
                </label>
                <input type="number" name="unit_price" id="unit_price" value="{{ old('unit_price') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('unit_price') border-red-500 @enderror"
                    min="0" step="0.01" required>
                @error('unit_price')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="selling_price" class="block text-gray-700 text-sm font-bold mb-2">
                    売値 <span class="text-red-500">*</span>
                </label>
                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('selling_price') border-red-500 @enderror"
                    min="0" step="0.01" required>
                @error('selling_price')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                    説明
                </label>
                <textarea name="description" id="description" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <x-submit-button label="登録" loadingLabel="登録中..." class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" />
                <a href="{{ route('products.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    キャンセル
                </a>
            </div>
        </x-form-with-loading>
    </div>
</div>
@endsection