<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses — Pengeluaran Harian</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeInUp 0.3s ease-out both; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gray-50">
    <div class="w-full max-w-sm animate-in">
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm px-6 py-8">
            <div class="text-center mb-6">
                <div class="text-4xl mb-2">💰</div>
                <h1 class="text-lg font-semibold">Pengeluaran Harian</h1>
                <p class="text-sm text-gray-500">Masukkan kode akses</p>
            </div>
            <form method="POST" action="/auth" class="space-y-3">
                @csrf
                <input type="password" name="code" placeholder="******" autofocus
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-center text-lg tracking-widest outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                @if (session('error'))
                    <p class="text-red-500 text-sm text-center">{{ session('error') }}</p>
                @endif
                <button type="submit"
                        class="w-full bg-blue-600 text-white rounded-xl py-2.5 font-medium hover:bg-blue-700 transition-colors">
                    Masuk
                </button>
            </form>
        </div>
        <p class="text-center text-gray-400 text-xs mt-3">Catatan pengeluaran harian pribadi</p>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.error('SW init failed', err));
            });
        }
    </script>
</body>
</html>