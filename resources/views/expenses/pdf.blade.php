<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1f2937; margin: 30px 25px; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin: 0; color: #111827; font-weight: 700; }
        .header p { margin: 4px 0 0; color: #6b7280; font-size: 12px; }
        .summary { display: flex; gap: 15px; margin-bottom: 20px; }
        .summary-box { background: #f3f4f6; border-radius: 6px; padding: 10px 14px; flex: 1; }
        .summary-box .label { font-size: 9px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; }
        .summary-box .value { font-size: 16px; font-weight: 700; color: #111827; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #f3f4f6; padding: 8px 10px; text-align: left; font-weight: 600; font-size: 10px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.3px; border-bottom: 1px solid #e5e7eb; }
        td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .text-gray { color: #9ca3af; }
        .total-row td { border-top: 2px solid #2563eb; padding-top: 10px; font-weight: 700; font-size: 12px; border-bottom: none; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pengeluaran</h1>
        <p>
            @if ($startDate && $endDate)
                {{ now()->parse($startDate)->format('d M Y') }} — {{ now()->parse($endDate)->format('d M Y') }}
            @else
                {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
            @endif
        </p>
    </div>

    @php
        $count = $expenses->count();
        $avg = $count > 0 ? round($total / $count) : 0;
        $catTotals = $expenses->groupBy(fn($e) => $e->category->name)->map(fn($items) => [
            'emoji' => $items->first()->category->emoji,
            'total' => $items->sum('amount'),
            'count' => $items->count(),
        ]);
    @endphp

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total</div>
            <div class="value">Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Transaksi</div>
            <div class="value">{{ $count }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Rata-rata</div>
            <div class="value">Rp {{ number_format($avg, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $e)
                <tr>
                    <td>{{ $e->date->format('d/m/Y') }}</td>
                    <td>{{ $e->category->emoji ?? '' }} {{ $e->category->name }}</td>
                    <td>{{ $e->description ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($catTotals->count() > 0)
        <table style="margin-top: 24px;">
            <thead>
                <tr>
                    <th colspan="2">Ringkasan per Kategori</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($catTotals as $name => $ct)
                    <tr>
                        <td colspan="2">{{ $ct['emoji'] }} {{ $name }}</td>
                        <td class="text-right">Rp {{ number_format($ct['total'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ $ct['count'] }}x</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Pengeluaran Harian &middot; {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>