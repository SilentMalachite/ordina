<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                新規ロール作成
            </h2>
            <a href="{{ route('roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('roles.store') }}">
                        @csrf

                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                ロール名 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                                required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-4">
                                権限の割り当て
                            </label>
                            
                            @foreach($permissions as $category => $categoryPermissions)
                                <div class="mb-6">
                                    <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ $category }}</h3>
                                    <div class="space-y-2 ml-4">
                                        @foreach($categoryPermissions as $permission)
                                            <label class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                    {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                <span class="ml-2 text-sm text-gray-600">
                                                    {{ $permission->name }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('roles.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                キャンセル
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                作成
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>