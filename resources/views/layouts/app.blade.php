<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Pengeluaran Harian')</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Catatan">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeIn 0.3s ease-out both; }
        body { padding-bottom: env(safe-area-inset-bottom); }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200 antialiased transition-colors">
    {{-- Desktop nav --}}
    <nav class="hidden md:flex bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50 transition-colors">
        <div class="max-w-5xl mx-auto w-full px-4 h-12 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="font-semibold text-base flex items-center gap-1.5">
                <span>💰</span> Pengeluaran Harian
            </a>
            <div class="flex items-center gap-0.5 text-sm">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">Dashboard</a>
                <a href="{{ route('expenses.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('expenses.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">Pengeluaran</a>
                <a href="{{ route('categories.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">Kategori</a>
                <form method="POST" action="{{ route('logout') }}" class="inline ml-1">
                    @csrf
                    <button class="px-3 py-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-red-600 dark:hover:text-red-400">Keluar</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Mobile bottom nav --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 z-50 transition-colors" style="padding-bottom: env(safe-area-inset-bottom)">
        <div class="flex items-center justify-around py-0.5">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0 px-2 py-1.5 rounded-md {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] font-medium">Dashboard</span>
            </a>
            <a href="{{ route('expenses.index') }}" class="flex flex-col items-center gap-0 px-2 py-1.5 rounded-md {{ request()->routeIs('expenses.*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="text-[10px] font-medium">Pengeluaran</span>
            </a>
            <a href="{{ route('categories.index') }}" class="flex flex-col items-center gap-0 px-2 py-1.5 rounded-md {{ request()->routeIs('categories.*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span class="text-[10px] font-medium">Kategori</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="flex flex-col items-center gap-0 px-2 py-1.5 rounded-md text-gray-500 dark:text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="text-[10px] font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </nav>

    {{-- Main content --}}
    <main class="max-w-5xl mx-auto px-4 py-5 pb-24 md:pb-5">
        @if (session('success'))
            <div class="animate-in mb-4 p-3 bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-900 text-emerald-700 dark:text-emerald-400 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="animate-in mb-4 p-3 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-400 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    if ('PushManager' in window) {
                        Notification.requestPermission().then(perm => {
                            if (perm === 'granted') {
                                reg.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: '{{ env("VAPID_PUBLIC_KEY") }}',
                                }).then(sub => {
                                    fetch('/api/push/subscribe', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify(sub.toJSON()),
                                    });
                                }).catch(() => {});
                            }
                        });
                    }
                }).catch(err => console.error('SW init failed', err));
            });
        }
    </script>
</body>
</html>