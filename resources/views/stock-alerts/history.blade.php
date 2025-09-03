@extends('layouts.app')

@section('title', '在庫アラート履歴')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">在庫アラート履歴</h1>
                    <a href="{{ route('stock-alerts.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        戻る
                    </a>
                </div>

                @if(count($history) > 0)
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="space-y-2">
                        @foreach($history as $entry)
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <div class="text-sm text-gray-600">
                                    {{ $entry }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-gray-500">在庫アラートの履歴がありません。</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection