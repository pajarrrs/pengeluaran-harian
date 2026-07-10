@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <form method="GET" class="flex flex-wrap gap-2 items-center">
            <select name="month" class="border rounded px-2 py-1 text-sm">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" {{ (int)$m === (int)$month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select name="year" class="border rounded px-2 py-1 text-sm">
                @foreach (range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Total Pengeluaran</p>
            <p class="text-xl font-bold">Rp {{ number_format($total, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Transaksi</p>
            <p class="text-xl font-bold">{{ $count }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Rata-rata / Hari</p>
            <p class="text-xl font-bold">Rp {{ number_format($avgPerDay, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Tertinggi</p>
            <p class="text-xl font-bold">@if ($highest) Rp {{ number_format($highest->amount, 0, ',', '.') }}@else - @endif</p>
            @if ($highest)
                <p class="text-xs text-gray-400">{{ $highest->category->emoji }} {{ $highest->category->name }} {{ $highest->description ? '— '.$highest->description : '' }}</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-semibold mb-2">Per Kategori</h2>
            <canvas id="pieChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-semibold mb-2">Total per Kategori</h2>
            <canvas id="barChart" height="200"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold mb-3">Rincian per Kategori</h2>
        @forelse ($perCategory as $cat)
            @if ($cat['total'] > 0)
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div class="flex items-center gap-2">
                        <span>{{ $cat['emoji'] }}</span>
                        <span>{{ $cat['name'] }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                        <span style="background: {{ $cat['color'] }}" class="w-3 h-3 rounded-full inline-block"></span>
                    </div>
                </div>
            @endif
        @empty
            <p class="text-gray-400 text-sm">Belum ada pengeluaran bulan ini.</p>
        @endforelse
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="font-semibold mb-3">Transaksi Terbaru</h2>
        @forelse ($recentExpenses as $e)
            <div class="flex items-center justify-between py-2 border-b last:border-0 text-sm">
                <div class="flex items-center gap-2 min-w-0">
                    <span>{{ $e->category->emoji ?? '📌' }}</span>
                    <span class="truncate">{{ $e->category->name }}</span>
                    @if ($e->description)
                        <span class="text-gray-400 truncate hidden sm:inline">— {{ $e->description }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="font-medium">Rp {{ number_format($e->amount, 0, ',', '.') }}</span>
                    <span class="text-xs {{ $e->source === 'whatsapp' ? 'text-green-500' : 'text-blue-500' }}">{{ $e->source === 'whatsapp' ? 'WA' : 'Web' }}</span>
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm">Belum ada transaksi.</p>
        @endforelse
    </div>
@endsection

@push('scripts')
<script>
const labels = {!! json_encode($chartLabels) !!};
const data = {!! json_encode($chartData) !!};
const colors = {!! json_encode($chartColors) !!};

new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: { labels, datasets: [{ data, backgroundColor: colors }] },
    options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Total (Rp)',
            data,
            backgroundColor: colors,
            borderRadius: 4
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp' + v.toLocaleString('id-ID') } } }
    }
});
</script>
@endpush
