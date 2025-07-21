<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">新規ユーザー登録</h1>
        <p class="text-gray-600 mt-2">アカウントを作成してください</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('ユーザー名')" class="text-lg font-medium" />
            <x-text-input id="name" 
                class="block mt-2 w-full h-12 text-lg px-4 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" 
                type="text" 
                name="name" 
                :value="old('name')" 
                required 
                autofocus 
                autocomplete="name" 
                placeholder="お名前を入力" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('メールアドレス')" class="text-lg font-medium" />
            <x-text-input id="email" 
                class="block mt-2 w-full h-12 text-lg px-4 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autocomplete="username" 
                placeholder="メールアドレスを入力" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('パスワード')" class="text-lg font-medium" />
            <x-text-input id="password" 
                class="block mt-2 w-full h-12 text-lg px-4 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                type="password"
                name="password"
                required 
                autocomplete="new-password" 
                placeholder="パスワードを入力" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('パスワード確認')" class="text-lg font-medium" />
            <x-text-input id="password_confirmation" 
                class="block mt-2 w-full h-12 text-lg px-4 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="パスワードを再入力" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Register Button -->
        <div class="pt-4">
            <button type="submit" 
                class="w-full h-14 bg-green-600 hover:bg-green-700 text-white text-xl font-bold rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                アカウント作成
            </button>
        </div>

        <!-- Login Link -->
        <div class="text-center pt-4 border-t border-gray-200">
            <p class="text-gray-600 text-lg mt-4">すでにアカウントをお持ちですか？</p>
            <a class="text-blue-600 hover:text-blue-800 text-lg font-medium underline" 
               href="{{ route('login') }}">
                ログイン
            </a>
        </div>
    </form>
</x-guest-layout>
