<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Ordina 在庫管理システム</h1>
        <p class="text-gray-600 mt-2">ログインしてください</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('メールアドレス')" class="text-lg font-medium" />
            <x-text-input id="email" 
                class="block mt-2 w-full h-12 text-lg px-4 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
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
                autocomplete="current-password" 
                placeholder="パスワードを入力" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" 
                class="w-5 h-5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" 
                name="remember">
            <label for="remember_me" class="ml-3 text-lg text-gray-700">ログイン状態を保持</label>
        </div>

        <!-- Login Button -->
        <div class="pt-4">
            <button type="submit" 
                class="w-full h-14 bg-blue-600 hover:bg-blue-700 text-white text-xl font-bold rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                ログイン
            </button>
        </div>

        <!-- Forgot Password Link -->
        @if (Route::has('password.request'))
            <div class="text-center pt-4">
                <a class="text-blue-600 hover:text-blue-800 text-lg underline" 
                   href="{{ route('password.request') }}">
                    パスワードをお忘れですか？
                </a>
            </div>
        @endif

        <!-- Register Link -->
        @if (Route::has('register'))
            <div class="text-center pt-2 border-t border-gray-200">
                <p class="text-gray-600 text-lg mt-4">アカウントをお持ちではありませんか？</p>
                <a class="text-blue-600 hover:text-blue-800 text-lg font-medium underline" 
                   href="{{ route('register') }}">
                    新規登録
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
