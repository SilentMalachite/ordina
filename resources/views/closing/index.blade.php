@extends('layouts.app')

@section('title', '締め処理')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">締め処理</h1>
                    <a href="{{ route('closing.history') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        履歴を表示
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- 次の締め日情報 -->
                @if($next_closing_date)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">次の締め日</h3>
                    <p class="text-blue-700">
                        {{ $next_closing_date->format('Y年m月d日') }} 
                        ({{ $next_closing_date->diffForHumans() }})
                    </p>
                </div>
                @endif

                <!-- 締め処理フォーム -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">締め処理の実行</h2>
                    
                    <form method="POST" action="{{ route('closing.process') }}" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="closing_date_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    締め日設定 <span class="text-red-500">*</span>
                                </label>
                                <select name="closing_date_id" id="closing_date_id" 
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">締め日設定を選択してください</option>
                                    @foreach($closing_dates as $closingDate)
                                        <option value="{{ $closingDate->id }}">
                                            毎月{{ $closingDate->day_of_month }}日
                                            @if($closingDate->description)
                                                - {{ $closingDate->description }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('closing_date_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="closing_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    締め処理日 <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="closing_date" id="closing_date" 
                                       value="{{ old('closing_date', now()->format('Y-m-d')) }}"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('closing_date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">注意事項</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>締め処理を実行すると、指定した期間のデータが確定されます</li>
                                            <li>締め処理後は、該当期間のデータを修正できません</li>
                                            <li>処理前に必ずデータの確認を行ってください</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="confirmation" id="confirmation" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                            <label for="confirmation" class="ml-2 block text-sm text-gray-900">
                                上記の注意事項を確認し、締め処理を実行することに同意します
                            </label>
                        </div>
                        @error('confirmation')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <div class="flex space-x-4">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                締め処理を実行
                            </button>
                            
                            <button type="button" onclick="previewClosing()" 
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                プレビュー
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 最近の締め処理履歴 -->
                @if(count($closing_history) > 0)
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-4">最近の締め処理履歴</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            @foreach($closing_history as $history)
                                <div class="text-sm text-gray-600">
                                    {{ $history }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function previewClosing() {
    const closingDateId = document.getElementById('closing_date_id').value;
    const closingDate = document.getElementById('closing_date').value;
    
    if (!closingDateId || !closingDate) {
        alert('締め日設定と締め処理日を選択してください。');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '{{ route("closing.show") }}';
    
    const input1 = document.createElement('input');
    input1.type = 'hidden';
    input1.name = 'closing_date_id';
    input1.value = closingDateId;
    
    const input2 = document.createElement('input');
    input2.type = 'hidden';
    input2.name = 'closing_date';
    input2.value = closingDate;
    
    form.appendChild(input1);
    form.appendChild(input2);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection