@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">商品編集</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('products.update', $product) }}" 
              class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4"
              x-data="offlineForm()"
              @submit.prevent="handleSubmit">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="product_code" class="block text-gray-700 text-sm font-bold mb-2">
                    商品コード <span class="text-red-500">*</span>
                </label>
                <input type="text" name="product_code" id="product_code" value="{{ old('product_code', $product->product_code) }}"
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
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}"
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
                <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}"
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
                <input type="number" name="unit_price" id="unit_price" value="{{ old('unit_price', $product->unit_price) }}"
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
                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price', $product->selling_price) }}"
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
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        :disabled="isSubmitting">
                    <span x-show="!isSubmitting">更新</span>
                    <span x-show="isSubmitting">処理中...</span>
                </button>
                <a href="{{ route('products.show', $product) }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    キャンセル
                </a>
            </div>
            
            <!-- オフライン時の通知 -->
            <div x-show="!isOnline" class="mt-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p class="text-sm">
                    現在オフラインです。変更は保存され、オンライン復帰時に同期されます。
                </p>
            </div>
        </form>

        <form method="POST" action="{{ route('products.destroy', $product) }}" class="text-center">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-bold"
                onclick="return confirm('本当にこの商品を削除しますか？')">
                この商品を削除
            </button>
        </form>
    </div>
</div>

<script>
function offlineForm() {
    return {
        isOnline: navigator.onLine,
        isSubmitting: false,
        
        init() {
            window.addEventListener('online', () => this.isOnline = true);
            window.addEventListener('offline', () => this.isOnline = false);
        },
        
        async handleSubmit(event) {
            this.isSubmitting = true;
            const form = event.target;
            const formData = new FormData(form);
            
            try {
                // フォームを通常通り送信
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    // 成功時はリダイレクト
                    window.location.href = '{{ route('products.show', $product) }}';
                } else {
                    throw new Error('更新に失敗しました');
                }
            } catch (error) {
                // オフライン時またはエラー時の処理
                if (!this.isOnline) {
                    alert('オフラインモードで保存されました。オンライン復帰時に同期されます。');
                    // ローカルストレージに保存（実際の実装では、より堅牢な方法を使用）
                    const offlineData = {
                        url: form.action,
                        method: 'POST',
                        data: Object.fromEntries(formData),
                        timestamp: new Date().toISOString()
                    };
                    
                    // 既存のオフラインデータを取得
                    const existingData = JSON.parse(localStorage.getItem('offlineQueue') || '[]');
                    existingData.push(offlineData);
                    localStorage.setItem('offlineQueue', JSON.stringify(existingData));
                    
                    // 成功したように見せかけてリダイレクト
                    window.location.href = '{{ route('products.show', $product) }}';
                } else {
                    alert('エラーが発生しました: ' + error.message);
                }
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endsection