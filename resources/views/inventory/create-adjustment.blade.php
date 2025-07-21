@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">在庫調整</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('inventory.adjustment.store') }}" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            
            <div class="mb-4">
                <label for="product_id" class="block text-gray-700 text-sm font-bold mb-2">
                    商品 <span class="text-red-500">*</span>
                </label>
                <select name="product_id" id="product_id" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('product_id') border-red-500 @enderror"
                    required>
                    <option value="">商品を選択してください</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" 
                            {{ old('product_id', request('product_id')) == $product->id ? 'selected' : '' }}
                            data-stock="{{ $product->stock_quantity }}">
                            {{ $product->product_code }} - {{ $product->name }} (在庫: {{ $product->stock_quantity }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="adjustment_type" class="block text-gray-700 text-sm font-bold mb-2">
                    調整タイプ <span class="text-red-500">*</span>
                </label>
                <select name="adjustment_type" id="adjustment_type" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('adjustment_type') border-red-500 @enderror"
                    required>
                    <option value="">選択してください</option>
                    <option value="increase" {{ old('adjustment_type') == 'increase' ? 'selected' : '' }}>増加</option>
                    <option value="decrease" {{ old('adjustment_type') == 'decrease' ? 'selected' : '' }}>減少</option>
                </select>
                @error('adjustment_type')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">
                    数量 <span class="text-red-500">*</span>
                </label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('quantity') border-red-500 @enderror"
                    min="1" required>
                @error('quantity')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
                <p class="text-gray-600 text-xs mt-1" id="stock-info"></p>
            </div>

            <div class="mb-6">
                <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">
                    理由 <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('reason') border-red-500 @enderror"
                    placeholder="在庫調整の理由を入力してください（例：棚卸し、破損、返品等）"
                    required>{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    調整を実行
                </button>
                <a href="{{ route('inventory.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const adjustmentTypeSelect = document.getElementById('adjustment_type');
    const quantityInput = document.getElementById('quantity');
    const stockInfo = document.getElementById('stock-info');

    function updateStockInfo() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const currentStock = selectedOption ? parseInt(selectedOption.dataset.stock || 0) : 0;
        const adjustmentType = adjustmentTypeSelect.value;
        const quantity = parseInt(quantityInput.value || 0);

        if (selectedOption && selectedOption.value && adjustmentType && quantity) {
            let newStock = currentStock;
            if (adjustmentType === 'increase') {
                newStock = currentStock + quantity;
                stockInfo.textContent = `調整後の在庫数: ${newStock}`;
                stockInfo.className = 'text-green-600 text-xs mt-1';
            } else if (adjustmentType === 'decrease') {
                newStock = currentStock - quantity;
                if (newStock < 0) {
                    stockInfo.textContent = `在庫が不足します。調整後: ${newStock}`;
                    stockInfo.className = 'text-red-600 text-xs mt-1';
                } else {
                    stockInfo.textContent = `調整後の在庫数: ${newStock}`;
                    stockInfo.className = 'text-gray-600 text-xs mt-1';
                }
            }
        } else {
            stockInfo.textContent = '';
        }
    }

    productSelect.addEventListener('change', updateStockInfo);
    adjustmentTypeSelect.addEventListener('change', updateStockInfo);
    quantityInput.addEventListener('input', updateStockInfo);
});
</script>
@endsection