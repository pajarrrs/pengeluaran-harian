@extends('layouts.app')
@section('title', 'Pengeluaran')

@section('content')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold">Pengeluaran</h1>
        <div class="flex flex-wrap gap-2 items-center">
            <form method="GET" class="flex flex-wrap gap-2 items-center">
                <input type="search" name="search" placeholder="Cari deskripsi..." value="{{ $search ?? '' }}" class="border rounded px-2 py-1 text-sm w-36">
                <select name="month" class="border rounded px-2 py-1 text-sm">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endforeach
                </select>
                <select name="year" class="border rounded px-2 py-1 text-sm">
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Filter</button>
            </form>
            <button onclick="toggleForm()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">+ Tambah</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <p class="text-sm text-gray-500">Total bulan ini: <span class="font-bold text-lg">Rp {{ number_format($total, 0, ',', '.') }}</span></p>
    </div>

    <div id="createForm" class="hidden bg-white rounded-lg shadow p-4 mb-4">
        <h2 class="font-semibold mb-3">Tambah Pengeluaran</h2>
        <form method="POST" action="{{ route('expenses.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            @csrf
            <select name="category_id" required class="border rounded px-3 py-2 text-sm">
                <option value="">Pilih Kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->emoji }} {{ $c->name }}</option>
                @endforeach
            </select>
            <input type="number" name="amount" placeholder="Jumlah (Rp)" required class="border rounded px-3 py-2 text-sm">
            <input type="text" name="description" placeholder="Deskripsi (opsional)" class="border rounded px-3 py-2 text-sm">
            <input type="date" name="date" value="{{ now()->toDateString() }}" class="border rounded px-3 py-2 text-sm">
            <button type="submit" class="md:col-span-4 bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">Simpan</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-left px-4 py-3">Kategori</th>
                    <th class="text-left px-4 py-3">Deskripsi</th>
                    <th class="text-right px-4 py-3">Jumlah</th>
                    <th class="text-center px-4 py-3">Sumber</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $e)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $e->date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $e->category->emoji ?? '' }} {{ $e->category->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $e->description ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded {{ $e->source === 'whatsapp' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $e->source === 'whatsapp' ? 'WA' : 'Web' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('expenses.edit', $e) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('expenses.destroy', $e) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $expenses->appends(request()->query())->links() }}
    </div>
@endsection

@push('scripts')
<script>
function toggleForm() {
    document.getElementById('createForm').classList.toggle('hidden');
}
</script>
@endpush
