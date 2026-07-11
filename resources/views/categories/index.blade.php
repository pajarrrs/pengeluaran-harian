@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Kategori</h1>
        <button onclick="toggleForm()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">+ Tambah</button>
    </div>

    <div id="createForm" class="hidden bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold mb-3">Tambah Kategori</h2>
        <form method="POST" action="{{ route('categories.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Nama kategori" required class="border rounded px-3 py-2 text-sm">
            <input type="text" name="emoji" placeholder="Emoji (contoh: 🍔)" class="border rounded px-3 py-2 text-sm">
            <input type="text" name="color" placeholder="Warna (contoh: #ef4444)" class="border rounded px-3 py-2 text-sm">
            <input type="number" name="budget" placeholder="Budget (Rp)" class="border rounded px-3 py-2 text-sm">
            <button type="submit" class="md:col-span-4 bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">Simpan</button>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @forelse ($categories as $c)
            <div class="bg-white rounded-lg shadow p-4 border-t-4" style="border-top-color: {{ $c->color ?? '#d1d5db' }}">
                <div class="text-3xl mb-2">{{ $c->emoji ?? '📌' }}</div>
                <h3 class="font-semibold">{{ $c->name }}</h3>
                @if ($c->budget)
                    <p class="text-xs text-green-600 mb-1">Budget: Rp {{ number_format($c->budget, 0, ',', '.') }}</p>
                @endif
                <p class="text-xs text-gray-400">
                    {{ $c->expenses_count }} pengeluaran
                    @if ($c->is_default)
                        <span class="text-blue-500">· default</span>
                    @endif
                </p>
                <div class="flex gap-2 mt-2">
                    <a href="{{ route('categories.edit', $c) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                    <form method="POST" action="{{ route('categories.destroy', $c) }}" onsubmit="return confirm('Hapus kategori {{ $c->name }}?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-xs">Hapus</button>
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
