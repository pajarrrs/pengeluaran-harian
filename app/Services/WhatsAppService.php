<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMessage(string $phoneNumberId, string $waId, string $message): void
    {
        $token = config('services.whatsapp.access_token');

        if (empty($token) || empty($phoneNumberId)) {
            Log::info('WhatsApp reply skipped (not configured)', [
                'to' => $waId,
                'message' => $message,
            ]);
            return;
        }

        try {
            Http::withToken($token)
                ->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $waId,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp reply failed', ['error' => $e->getMessage()]);
        }
    }

    public function handleCommand(string $waId, string $text): ?array
    {
        $lower = trim(mb_strtolower($text));

        if (preg_match('/^hapus(?:\s+terakhir)?$/', $lower)) {
            $expense = Expense::where('wa_id', $waId)->latest()->first();
            if (!$expense) {
                return ['type' => 'reply', 'message' => 'Tidak ada pengeluaran yang bisa dihapus.'];
            }
            $cat = $expense->category;
            $detail = "{$cat->emoji} {$cat->name}: Rp " . number_format($expense->amount, 0, ',', '.');
            $expense->delete();
            return ['type' => 'reply', 'message' => "✅ Berhasil dihapus!\n{$detail}"];
        }

        if (preg_match('/^edit\s+terakhir\s+jadi\s+(\d[\d.,]*)/', $lower, $m)) {
            $amount = (int) str_replace(['.', ','], '', $m[1]);
            $expense = Expense::where('wa_id', $waId)->latest()->first();
            if (!$expense) {
                return ['type' => 'reply', 'message' => 'Tidak ada pengeluaran yang bisa diedit.'];
            }
            $expense->update(['amount' => $amount]);
            $cat = $expense->category;
            return ['type' => 'reply', 'message' => "✅ Diubah jadi Rp " . number_format($amount, 0, ',', '.') . "\n{$cat->emoji} {$cat->name}" . ($expense->description ? " — {$expense->description}" : '')];
        }

        return null;
    }

    public function getDailySummary(): string
    {
        $today = now()->toDateString();
        $month = now()->month;
        $year = now()->year;

        $todayTotal = Expense::whereDate('date', $today)->sum('amount');
        $todayCount = Expense::whereDate('date', $today)->count();
        $monthTotal = Expense::whereMonth('date', $month)->whereYear('date', $year)->sum('amount');

        $perCategory = Category::with(['expenses' => fn($q) => $q->whereDate('date', $today)])
            ->get()
            ->map(fn($c) => [
                'emoji' => $c->emoji,
                'name' => $c->name,
                'total' => $c->expenses->sum('amount'),
            ])
            ->filter(fn($c) => $c['total'] > 0);

        $lines = ["📊 Ringkasan " . now()->format('d M Y')];

        if ($todayCount > 0) {
            $lines[] = "";
            $lines[] = "Hari ini: Rp " . number_format($todayTotal, 0, ',', '.') . " ({$todayCount} transaksi)";
            foreach ($perCategory as $c) {
                $lines[] = "{$c['emoji']} {$c['name']}: Rp " . number_format($c['total'], 0, ',', '.');
            }
        } else {
            $lines[] = "";
            $lines[] = "Tidak ada pengeluaran hari ini.";
        }

        $lines[] = "";
        $lines[] = "Bulan ini: Rp " . number_format($monthTotal, 0, ',', '.');

        return implode("\n", $lines);
    }

    public function getBudgetAlerts(): ?string
    {
        $month = now()->month;
        $year = now()->year;

        $alerts = Category::with(['expenses' => fn($q) => $q->whereMonth('date', $month)->whereYear('date', $year)])
            ->get()
            ->filter(function ($c) {
                if (!$c->budget) return false;
                $total = $c->expenses->sum('amount');
                return $total >= $c->budget * 0.8;
            })
            ->map(fn($c) => [
                'emoji' => $c->emoji,
                'name' => $c->name,
                'total' => $c->expenses->sum('amount'),
                'budget' => $c->budget,
            ]);

        if ($alerts->isEmpty()) return null;

        $lines = ["⚠️ *Peringatan Budget*"];
        foreach ($alerts as $a) {
            $pct = round(($a['total'] / $a['budget']) * 100);
            $lines[] = "{$a['emoji']} {$a['name']}: Rp " . number_format($a['total'], 0, ',', '.') . " / Rp " . number_format($a['budget'], 0, ',', '.') . " ({$pct}%)";
            if ($pct >= 100) $lines[] = "⚠️ Over budget!";
        }

        return implode("\n", $lines);
    }

    public function getDailySummaryData(): array
    {
        $today = now()->toDateString();
        $month = now()->month;
        $year = now()->year;

        $todayTotal = Expense::whereDate('date', $today)->sum('amount');
        $todayCount = Expense::whereDate('date', $today)->count();
        $monthTotal = Expense::whereMonth('date', $month)->whereYear('date', $year)->sum('amount');

        $categories = Category::with(['expenses' => fn($q) => $q->whereDate('date', $today)])
            ->get()
            ->map(fn($c) => [
                'emoji' => $c->emoji,
                'name' => $c->name,
                'color' => $c->color,
                'total' => $c->expenses->sum('amount'),
            ])
            ->filter(fn($c) => $c['total'] > 0)
            ->values()
            ->toArray();

        return [
            'date' => now()->format('d M Y'),
            'todayTotal' => $todayTotal,
            'todayCount' => $todayCount,
            'monthTotal' => $monthTotal,
            'categories' => $categories,
        ];
    }

    public function getBudgetAlertsData(): array
    {
        $month = now()->month;
        $year = now()->year;

        return Category::with(['expenses' => fn($q) => $q->whereMonth('date', $month)->whereYear('date', $year)])
            ->get()
            ->filter(function ($c) {
                if (!$c->budget) return false;
                $total = $c->expenses->sum('amount');
                return $total >= $c->budget * 0.8;
            })
            ->map(fn($c) => [
                'emoji' => $c->emoji,
                'name' => $c->name,
                'color' => $c->color,
                'total' => $c->expenses->sum('amount'),
                'budget' => $c->budget,
                'percentage' => round(($c->expenses->sum('amount') / $c->budget) * 100),
            ])
            ->values()
            ->toArray();
    }
}
