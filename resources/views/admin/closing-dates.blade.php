@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">締め日設定</h1>
        <a href="{{ route('admin.closing-dates.create') }}" 
           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            新しい締め日を追加
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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="mb-4">
                <p class="text-gray-600">
                    締め日を設定することで、レポート機能で期間別の集計を行う際の基準日として使用できます。
                    月末や四半期末など、業務に応じて締め日を設定してください。
                </p>
            </div>

            @if($closingDates->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    締め日
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    説明
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    登録日時
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    アクション
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($closingDates as $closingDate)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            毎月{{ $closingDate->day_of_month }}日
                                        </div>
                                        @if($closingDate->is_active)
                                            <div class="text-sm text-green-600">
                                                有効
                                            </div>
                                        @else
                                            <div class="text-sm text-red-600">
                                                無効
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $closingDate->description ?? '（説明なし）' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $closingDate->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" action="{{ route('admin.closing-dates.destroy', $closingDate) }}" 
                                              class="inline"
                                              onsubmit="return confirm('この締め日設定を削除してもよろしいですか？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900">
                                                削除
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $closingDates->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">締め日が設定されていません</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        新しい締め日を追加して、レポート機能で使用する期間を設定しましょう。
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.closing-dates.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            新しい締め日を追加
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($closingDates->count() > 0)
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">締め日について</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>締め日は、レポート機能で期間別の集計を行う際の基準日として使用されます</li>
                            <li>月末締め、四半期末締めなど、業務に応じて複数の締め日を設定できます</li>
                            <li>既に設定された締め日と同じ日付は登録できません</li>
                            <li>締め日を削除しても、過去のレポートデータには影響しません</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection