@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">データ管理</h1>

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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- システム統計 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">システム統計</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">商品数:</span>
                        <span class="font-medium">{{ number_format($stats['products_count']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">顧客数:</span>
                        <span class="font-medium">{{ number_format($stats['customers_count']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">取引数:</span>
                        <span class="font-medium">{{ number_format($stats['transactions_count']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">在庫調整数:</span>
                        <span class="font-medium">{{ number_format($stats['adjustments_count']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">データベースサイズ:</span>
                        <span class="font-medium">{{ number_format($stats['database_size'] / 1024 / 1024, 2) }} MB</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- バックアップ機能 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">データバックアップ</h2>
                <p class="text-gray-600 mb-4">
                    システムの全データをJSONファイルとしてバックアップできます。
                </p>
                <button id="backup-btn" 
                        class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    バックアップを作成
                </button>
                <div id="backup-status" class="mt-2 text-sm"></div>
            </div>
        </div>
    </div>

    <!-- データ削除機能 -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-red-600">危険な操作</h2>
            <p class="text-gray-600 mb-6">
                以下の操作は取り消すことができません。実行前に必ずバックアップを作成してください。
            </p>

            <form method="POST" action="{{ route('admin.clear-data') }}" 
                  onsubmit="return confirmDataClear(event)" class="space-y-6">
                @csrf

                <div>
                    <label for="data_type" class="block text-sm font-medium text-gray-700 mb-2">
                        削除するデータタイプ
                    </label>
                    <select id="data_type" 
                            name="data_type" 
                            required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="">選択してください</option>
                        <option value="transactions">取引データのみ（在庫調整含む）</option>
                        <option value="products">商品データ（関連する取引データも削除）</option>
                        <option value="customers">顧客データ（関連する取引データも削除）</option>
                        <option value="all">全データ（商品・顧客・取引・締め日）</option>
                    </select>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">警告</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>この操作は元に戻すことができません。削除されたデータは復旧できません。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               id="confirmation" 
                               name="confirmation" 
                               required
                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">
                            上記の警告を理解し、データの削除を実行することを確認します
                        </span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        データを削除
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('backup-btn').addEventListener('click', function() {
    const btn = this;
    const status = document.getElementById('backup-status');
    
    btn.disabled = true;
    btn.textContent = 'バックアップ作成中...';
    status.textContent = '';
    status.className = 'mt-2 text-sm';
    
    fetch('{{ route("admin.backup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            status.textContent = `バックアップが正常に作成されました: ${data.backup_file}`;
            status.className = 'mt-2 text-sm text-green-600';
            
            // バックアップファイルのダウンロードリンクを表示
            const downloadLink = document.createElement('a');
            downloadLink.href = `{{ route('admin.backup.download', '') }}/${data.backup_file}`;
            downloadLink.textContent = 'ダウンロード';
            downloadLink.className = 'ml-2 text-blue-600 hover:text-blue-800 underline';
            status.appendChild(downloadLink);
        } else {
            status.textContent = `エラー: ${data.message}`;
            status.className = 'mt-2 text-sm text-red-600';
        }
    })
    .catch(error => {
        status.textContent = `エラーが発生しました: ${error.message}`;
        status.className = 'mt-2 text-sm text-red-600';
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'バックアップを作成';
    });
});

function confirmDataClear(event) {
    const dataType = document.getElementById('data_type').value;
    const confirmation = document.getElementById('confirmation').checked;
    
    if (!dataType) {
        alert('削除するデータタイプを選択してください。');
        event.preventDefault();
        return false;
    }
    
    if (!confirmation) {
        alert('確認チェックボックスにチェックを入れてください。');
        event.preventDefault();
        return false;
    }
    
    const typeMap = {
        'transactions': '取引データ',
        'products': '商品データ',
        'customers': '顧客データ',
        'all': '全データ'
    };
    
    const confirmed = confirm(
        `本当に${typeMap[dataType]}を削除しますか？\n\n` +
        'この操作は取り消すことができません。\n' +
        'バックアップを作成済みであることを確認してから実行してください。'
    );
    
    if (!confirmed) {
        event.preventDefault();
        return false;
    }
    
    return true;
}
</script>
@endsection