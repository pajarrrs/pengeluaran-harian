<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pengeluaran Harian')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center gap-6">
            <a href="{{ route('dashboard') }}" class="font-bold text-lg text-blue-600">💰 Pengeluaran Harian</a>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-blue-600">Dashboard</a>
            <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 hover:text-blue-600">Pengeluaran</a>
            <a href="{{ route('categories.index') }}" class="text-sm text-gray-600 hover:text-blue-600">Kategori</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-6">
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded text-sm">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
