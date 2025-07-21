@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">ユーザー編集</h1>
        <a href="{{ route('admin.users') }}" 
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            戻る
        </a>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        名前 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}"
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
                           value="{{ old('email', $user->email) }}"
                           required
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        新しいパスワード
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">
                        空欄の場合はパスワードは変更されません。変更する場合は8文字以上で入力してください。
                    </p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        新しいパスワード（確認）
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        ロール <span class="text-red-500">*</span>
                    </label>
                    <select id="role" 
                            name="role" 
                            required
                            @if($user->id === auth()->id()) disabled @endif
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror">
                        <option value="">ロールを選択してください</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" 
                                {{ old('role', $userRole ? $userRole->name : '') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $userRole ? $userRole->name : '' }}">
                        <p class="text-yellow-600 text-sm mt-1">
                            自分自身のロールは変更できません。
                        </p>
                    @else
                        @error('role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">
                            ユーザーに割り当てるロールを選択してください。
                        </p>
                    @endif
                </div>

                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">ユーザー情報</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><strong>ユーザーID:</strong> {{ $user->id }}</p>
                        <p><strong>登録日時:</strong> {{ $user->created_at->format('Y-m-d H:i:s') }}</p>
                        <p><strong>最終更新:</strong> {{ $user->updated_at->format('Y-m-d H:i:s') }}</p>
                        @if($user->email_verified_at)
                            <p><strong>メール認証:</strong> 認証済み ({{ $user->email_verified_at->format('Y-m-d H:i:s') }})</p>
                        @else
                            <p><strong>メール認証:</strong> <span class="text-red-600">未認証</span></p>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.users') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        ユーザー情報を更新
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection