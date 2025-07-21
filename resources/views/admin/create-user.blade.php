@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">新規ユーザー作成</h1>
        <a href="{{ route('admin.users') }}" 
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            戻る
        </a>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        名前 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        メールアドレス <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        パスワード <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">8文字以上で入力してください</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        パスワード（確認） <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        ロール <span class="text-red-500">*</span>
                    </label>
                    <select id="role" 
                            name="role" 
                            required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror">
                        <option value="">ロールを選択してください</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">
                        ユーザーに割り当てるロールを選択してください。
                    </p>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.users') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        ユーザーを作成
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection