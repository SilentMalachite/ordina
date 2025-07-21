@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">一括在庫調整</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('inventory.bulk-adjustment.store') }}" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            
            <div class="mb-6">
                <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">
                    調整理由 <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" id="reason" rows="2"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('reason') border-red-500 @enderror"
                    placeholder="一括調整の理由を入力してください（例：棚卸し、定期整理等）"
                    required>{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">調整対象商品</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="adjustment-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">現在庫</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">調整タイプ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">調整後</th>
                                <th class="px-6 py-3"><span class="sr-only">削除</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="adjustment-rows">
                            <tr class="adjustment-row">
                                <td class="px-6 py-4">
                                    <select name="adjustments[0][product_id]" class="product-select shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        <option value="">商品を選択</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-stock="{{ $product->stock_quantity }}">
                                                {{ $product->product_code }} - {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 current-stock">-</td>
                                <td class="px-6 py-4">
                                    <select name="adjustments[0][adjustment_type]" class="adjustment-type shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        <option value="">選択</option>
                                        <option value="increase">増加</option>
                                        <option value="decrease">減少</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" name="adjustments[0][quantity]" min="1" class="quantity-input shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 new-stock">-</td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" class="remove-row text-red-600 hover:text-red-900">削除</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <button type="button" id="add-row" class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                    商品を追加
                </button>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    一括調整を実行
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
    let rowIndex = 0;
    const productOptions = document.querySelector('.product-select').innerHTML;

    function updateStockInfo(row) {
        const productSelect = row.querySelector('.product-select');
        const adjustmentType = row.querySelector('.adjustment-type');
        const quantityInput = row.querySelector('.quantity-input');
        const currentStockCell = row.querySelector('.current-stock');
        const newStockCell = row.querySelector('.new-stock');

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const currentStock = selectedOption ? parseInt(selectedOption.dataset.stock || 0) : 0;
        const quantity = parseInt(quantityInput.value || 0);

        if (selectedOption && selectedOption.value) {
            currentStockCell.textContent = currentStock;
            
            if (adjustmentType.value && quantity) {
                let newStock = currentStock;
                if (adjustmentType.value === 'increase') {
                    newStock = currentStock + quantity;
                    newStockCell.textContent = newStock;
                    newStockCell.className = 'px-6 py-4 text-sm text-green-600 font-bold new-stock';
                } else if (adjustmentType.value === 'decrease') {
                    newStock = currentStock - quantity;
                    newStockCell.textContent = newStock;
                    newStockCell.className = newStock < 0 ? 'px-6 py-4 text-sm text-red-600 font-bold new-stock' : 'px-6 py-4 text-sm text-gray-900 new-stock';
                }
            } else {
                newStockCell.textContent = '-';
                newStockCell.className = 'px-6 py-4 text-sm text-gray-900 new-stock';
            }
        } else {
            currentStockCell.textContent = '-';
            newStockCell.textContent = '-';
        }
    }

    function addEventListeners(row) {
        row.querySelector('.product-select').addEventListener('change', () => updateStockInfo(row));
        row.querySelector('.adjustment-type').addEventListener('change', () => updateStockInfo(row));
        row.querySelector('.quantity-input').addEventListener('input', () => updateStockInfo(row));
        row.querySelector('.remove-row').addEventListener('click', function() {
            if (document.querySelectorAll('.adjustment-row').length > 1) {
                row.remove();
            } else {
                alert('最低1つの商品が必要です。');
            }
        });
    }

    document.getElementById('add-row').addEventListener('click', function() {
        rowIndex++;
        const newRow = document.createElement('tr');
        newRow.className = 'adjustment-row';
        newRow.innerHTML = `
            <td class="px-6 py-4">
                <select name="adjustments[${rowIndex}][product_id]" class="product-select shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                    ${productOptions}
                </select>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 current-stock">-</td>
            <td class="px-6 py-4">
                <select name="adjustments[${rowIndex}][adjustment_type]" class="adjustment-type shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                    <option value="">選択</option>
                    <option value="increase">増加</option>
                    <option value="decrease">減少</option>
                </select>
            </td>
            <td class="px-6 py-4">
                <input type="number" name="adjustments[${rowIndex}][quantity]" min="1" class="quantity-input shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 new-stock">-</td>
            <td class="px-6 py-4 text-center">
                <button type="button" class="remove-row text-red-600 hover:text-red-900">削除</button>
            </td>
        `;
        
        document.getElementById('adjustment-rows').appendChild(newRow);
        addEventListeners(newRow);
    });

    // Initialize first row
    addEventListeners(document.querySelector('.adjustment-row'));
});
</script>
@endsection