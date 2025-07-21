<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ロール管理
            </h2>
            <a href="{{ route('roles.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                新規ロール作成
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ロール名
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        権限
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ユーザー数
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($roles as $role)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $role->name }}
                                                @if(in_array($role->name, ['管理者', 'マネージャー', '一般スタッフ', '閲覧者']))
                                                    <span class="ml-2 text-xs text-gray-500">(デフォルト)</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-gray-900">
                                                @if($role->name === '管理者')
                                                    <span class="text-green-600 font-semibold">全権限</span>
                                                @else
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($role->permissions as $permission)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                                {{ $permission->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $role->users()->count() }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                @if(!in_array($role->name, ['管理者', 'マネージャー', '一般スタッフ', '閲覧者']))
                                                    <a href="{{ route('roles.edit', $role->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        編集
                                                    </a>
                                                    @if($role->users()->count() === 0)
                                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline" onsubmit="return confirm('本当に削除しますか？');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                                削除
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">変更不可</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>