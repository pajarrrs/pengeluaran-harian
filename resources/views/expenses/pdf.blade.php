<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { margin: 0 0 16px; color: #666; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f3f4f6; font-weight: 600; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .total-row td { border-top: 2px solid #333; padding-top: 10px; font-weight: 700; }
    </style>
</head>
<body>
    <h1>📊 Laporan Pengeluaran</h1>
    <p>
        @if ($startDate && $endDate)
            {{ $startDate }} — {{ $endDate }}
        @else
            {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
        @endif
    </p>

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
                    <td>{{ $e->date->format('d M Y') }}</td>
                    <td>{{ $e->category->emoji ?? '' }} {{ $e->category->name }}</td>
                    <td>{{ $e->description ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">Total</td>
                <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>