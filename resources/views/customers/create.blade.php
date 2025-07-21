@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">顧客登録</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customers.store') }}" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            
            <div class="mb-4">
                <label for="type" class="block text-gray-700 text-sm font-bold mb-2">
                    顧客タイプ <span class="text-red-500">*</span>
                </label>
                <select name="type" id="type" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('type') border-red-500 @enderror"
                    required>
                    <option value="individual" {{ old('type', 'individual') == 'individual' ? 'selected' : '' }}>個人</option>
                    <option value="company" {{ old('type') == 'company' ? 'selected' : '' }}>法人</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                    <span id="name-label">名前</span> <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
                    required>
                @error('name')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4" id="contact-person-field" style="display: none;">
                <label for="contact_person" class="block text-gray-700 text-sm font-bold mb-2">
                    担当者名
                </label>
                <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('contact_person') border-red-500 @enderror">
                @error('contact_person')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                    メールアドレス
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">
                    電話番号
                </label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone') border-red-500 @enderror"
                    placeholder="03-1234-5678 または 090-1234-5678">
                @error('phone')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="block text-gray-700 text-sm font-bold mb-2">
                    住所
                </label>
                <textarea name="address" id="address" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">
                    備考
                </label>
                <textarea name="notes" id="notes" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    登録
                </button>
                <a href="{{ route('customers.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const nameLabel = document.getElementById('name-label');
    const contactPersonField = document.getElementById('contact-person-field');

    function updateFormFields() {
        if (typeSelect.value === 'company') {
            nameLabel.textContent = '会社名';
            contactPersonField.style.display = 'block';
        } else {
            nameLabel.textContent = '名前';
            contactPersonField.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', updateFormFields);
    updateFormFields(); // Initial state
});
</script>
@endsection