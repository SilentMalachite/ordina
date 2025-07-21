@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">商品データインポート</h1>
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
            <h2 class="text-xl font-bold text-gray-800 mb-4">商品データのアップロード</h2>
            
            <form method="POST" action="{{ route('import.products.store') }}" enctype="multipart/form-data">
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
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        インポート実行
                    </button>
                    <a href="{{ route('import.template', 'products') }}" 
                       class="text-blue-600 hover:text-blue-800 font-medium">
                        テンプレートをダウンロード
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">商品データのフォーマット</h3>
            
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
                            <td class="px-4 py-2 border text-sm">商品コード</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">一意の商品識別コード</td>
                            <td class="px-4 py-2 border text-sm font-mono">PRD-001</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">商品名</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">商品の名称</td>
                            <td class="px-4 py-2 border text-sm">ノートPC A4サイズ</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border text-sm">在庫数</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">現在の在庫数量</td>
                            <td class="px-4 py-2 border text-sm font-mono">100</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">単価</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">仕入れ単価（円）</td>
                            <td class="px-4 py-2 border text-sm font-mono">50000</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border text-sm">売値</td>
                            <td class="px-4 py-2 border text-sm text-red-600">必須</td>
                            <td class="px-4 py-2 border text-sm">販売価格（円）</td>
                            <td class="px-4 py-2 border text-sm font-mono">75000</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border text-sm">説明</td>
                            <td class="px-4 py-2 border text-sm text-gray-400">任意</td>
                            <td class="px-4 py-2 border text-sm">商品の説明文</td>
                            <td class="px-4 py-2 border text-sm">15.6インチ、Core i5</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-sm text-gray-600">
                <p class="font-semibold mb-2">注意事項:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>商品コードは既存のものと重複しないようにしてください</li>
                    <li>数値項目（在庫数、単価、売値）にはカンマを含めないでください</li>
                    <li>空白セルは0または空文字として処理されます</li>
                    <li>文字コードはUTF-8を推奨します</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection