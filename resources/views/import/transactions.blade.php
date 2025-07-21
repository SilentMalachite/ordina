@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">取引データインポート</h1>
            <a href="{{ route('import.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                インポート一覧へ
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
                <a href="{{ route('job-statuses.index') }}" class="underline text-green-800 hover:text-green-900 ml-2">
                    ジョブ状況を確認
                </a>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-orange-700">
                        <strong>重要:</strong> 取引データをインポートする前に、関連する商品と顧客が登録されている必要があります。
                        在庫数が自動的に減少しますので、正確なデータを入力してください。
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">取引データのアップロード</h2>
            
            <form method="POST" action="{{ route('import.transactions.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-6">
                    <label for="file" class="block text-gray-700 text-sm font-bold mb-2">
                        CSVファイルを選択 <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="file" id="file" accept=".csv,.txt,.xlsx,.xls"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('file') border-red-500 @enderror"
                        required>
                    <p class="text-sm text-gray-600 mt-1">対応形式: CSV, TXT, XLSX, XLS (最大10MB)</p>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="has_header" value="1" checked
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">1行目をヘッダー行として扱う</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        インポート実行
                    </button>
                    <a href="{{ route('import.template', 'transactions') }}" 
                       class="text-blue-600 hover:text-blue-800 font-medium">
                        テンプレートをダウンロード
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">取引データのフォーマット</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border text-left text-sm font-medium text-gray-700">列名</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium text-gray-700">必須</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium text-gray-700">説明</th>
                            <th class="px-4 py-2 border text-left text-sm font-medium text-gray-700">例</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-2 border text-sm">取引タイプ</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">「売上」または「貸出」</td>
                            <td class="px-4 py-2 border text-sm">売上 / 貸出</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">顧客名</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">登録済みの顧客名</td>
                            <td class="px-4 py-2 border text-sm">山田太郎</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border text-sm">商品コード</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">登録済みの商品コード</td>
                            <td class="px-4 py-2 border text-sm font-mono">PRD-001</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">数量</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">取引数量</td>
                            <td class="px-4 py-2 border text-sm font-mono">5</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border text-sm">単価</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">取引単価（円）</td>
                            <td class="px-4 py-2 border text-sm font-mono">75000</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">取引日</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">取引実施日</td>
                            <td class="px-4 py-2 border text-sm">2025-07-20</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border text-sm">備考</td>
                            <td class="px-4 py-2 border text-sm text-gray-400">任意</td>
                            <td class="px-4 py-2 border text-sm">取引に関する備考</td>
                            <td class="px-4 py-2 border text-sm">月末特別価格</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-sm text-gray-600">
                <p class="font-semibold mb-2">注意事項:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>顧客名と商品コードは事前に登録されている必要があります</li>
                    <li>取引タイプは「売上」または「貸出」と正確に入力してください</li>
                    <li>在庫数が不足している場合、その取引はスキップされます</li>
                    <li>日付形式は「YYYY-MM-DD」または「YYYY/MM/DD」を使用してください</li>
                    <li>数値項目（数量、単価）にはカンマを含めないでください</li>
                    <li>貸出の場合、返却予定日は別途管理画面から設定してください</li>
                </ul>
            </div>

            <div class="mt-4 p-4 bg-blue-50 rounded">
                <p class="text-sm text-blue-800">
                    <strong>ヒント:</strong> 大量のデータをインポートする場合は、少量のデータでテストを行ってから本番データをインポートすることをお勧めします。
                </p>
            </div>
        </div>
    </div>
</div>
@endsection