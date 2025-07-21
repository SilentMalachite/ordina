@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">新しい締め日の追加</h1>
        <a href="{{ route('admin.closing-dates') }}" 
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            戻る
        </a>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('admin.closing-dates.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="day_of_month" class="block text-sm font-medium text-gray-700 mb-2">
                        締め日（月の日にち） <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center space-x-2">
                        <input type="number" 
                               id="day_of_month" 
                               name="day_of_month" 
                               value="{{ old('day_of_month', 25) }}"
                               min="1" 
                               max="31"
                               required
                               class="w-20 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('day_of_month') border-red-500 @enderror">
                        <span class="text-sm text-gray-600">日</span>
                    </div>
                    @error('day_of_month')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">
                        毎月の締め日として使用される日にちを指定してください（1〜31日）
                    </p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        説明
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">
                        この締め日の目的や用途を記載してください（例：月末締め、四半期末締めなど）
                    </p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">締め日設定について</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>同じ日付の締め日は複数登録できません</li>
                                    <li>過去の日付でも締め日として設定可能です</li>
                                    <li>設定した締め日は、売上・在庫レポートの期間設定で使用されます</li>
                                    <li>一般的な締め日の例：月末（31日）、四半期末（3/31, 6/30, 9/30, 12/31）</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.closing-dates') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        締め日を追加
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- プリセット締め日の提案 -->
    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">よく使われる締め日</h2>
            <p class="text-gray-600 mb-4">
                以下は一般的に使用される締め日の例です。参考にしてください。
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="border border-gray-200 rounded-md p-4">
                    <h3 class="font-medium text-gray-800">月末締め</h3>
                    <p class="text-sm text-gray-600 mt-1">毎月末日を締め日とする設定</p>
                    <div class="mt-2 text-sm text-gray-500">
                        日にち：31日（2月は28/29日まで自動調整）
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-md p-4">
                    <h3 class="font-medium text-gray-800">25日締め</h3>
                    <p class="text-sm text-gray-600 mt-1">毎月25日を締め日とする設定</p>
                    <div class="mt-2 text-sm text-gray-500">
                        日にち：25日（一般的な給与計算期間）
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-md p-4">
                    <h3 class="font-medium text-gray-800">20日締め</h3>
                    <p class="text-sm text-gray-600 mt-1">毎月20日を締め日とする設定</p>
                    <div class="mt-2 text-sm text-gray-500">
                        日にち：20日（中旬締めでよく使用）
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 入力値の妥当性チェック
document.addEventListener('DOMContentLoaded', function() {
    const dayInput = document.getElementById('day_of_month');
    
    dayInput.addEventListener('change', function() {
        const value = parseInt(this.value);
        if (value < 1) {
            this.value = 1;
        } else if (value > 31) {
            this.value = 31;
        }
    });
});
</script>
@endsection