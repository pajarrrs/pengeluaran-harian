@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
    <div class="flex items-center justify-between mb-5 animate-in">
        <h1 class="text-xl font-semibold">Kategori</h1>
        <button onclick="toggleForm()" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-emerald-700">+ Tambah</button>
    </div>

    <div id="createForm" class="hidden animate-in bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 mb-5">
        <h2 class="text-sm font-medium mb-3">Tambah Kategori</h2>
        <form method="POST" action="{{ route('categories.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-2.5">
            @csrf
            <input type="text" name="name" placeholder="Nama kategori" required class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 placeholder-gray-400 outline-none focus:border-blue-500">
            <input type="text" name="emoji" placeholder="Emoji (contoh: 🍔)" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 placeholder-gray-400 outline-none focus:border-blue-500">
            <input type="text" name="color" placeholder="Warna (contoh: #ef4444)" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 placeholder-gray-400 outline-none focus:border-blue-500">
            <input type="number" name="budget" placeholder="Budget (Rp)" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 placeholder-gray-400 outline-none focus:border-blue-500">
            <button type="submit" class="md:col-span-4 bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Simpan</button>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @forelse ($categories as $c)
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-3.5 border-t-[3px] hover:shadow-sm transition-shadow" style="border-top-color: {{ $c->color ?? '#d1d5db' }}">
                <div class="text-2xl mb-1.5">{{ $c->emoji ?? '📌' }}</div>
                <h3 class="font-medium text-sm">{{ $c->name }}</h3>
                @if ($c->budget)
                    <p class="text-xs text-emerald-600 dark:text-emerald-400">Budget: Rp {{ number_format($c->budget, 0, ',', '.') }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $c->expenses_count }} pengeluaran
                    @if ($c->is_default)
                        <span class="text-blue-500">· default</span>
                    @endif
                </p>
                <div class="flex gap-2.5 mt-2.5 pt-2.5 border-t border-gray-100 dark:border-gray-800 text-xs">
                    <a href="{{ route('categories.edit', $c) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('categories.destroy', $c) }}" onsubmit="return confirm('Hapus kategori {{ $c->name }}?')">
                        @csrf @method('DELETE')
                        <button class="text-red-500 dark:text-red-400 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-400 py-8">Belum ada kategori.</div>
        @endforelse
    </div>
@endsection

@push('scripts')
<script>
function toggleForm() {
    document.getElementById('createForm').classList.toggle('hidden');
}
</script>
@endpush