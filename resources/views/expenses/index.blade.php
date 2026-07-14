@extends('layouts.app')
@section('title', 'Pengeluaran')

@section('content')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5 animate-in">
        <h1 class="text-xl font-semibold">Pengeluaran</h1>
        <div class="flex flex-wrap gap-1.5 items-center">
            <form method="GET" class="flex flex-wrap gap-1.5 items-center">
                <input type="search" name="search" placeholder="Cari..." value="{{ $search ?? '' }}" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500 w-28">
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500 w-32">
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500 w-32">
                <select name="month" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endforeach
                </select>
                <select name="year" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700">Filter</button>
                <a href="{{ route('export.pdf', request()->query()) }}" class="text-gray-500 dark:text-gray-400 px-3 py-1.5 rounded-lg text-sm border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">PDF</a>
            </form>
            <button onclick="toggleForm('createForm')" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-emerald-700">+ Tambah</button>
            <button onclick="toggleForm('importForm')" class="text-gray-500 dark:text-gray-400 px-3 py-1.5 rounded-lg text-sm border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">Import</button>
        </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-950 border border-blue-100 dark:border-blue-900 rounded-xl px-4 py-3 mb-4">
        <p class="text-xs text-blue-600 dark:text-blue-400">Total bulan ini</p>
        <p class="text-xl font-semibold text-blue-700 dark:text-blue-300">Rp {{ number_format($total, 0, ',', '.') }}</p>
    </div>

    <div id="createForm" class="hidden animate-in bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 mb-4">
        <h2 class="text-sm font-medium mb-3">Tambah Transaksi</h2>
        <form method="POST" action="{{ route('expenses.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-2.5">
            @csrf
            <select name="category_id" required class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
                <option value="">Pilih Kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->emoji }} {{ $c->name }}</option>
                @endforeach
            </select>
            <input type="number" name="amount" placeholder="Jumlah (Rp)" required class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 placeholder-gray-400 outline-none focus:border-blue-500">
            <input type="text" name="description" placeholder="Deskripsi" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 placeholder-gray-400 outline-none focus:border-blue-500">
            <input type="date" name="date" value="{{ now()->toDateString() }}" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
            <div class="md:col-span-4 flex flex-wrap items-center gap-3">
                <label class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                    <input type="checkbox" onchange="var f=document.getElementById('intervalField');var s=f.querySelector('select');f.classList.toggle('hidden',!this.checked);s.disabled=!this.checked">
                    <span>Berulang</span>
                </label>
                <div id="intervalField" class="hidden flex items-center gap-1.5">
                    <span class="text-sm text-gray-500">setiap</span>
                    <select name="recurring_interval" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none" disabled>
                        <option value="7">7 hari</option>
                        <option value="14">14 hari</option>
                        <option value="30">30 hari</option>
                        <option value="60">60 hari</option>
                        <option value="365">365 hari</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="md:col-span-4 bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Simpan</button>
        </form>
    </div>

    <div id="importForm" class="hidden animate-in bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 mb-4">
        <h2 class="text-sm font-medium mb-3">Import CSV</h2>
        <p class="text-xs text-gray-500 mb-2">Format: <code>Tanggal;Kategori;Jumlah;Deskripsi;Sumber</code></p>
        <form method="POST" action="{{ route('import.csv') }}" enctype="multipart/form-data" class="flex flex-wrap gap-2 items-end">
            @csrf
            <input type="file" name="csv" accept=".csv,.txt" required class="text-sm text-gray-600 dark:text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950 dark:file:text-blue-400">
            <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700">Import</button>
        </form>
    </div>

    <div class="hidden md:block bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                    <th class="text-left px-4 py-2.5 text-gray-600 dark:text-gray-400 font-medium">Tanggal</th>
                    <th class="text-left px-4 py-2.5 text-gray-600 dark:text-gray-400 font-medium">Kategori</th>
                    <th class="text-left px-4 py-2.5 text-gray-600 dark:text-gray-400 font-medium">Deskripsi</th>
                    <th class="text-right px-4 py-2.5 text-gray-600 dark:text-gray-400 font-medium">Jumlah</th>
                    <th class="text-center px-4 py-2.5 text-gray-600 dark:text-gray-400 font-medium">Sumber</th>
                    <th class="text-center px-4 py-2.5 text-gray-600 dark:text-gray-400 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $e)
                    <tr class="border-b border-gray-100 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800/30">
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ $e->date->format('d M Y') }}</td>
                        <td class="px-4 py-2.5">{{ $e->category->emoji ?? '' }} {{ $e->category->name }}</td>
                        <td class="px-4 py-2.5 text-gray-500">{{ $e->description ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <span x-data="{ editing: false, amount: '{{ $e->amount }}' }">
                                <template x-if="!editing">
                                    <span @click="editing = true; $nextTick(() => { $refs.input.focus(); $refs.input.select(); })" class="font-medium cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-950 rounded px-1 -mx-1 {{ $e->category->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                                        {{ $e->category->type === 'income' ? '+' : '-' }} Rp {{ number_format($e->amount, 0, ',', '.') }}
                                    </span>
                                </template>
                                <template x-if="editing">
                                    <form @submit.prevent="fetch('{{ route('expenses.inline-update', $e) }}', { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ amount: $refs.input.value }) }).then(r => r.json()).then(d => { if(d.success) { amount = $refs.input.value; editing = false; window.location.reload(); } })" class="inline">
                                        @method('PATCH')
                                        @csrf
                                        <input x-ref="input" type="number" :value="amount" @click.stop @keydown.escape="editing = false" @click.away="editing = false" class="w-24 border border-blue-400 rounded px-1.5 py-0.5 text-sm font-medium text-right dark:bg-gray-700 dark:text-gray-100 dark:border-blue-500 outline-none">
                                    </form>
                                </template>
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $e->source === 'whatsapp' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : ($e->source === 'telegram' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400') }}">{{ $e->source === 'whatsapp' ? 'WA' : ($e->source === 'telegram' ? 'Tele' : 'Web') }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <div class="flex justify-center gap-2 text-xs">
                                <a href="{{ route('expenses.edit', $e) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('expenses.destroy', $e) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 dark:text-red-400 hover:underline">Hapus</button>
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

    <div class="md:hidden space-y-2.5">
        @forelse ($expenses as $e)
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-3.5 border-l-[3px]" style="border-left-color: {{ $e->category->color ?? '#d1d5db' }}">
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <span>{{ $e->category->emoji ?? '📌' }}</span>
                        <span class="font-medium text-sm">{{ $e->category->name }}</span>
                    </div>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $e->source === 'whatsapp' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : ($e->source === 'telegram' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400') }}">{{ $e->source === 'whatsapp' ? 'WA' : ($e->source === 'telegram' ? 'Tele' : 'Web') }}</span>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-base font-semibold {{ $e->category->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                            {{ $e->category->type === 'income' ? '+' : '-' }} Rp {{ number_format($e->amount, 0, ',', '.') }}
                        </p>
                        @if ($e->description)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $e->description }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">{{ $e->date->format('d M Y') }}</p>
                    </div>
                    <div class="flex gap-2 text-xs">
                        <a href="{{ route('expenses.edit', $e) }}" class="text-blue-600 dark:text-blue-400">Edit</a>
                        <form method="POST" action="{{ route('expenses.destroy', $e) }}" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 dark:text-red-400">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-400 py-8">Belum ada pengeluaran.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $expenses->appends(request()->query())->links() }}
    </div>
@endsection

@push('scripts')
<script>
function toggleForm(id) {
    document.getElementById(id).classList.toggle('hidden');
}
document.getElementById('createForm').querySelector('form').addEventListener('submit', function() {
    var cb = this.querySelector('[type="checkbox"]');
    if (!cb) return;
    var isRecurring = !document.getElementById('intervalField').classList.contains('hidden') ? '1' : '0';
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'is_recurring';
    input.value = isRecurring;
    this.appendChild(input);
});
</script>
@endpush