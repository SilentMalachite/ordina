@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">システムログ</h1>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <p class="text-gray-600">
                    最新100行のシステムログを表示しています。
                </p>
                <button onclick="refreshLogs()" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    更新
                </button>
            </div>

            @if(!empty($logs) && count($logs) > 0)
                <div class="bg-gray-900 text-green-400 p-4 rounded-md overflow-x-auto" style="max-height: 600px; overflow-y: auto;">
                    <pre class="text-sm font-mono leading-relaxed" id="log-content">{{ implode("\n", array_filter($logs)) }}</pre>
                </div>

                <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
                    <span>ログファイルパス: storage/logs/laravel.log</span>
                    <span>表示行数: {{ count(array_filter($logs)) }}行</span>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">ログが見つかりません</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        システムログファイルが存在しないか、ログエントリがありません。
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- ログレベルの説明 -->
    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">ログレベルについて</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                        <h3 class="font-medium text-gray-800">ERROR</h3>
                    </div>
                    <p class="text-sm text-gray-600">
                        システムエラーや例外が発生した場合のログ
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                        <h3 class="font-medium text-gray-800">WARNING</h3>
                    </div>
                    <p class="text-sm text-gray-600">
                        注意が必要な状況や非推奨機能の使用
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                        <h3 class="font-medium text-gray-800">INFO</h3>
                    </div>
                    <p class="text-sm text-gray-600">
                        一般的な情報メッセージやシステムの動作状況
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                        <h3 class="font-medium text-gray-800">DEBUG</h3>
                    </div>
                    <p class="text-sm text-gray-600">
                        開発時のデバッグ情報（本番環境では表示されません）
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                        <h3 class="font-medium text-gray-800">EMERGENCY</h3>
                    </div>
                    <p class="text-sm text-gray-600">
                        システムが使用不可能な緊急事態
                    </p>
                </div>
                
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-pink-500 rounded-full mr-2"></span>
                        <h3 class="font-medium text-gray-800">CRITICAL</h3>
                    </div>
                    <p class="text-sm text-gray-600">
                        重大なエラーで即座の対応が必要
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ログ管理のヒント -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">ログ管理のヒント</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>ERRORレベルのログは優先的に確認してください</li>
                        <li>ログファイルが大きくなりすぎた場合は、定期的にローテーションすることをお勧めします</li>
                        <li>本番環境では、DEBUGレベルのログは無効にしてパフォーマンスを向上させてください</li>
                        <li>重要なエラーについては、アラート通知の設定を検討してください</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshLogs() {
    location.reload();
}

// ログコンテンツのカラーハイライト
document.addEventListener('DOMContentLoaded', function() {
    const logContent = document.getElementById('log-content');
    if (logContent) {
        let content = logContent.innerHTML;
        
        // ログレベルに応じて色分け
        content = content.replace(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.ERROR:/g, 
            '<span class="text-red-400">[$1] local.ERROR:</span>');
        content = content.replace(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.WARNING:/g, 
            '<span class="text-yellow-400">[$1] local.WARNING:</span>');
        content = content.replace(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.INFO:/g, 
            '<span class="text-blue-400">[$1] local.INFO:</span>');
        content = content.replace(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.DEBUG:/g, 
            '<span class="text-green-400">[$1] local.DEBUG:</span>');
        content = content.replace(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.EMERGENCY:/g, 
            '<span class="text-purple-400">[$1] local.EMERGENCY:</span>');
        content = content.replace(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.CRITICAL:/g, 
            '<span class="text-pink-400">[$1] local.CRITICAL:</span>');
        
        // スタックトレースの強調
        content = content.replace(/(Stack trace:)/g, '<span class="text-red-300">$1</span>');
        content = content.replace(/(#\d+\s+[^\n]+)/g, '<span class="text-gray-400">$1</span>');
        
        logContent.innerHTML = content;
    }
});

// 自動スクロールを最下部に設定
document.addEventListener('DOMContentLoaded', function() {
    const logContainer = document.querySelector('.bg-gray-900');
    if (logContainer) {
        logContainer.scrollTop = logContainer.scrollHeight;
    }
});
</script>
@endsection