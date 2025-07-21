@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">顧客データインポート</h1>
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

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">顧客データのアップロード</h2>
            
            <form method="POST" action="{{ route('import.customers.store') }}" enctype="multipart/form-data">
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
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        インポート実行
                    </button>
                    <a href="{{ route('import.template', 'customers') }}" 
                       class="text-blue-600 hover:text-blue-800 font-medium">
                        テンプレートをダウンロード
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">顧客データのフォーマット</h3>
            
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
                            <td class="px-4 py-2 border text-sm">顧客名</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">個人名または会社名</td>
                            <td class="px-4 py-2 border text-sm">山田太郎 / 株式会社ABC</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">タイプ</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">individual（個人）またはcompany（法人）</td>
                            <td class="px-4 py-2 border text-sm font-mono">individual / company</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border text-sm">メールアドレス</td>
                            <td class="px-4 py-2 border text-sm text-gray-400">任意</td>
                            <td class="px-4 py-2 border text-sm">連絡先メールアドレス</td>
                            <td class="px-4 py-2 border text-sm">yamada@example.com</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">電話番号</td>
                            <td class="px-4 py-2 border text-sm text-gray-400">任意</td>
                            <td class="px-4 py-2 border text-sm">連絡先電話番号</td>
                            <td class="px-4 py-2 border text-sm">03-1234-5678</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border text-sm">住所</td>
                            <td class="px-4 py-2 border text-sm text-gray-400">任意</td>
                            <td class="px-4 py-2 border text-sm">住所情報</td>
                            <td class="px-4 py-2 border text-sm">東京都千代田区1-1-1</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">担当者</td>
                            <td class="px-4 py-2 border text-sm text-gray-400">任意</td>
                            <td class="px-4 py-2 border text-sm">法人の場合の担当者名</td>
                            <td class="px-4 py-2 border text-sm">鈴木一郎</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border text-sm">備考</td>
                            <td class="px-4 py-2 border text-sm text-gray-400">任意</td>
                            <td class="px-4 py-2 border text-sm">その他の情報</td>
                            <td class="px-4 py-2 border text-sm">優良顧客</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-sm text-gray-600">
                <p class="font-semibold mb-2">注意事項:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>タイプは「individual」（個人）または「company」（法人）を正確に入力してください</li>
                    <li>メールアドレスは有効な形式で入力してください</li>
                    <li>電話番号はハイフン付きでも入力可能です</li>
                    <li>法人の場合、担当者名の入力を推奨します</li>
                    <li>顧客名の重複は許可されますが、識別のため一意にすることを推奨します</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection