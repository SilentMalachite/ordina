@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">データインポート</h1>

    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    インポート前に必ずデータのバックアップを作成してください。
                    正しいフォーマットでデータを準備するため、各テンプレートをダウンロードしてご利用ください。
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-4">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">商品データ</h2>
            <p class="text-gray-600 mb-4">商品マスタデータの一括インポート</p>
            <div class="space-y-2">
                <a href="{{ route('import.template', 'products') }}" 
                   class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded text-center">
                    テンプレートダウンロード
                </a>
                <a href="{{ route('import.products') }}" 
                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                    インポート画面へ
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-4">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">顧客データ</h2>
            <p class="text-gray-600 mb-4">顧客マスタデータの一括インポート</p>
            <div class="space-y-2">
                <a href="{{ route('import.template', 'customers') }}" 
                   class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded text-center">
                    テンプレートダウンロード
                </a>
                <a href="{{ route('import.customers') }}" 
                   class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                    インポート画面へ
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-4">
                <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">取引データ</h2>
            <p class="text-gray-600 mb-4">売上・貸出取引データの一括インポート</p>
            <div class="space-y-2">
                <a href="{{ route('import.template', 'transactions') }}" 
                   class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded text-center">
                    テンプレートダウンロード
                </a>
                <a href="{{ route('import.transactions') }}" 
                   class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                    インポート画面へ
                </a>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4">インポート手順</h3>
        <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
            <li>各データのテンプレートをダウンロードしてください</li>
            <li>テンプレートに従ってデータを入力します（日本語のヘッダー行は必須です）</li>
            <li>CSVファイルとして保存します（文字コードはUTF-8推奨）</li>
            <li>インポート画面でファイルを選択してアップロードします</li>
            <li>エラーがある場合は、エラー内容を確認して修正後に再度アップロードしてください</li>
        </ol>
    </div>

    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">注意事項</h3>
        <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
            <li>商品コードや顧客名は重複しないようにしてください</li>
            <li>取引データのインポート時は、商品と顧客が事前に登録されている必要があります</li>
            <li>日付形式は「YYYY-MM-DD」または「YYYY/MM/DD」を使用してください</li>
            <li>数値項目にはカンマを含めないでください</li>
            <li>最大ファイルサイズは10MBです</li>
        </ul>
    </div>
</div>
@endsection