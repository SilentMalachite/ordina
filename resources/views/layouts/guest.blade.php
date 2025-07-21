<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Ordina 在庫管理システム</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-blue-50 to-indigo-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="mb-8">
                <div class="bg-white rounded-full p-6 shadow-lg">
                    <svg class="w-16 h-16 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/>
                        <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <div class="w-full sm:max-w-lg mt-6 px-8 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-xl border border-gray-200">
                {{ $slot }}
            </div>
        </div>
        
        <footer class="text-center text-gray-500 text-sm mt-8 pb-6">
            <p>&copy; 2024 Ordina 在庫管理システム. All rights reserved.</p>
        </footer>
    </body>
</html>
