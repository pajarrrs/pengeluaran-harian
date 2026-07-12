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
        [x-cloak] { display: none !important; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeIn 0.3s ease-out both; }
        body { padding-bottom: env(safe-area-inset-bottom); }
    </style>
    @stack('styles')
</head>
<body x-data="{ openExport: false }" class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200 antialiased transition-colors">
    {{-- Desktop nav --}}
    <nav class="hidden md:flex bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50 transition-colors">
        <div class="max-w-5xl mx-auto w-full px-4 h-12 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="font-semibold text-base">Pengeluaran Harian</a>
            <div class="flex items-center gap-0.5 text-sm">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">Dashboard</a>
                <a href="{{ route('expenses.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('expenses.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">Pengeluaran</a>
                <a href="{{ route('categories.index') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">Kategori</a>
                <button @click="openExport = true" x-data class="px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">PDF</button>
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
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-2 py-1.5 rounded-md {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                <span class="text-[10px] font-medium">Dashboard</span>
            </a>
            <a href="{{ route('expenses.index') }}" class="flex flex-col items-center px-2 py-1.5 rounded-md {{ request()->routeIs('expenses.*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                <span class="text-[10px] font-medium">Pengeluaran</span>
            </a>
            <a href="{{ route('categories.index') }}" class="flex flex-col items-center px-2 py-1.5 rounded-md {{ request()->routeIs('categories.*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                <span class="text-[10px] font-medium">Kategori</span>
            </a>
            <button @click="openExport = true" class="flex flex-col items-center px-2 py-1.5 rounded-md text-gray-500 dark:text-gray-400">
                <span class="text-[10px] font-medium">PDF</span>
            </button>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="flex flex-col items-center px-2 py-1.5 rounded-md text-gray-500 dark:text-gray-400">
                    <span class="text-[10px] font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </nav>

    {{-- Export PDF Modal --}}
    <div x-show="openExport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30" @click="openExport = false" x-transition>
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 w-full max-w-sm p-5" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Export PDF</h2>
                <button @click="openExport = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
            </div>
            <form method="GET" action="{{ route('export.pdf') }}" class="space-y-3">
                <div class="flex gap-1.5 flex-wrap">
                    <button type="button" @click="$refs.sd.value='{{ now()->toDateString() }}'; $refs.ed.value='{{ now()->toDateString() }}'" class="text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">Hari ini</button>
                    <button type="button" @click="$refs.sd.value='{{ now()->startOfWeek()->toDateString() }}'; $refs.ed.value='{{ now()->toDateString() }}'" class="text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">Minggu ini</button>
                    <button type="button" @click="$refs.sd.value='{{ now()->startOfMonth()->toDateString() }}'; $refs.ed.value='{{ now()->toDateString() }}'" class="text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">Bulan ini</button>
                    <button type="button" @click="$refs.sd.value=''; $refs.ed.value=''" class="text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">Semua</button>
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs text-gray-500 mb-0.5">Dari</label>
                        <input x-ref="sd" type="date" name="start_date" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-800 dark:text-gray-300 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-0.5">Sampai</label>
                        <input x-ref="ed" type="date" name="end_date" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-800 dark:text-gray-300 outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Download PDF</button>
            </form>
        </div>
    </div>

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