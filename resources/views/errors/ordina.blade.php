@extends('layouts.app')

@section('title', 'エラー')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-12 w-12 text-red-500">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                エラーが発生しました
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                エラーコード: {{ $error_code ?? 'UNKNOWN_ERROR' }}
            </p>
        </div>
        
        <div class="bg-white shadow rounded-lg p-6">
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ $message ?? 'システムエラーが発生しました。' }}
                </h3>
                
                @if(isset($context) && !empty($context))
                <div class="mt-4 text-left">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">詳細情報:</h4>
                    <div class="bg-gray-50 rounded-md p-3 text-xs text-gray-600">
                        <pre>{{ json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
                
                <div class="mt-6 space-y-3">
                    <a href="{{ url()->previous() }}" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        前のページに戻る
                    </a>
                    
                    <a href="{{ route('dashboard') }}" 
                       class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ダッシュボードに戻る
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <p class="text-xs text-gray-500">
                問題が解決しない場合は、システム管理者にお問い合わせください。
            </p>
        </div>
    </div>
</div>
@endsection