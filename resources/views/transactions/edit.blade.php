@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">取引編集</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="type" class="block text-gray-700 text-sm font-bold mb-2">
                    取引タイプ <span class="text-red-500">*</span>
                </label>
                <select name="type" id="type" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('type') border-red-500 @enderror"
                    required>
                    <option value="sale" {{ old('type', $transaction->type) == 'sale' ? 'selected' : '' }}>売上</option>
                    <option value="rental" {{ old('type', $transaction->type) == 'rental' ? 'selected' : '' }}>貸出</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="customer_id" class="block text-gray-700 text-sm font-bold mb-2">
                    顧客 <span class="text-red-500">*</span>
                </label>
                <select name="customer_id" id="customer_id" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('customer_id') border-red-500 @enderror"
                    required>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" 
                            {{ old('customer_id', $transaction->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} {{ $customer->type === 'company' ? '(法人)' : '(個人)' }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="product_id" class="block text-gray-700 text-sm font-bold mb-2">
                    商品 <span class="text-red-500">*</span>
                </label>
                <select name="product_id" id="product_id" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('product_id') border-red-500 @enderror"
                    required>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" 
                            data-unit-price="{{ $product->unit_price }}"
                            data-selling-price="{{ $product->selling_price }}"
                            data-stock="{{ $product->stock_quantity }}"
                            {{ old('product_id', $transaction->product_id) == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} (在庫: {{ $product->stock_quantity }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-600 mt-1">在庫数: <span id="stock-display">{{ $transaction->product->stock_quantity }}</span></p>
            </div>

            <div class="mb-4">
                <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">
                    数量 <span class="text-red-500">*</span>
                </label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $transaction->quantity) }}"
                    min="1" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('quantity') border-red-500 @enderror">
                @error('quantity')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-600 mt-1">現在の取引数量: {{ $transaction->quantity }}</p>
            </div>

            <div class="mb-4">
                <label for="unit_price" class="block text-gray-700 text-sm font-bold mb-2">
                    単価 <span class="text-red-500">*</span>
                </label>
                <input type="number" name="unit_price" id="unit_price" value="{{ old('unit_price', $transaction->unit_price) }}"
                    min="0" step="0.01" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('unit_price') border-red-500 @enderror">
                @error('unit_price')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-600 mt-1">合計金額: ¥<span id="total-amount">{{ number_format($transaction->total_amount) }}</span></p>
            </div>

            <div class="mb-4">
                <label for="transaction_date" class="block text-gray-700 text-sm font-bold mb-2">
                    取引日 <span class="text-red-500">*</span>
                </label>
                <input type="date" name="transaction_date" id="transaction_date" 
                    value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('transaction_date') border-red-500 @enderror">
                @error('transaction_date')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4" id="expected-return-date-field" style="{{ $transaction->type === 'rental' ? '' : 'display: none;' }}">
                <label for="expected_return_date" class="block text-gray-700 text-sm font-bold mb-2">
                    返却予定日 <span class="text-red-500">*</span>
                </label>
                <input type="date" name="expected_return_date" id="expected_return_date" 
                    value="{{ old('expected_return_date', $transaction->expected_return_date ? $transaction->expected_return_date->format('Y-m-d') : '') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('expected_return_date') border-red-500 @enderror">
                @error('expected_return_date')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
                @if($transaction->type === 'rental' && $transaction->returned_at)
                    <p class="text-sm text-green-600 mt-1">返却済み: {{ $transaction->returned_at->format('Y年m月d日') }}</p>
                @endif
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">
                    備考
                </label>
                <textarea name="notes" id="notes" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('notes') border-red-500 @enderror">{{ old('notes', $transaction->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    更新
                </button>
                <a href="{{ route('transactions.show', $transaction) }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unit_price');
    const expectedReturnDateField = document.getElementById('expected-return-date-field');
    const expectedReturnDateInput = document.getElementById('expected_return_date');
    const stockDisplay = document.getElementById('stock-display');
    const totalAmountDisplay = document.getElementById('total-amount');

    // Store original values
    const originalProductId = {{ $transaction->product_id }};
    const originalQuantity = {{ $transaction->quantity }};

    function updateTypeFields() {
        if (typeSelect.value === 'rental') {
            expectedReturnDateField.style.display = 'block';
            expectedReturnDateInput.setAttribute('required', 'required');
        } else {
            expectedReturnDateField.style.display = 'none';
            expectedReturnDateInput.removeAttribute('required');
        }
    }

    function updateProductInfo() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const stock = parseInt(selectedOption.getAttribute('data-stock'));
            const unitPrice = selectedOption.getAttribute('data-unit-price');
            const sellingPrice = selectedOption.getAttribute('data-selling-price');
            
            // Calculate available stock
            let availableStock = stock;
            if (selectedOption.value == originalProductId) {
                // If same product, add back the original quantity
                availableStock = stock + originalQuantity;
            }
            
            stockDisplay.textContent = availableStock;
            quantityInput.max = availableStock;
            
            updateTotalAmount();
        }
    }

    function updateTotalAmount() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const total = quantity * unitPrice;
        totalAmountDisplay.textContent = total.toLocaleString();
    }

    typeSelect.addEventListener('change', function() {
        updateTypeFields();
        updateProductInfo();
    });
    
    productSelect.addEventListener('change', updateProductInfo);
    quantityInput.addEventListener('input', updateTotalAmount);
    unitPriceInput.addEventListener('input', updateTotalAmount);

    // Initialize
    updateTypeFields();
    updateProductInfo();
});
</script>
@endsection