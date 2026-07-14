@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5 animate-in">
        <h1 class="text-xl font-semibold">Dashboard</h1>
        <form method="GET" class="flex flex-wrap gap-1.5 items-center">
            <select name="category_id" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
                <option value="">Semua</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" {{ (int)$c->id === (int)($categoryId ?? 0) ? 'selected' : '' }}>{{ $c->emoji }} {{ $c->name }}</option>
                @endforeach
            </select>
            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500 w-32">
            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500 w-32">
            <select name="month" class="border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm bg-white dark:bg-gray-900 dark:text-gray-300 outline-none focus:border-blue-500">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" {{ (int)$m === (int)$month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
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
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Pemasukan Bulan Ini</p>
            <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($incomeTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">Total pendapatan bulan ini</p>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Pengeluaran Bulan Ini</p>
            <p class="text-lg font-semibold text-rose-600 dark:text-rose-400">Rp {{ number_format($expenseTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">{{ $count }} transaksi &middot; avg Rp {{ number_format($avgPerDay, 0, ',', '.') }}/hari</p>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-3.5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Sisa Saldo</p>
            <p class="text-lg font-semibold {{ $saldo < 0 ? 'text-red-500' : 'text-blue-600 dark:text-blue-400' }}">Rp {{ number_format($saldo, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">Selisih pemasukan dan pengeluaran</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 mb-5">
        <h2 class="text-sm font-medium mb-3">Transaksi Hari Ini</h2>
        @forelse ($todayExpenses as $e)
            <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-800 last:border-0 text-sm">
                <div class="flex items-center gap-2 min-w-0">
                    <span>{{ $e->category->emoji ?? '📌' }}</span>
                    <span class="font-medium">{{ $e->category->name }}</span>
                    @if ($e->description)
                        <span class="text-gray-400 truncate hidden sm:inline">— {{ $e->description }}</span>
                    @endif
                </div>
                <span class="font-medium shrink-0 {{ $e->category->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                    {{ $e->category->type === 'income' ? '+' : '-' }} Rp {{ number_format($e->amount, 0, ',', '.') }}
                </span>
            </div>
        @empty
            <p class="text-gray-400 text-sm py-1">Belum ada transaksi hari ini.</p>
        @endforelse
    </div>

    @if ($budgetAlerts->isNotEmpty())
        <div class="space-y-2 mb-5">
            @foreach ($budgetAlerts as $cat)
                @php $pct = min(round(($cat['total'] / $cat['budget']) * 100), 100); @endphp
                <div class="flex items-center gap-2.5 p-3 rounded-lg text-sm {{ $pct >= 100 ? 'bg-red-50 border border-red-200 text-red-700 dark:bg-red-950 dark:border-red-900 dark:text-red-400' : 'bg-amber-50 border border-amber-200 text-amber-700 dark:bg-amber-950 dark:border-amber-900 dark:text-amber-400' }}">
                    <span>{{ $cat['emoji'] }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between mb-0.5">
                            <span class="font-medium">{{ $cat['name'] }}</span>
                            <span>{{ $pct >= 100 ? 'Over!' : $pct . '%' }}</span>
                        </div>
                        <div class="w-full h-1 rounded-full bg-white/60 dark:bg-gray-700">
                            <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-red-400' : 'bg-amber-400' }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-xs mt-0.5 opacity-75">Rp {{ number_format($cat['total'], 0, ',', '.') }} / Rp {{ number_format($cat['budget'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
            <h2 class="text-sm font-medium mb-3">Per Kategori</h2>
            <canvas id="pieChart" height="180"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
            <h2 class="text-sm font-medium mb-3">Total per Kategori</h2>
            <canvas id="barChart" height="180"></canvas>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 mb-5">
        <h2 class="text-sm font-medium mb-3">Tren Mingguan</h2>
        <canvas id="weeklyChart" height="140"></canvas>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 mb-5">
        <h2 class="text-sm font-medium mb-3">Rincian per Kategori</h2>
        @forelse ($perCategory as $cat)
            @if ($cat['total'] > 0)
                <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-800 last:border-0 text-sm">
                    <div class="flex items-center gap-2">
                        <span>{{ $cat['emoji'] }}</span>
                        <span>{{ $cat['name'] }}</span>
                        @if ($cat['budget'])
                            <span class="text-xs text-gray-400">({{ round(($cat['total'] / max($cat['budget'], 1)) * 100) }}%)</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium {{ $cat['type'] === 'income' ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                            {{ $cat['type'] === 'income' ? '+' : '-' }} Rp {{ number_format($cat['total'], 0, ',', '.') }}
                        </span>
                        <span style="background: {{ $cat['color'] ?? '#d1d5db' }}" class="w-2.5 h-2.5 rounded-full inline-block"></span>
                    </div>
                </div>
            @endif
        @empty
            <p class="text-gray-400 text-sm py-1">Belum ada pengeluaran bulan ini.</p>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4">
        <h2 class="text-sm font-medium mb-3">Transaksi Terbaru</h2>
        @forelse ($recentExpenses as $e)
            <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-800 last:border-0 text-sm">
                <div class="flex items-center gap-2 min-w-0">
                    <span>{{ $e->category->emoji ?? '📌' }}</span>
                    <span class="font-medium">{{ $e->category->name }}</span>
                    @if ($e->description)
                        <span class="text-gray-400 truncate hidden sm:inline">— {{ $e->description }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="font-medium {{ $e->category->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                        {{ $e->category->type === 'income' ? '+' : '-' }} Rp {{ number_format($e->amount, 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $e->source === 'whatsapp' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : ($e->source === 'telegram' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400') }}">{{ $e->source === 'whatsapp' ? 'WA' : ($e->source === 'telegram' ? 'Tele' : 'Web') }}</span>
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm py-1">Belum ada transaksi.</p>
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
    data: { labels: labels.length ? labels : ['Belum ada data'], datasets: [{ data: data.length ? data : [1], backgroundColor: labels.length ? colors : ['#e5e7eb'] }] },
    options: { plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } } }
});

new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: labels.length ? labels : ['Belum ada data'],
        datasets: [{
            label: 'Total (Rp)',
            data: data.length ? data : [0],
            backgroundColor: labels.length ? colors.map(c => c + 'CC') : ['#e5e7eb'],
            borderColor: labels.length ? colors : ['#e5e7eb'],
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp' + v.toLocaleString('id-ID') } } }
    }
});

new Chart(document.getElementById('weeklyChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($weeklyLabels) !!},
        datasets: [{
            label: 'Total (Rp)',
            data: {!! json_encode($weeklyData) !!},
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.08)',
            fill: true,
            tension: 0.3,
            pointRadius: 3,
            borderWidth: 2,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp' + v.toLocaleString('id-ID') } } }
    }
});
</script>
@endpush