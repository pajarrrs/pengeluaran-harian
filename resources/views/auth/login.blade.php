<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses — Pengeluaran Harian</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-xl shadow-lg p-6">
        <div class="text-center mb-6">
            <div class="text-4xl mb-2">💰</div>
            <h1 class="text-xl font-bold">Pengeluaran Harian</h1>
            <p class="text-sm text-gray-500">Masukkan kode akses</p>
        </div>

        <form method="POST" action="{{ route('auth') }}" class="space-y-4">
            @csrf
            <input type="password" name="code" placeholder="Kode akses" autofocus
                   class="w-full border rounded-lg px-4 py-3 text-center text-lg tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500">
            @if (session('error'))
                <p class="text-red-500 text-sm text-center">{{ session('error') }}</p>
            @endif
            <button type="submit"
                    class="w-full bg-blue-600 text-white rounded-lg py-3 font-medium hover:bg-blue-700 transition">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>
