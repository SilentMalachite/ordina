@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">顧客管理</h1>
        <a href="{{ route('customers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            顧客を登録
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <form method="GET" action="{{ route('customers.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="名前・メール・電話番号で検索" 
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <select name="type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">すべてのタイプ</option>
                    <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>個人</option>
                    <option value="company" {{ request('type') == 'company' ? 'selected' : '' }}>法人</option>
                </select>
                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    検索
                </button>
                <a href="{{ route('customers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    クリア
                </a>
            </form>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名前</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイプ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メール</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">電話番号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">取引数</th>
                    <th class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($customers as $customer)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $customer->name }}
                            @if($customer->contact_person)
                                <br><span class="text-xs text-gray-500">担当: {{ $customer->contact_person }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($customer->type === 'individual')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    個人
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    法人
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $customer->email ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $customer->phone ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $customer->transactions_count ?? 0 }}件
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('customers.show', $customer) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">詳細</a>
                            <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">編集</a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" 
                                    onclick="return confirm('本当に削除しますか？')">削除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            顧客が登録されていません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection