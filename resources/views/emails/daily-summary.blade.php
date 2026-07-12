<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Pengeluaran</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:24px 16px;">
        <tr>
            <td align="center">
                <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;">
                    <tr>
                        <td style="background:#ffffff;border-radius:12px;padding:32px 28px;text-align:left;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                            <h1 style="font-size:20px;margin:0 0 4px;color:#111827;">Ringkasan Pengeluaran</h1>
                            <p style="font-size:13px;color:#6b7280;margin:0 0 24px;">{{ $summary['date'] }}</p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="background:#f3f4f6;border-radius:8px;padding:12px 16px;width:33.33%;text-align:center;">
                                        <p style="font-size:11px;color:#6b7280;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px;">Hari Ini</p>
                                        <p style="font-size:17px;font-weight:700;color:#111827;margin:0;">Rp {{ number_format($summary['todayTotal'], 0, ',', '.') }}</p>
                                        <p style="font-size:11px;color:#9ca3af;margin:2px 0 0;">{{ $summary['todayCount'] }} transaksi</p>
                                    </td>
                                    <td width="8"></td>
                                    <td style="background:#f3f4f6;border-radius:8px;padding:12px 16px;width:33.33%;text-align:center;">
                                        <p style="font-size:11px;color:#6b7280;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px;">Bulan Ini</p>
                                        <p style="font-size:17px;font-weight:700;color:#111827;margin:0;">Rp {{ number_format($summary['monthTotal'], 0, ',', '.') }}</p>
                                    </td>
                                </tr>
                            </table>

                            @if (count($summary['categories']) > 0)
                                <h2 style="font-size:13px;margin:0 0 8px;color:#374151;">Transaksi Hari Ini</h2>
                                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                    @foreach ($summary['categories'] as $cat)
                                        <tr>
                                            <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;font-size:13px;color:#1f2937;">
                                                <span>{{ $cat['emoji'] }}</span> {{ $cat['name'] }}
                                            </td>
                                            <td style="padding:6px 0;border-bottom:1px solid #f3f4f6;font-size:13px;font-weight:600;color:#111827;text-align:right;white-space:nowrap;">
                                                Rp {{ number_format($cat['total'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <p style="font-size:13px;color:#9ca3af;margin:0 0 24px;">Tidak ada pengeluaran hari ini.</p>
                            @endif

                            @if (count($budgetAlerts) > 0)
                                <h2 style="font-size:13px;margin:0 0 8px;color:#92400e;">Peringatan Budget</h2>
                                @foreach ($budgetAlerts as $a)
                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:8px;background:#fffbeb;border-radius:8px;padding:10px 14px;border:1px solid #fde68a;">
                                        <tr>
                                            <td style="font-size:13px;font-weight:500;color:#92400e;">
                                                {{ $a['emoji'] }} {{ $a['name'] }}
                                            </td>
                                            <td style="font-size:13px;font-weight:600;color:#92400e;text-align:right;">
                                                {{ $a['percentage'] >= 100 ? 'Over!' : $a['percentage'] . '%' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="padding-top:4px;">
                                                <table width="100%" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:4px;height:6px;overflow:hidden;">
                                                    <tr>
                                                        <td width="{{ min($a['percentage'], 100) }}%" style="background:#f59e0b;height:6px;font-size:1px;line-height:1px;">&nbsp;</td>
                                                        <td style="height:6px;">&nbsp;</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="padding-top:2px;font-size:11px;color:#b45309;">
                                                Rp {{ number_format($a['total'], 0, ',', '.') }} / Rp {{ number_format($a['budget'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </table>
                                @endforeach
                            @endif

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ config('app.url') }}" style="display:inline-block;background:#2563eb;color:#ffffff;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;">Buka Dashboard</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align:center;font-size:11px;color:#9ca3af;margin:20px 0 0;border-top:1px solid #e5e7eb;padding-top:16px;">
                                Laporan ini dikirim otomatis oleh <span style="color:#6b7280;">Pengeluaran Harian</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>