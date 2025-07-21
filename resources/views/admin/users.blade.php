@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">ユーザー管理</h1>
        <a href="{{ route('admin.users.create') }}" 
           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            新規ユーザー作成
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
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                名前
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                メールアドレス
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ロール
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
                        @foreach($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $user->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $role = $user->roles->first();
                                        $roleColors = [
                                            '管理者' => 'bg-red-100 text-red-800',
                                            'マネージャー' => 'bg-purple-100 text-purple-800',
                                            '一般スタッフ' => 'bg-green-100 text-green-800',
                                            '閲覧者' => 'bg-gray-100 text-gray-800',
                                        ];
                                    @endphp
                                    @if($role)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $roleColors[$role->name] ?? 'bg-blue-100 text-blue-800' }}">
                                            {{ $role->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            未割当
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            編集
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('このユーザーを削除してもよろしいですか？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900">
                                                    削除
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection