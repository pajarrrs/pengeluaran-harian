<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use App\Models\PendingInput;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
    }

    public function setWebhook(string $url): bool
    {
        $r = Http::post("https://api.telegram.org/bot{$this->token}/setWebhook", [
            'url' => $url,
        ]);
        return $r->successful() && ($r->json('ok') ?? false);
    }

    public function sendMessage(string $chatId, string $text): void
    {
        if (!$this->token) return;

        try {
            Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error('Telegram send failed', ['error' => $e->getMessage()]);
        }
    }

    public function handleUpdate(array $update): void
    {
        $message = $update['message'] ?? null;
        if (!$message || !isset($message['text'])) return;

        $chatId = $message['chat']['id'];
        $text = trim($message['text']);
        $textId = (string) $chatId;

        // Check for commands
        $cmd = $this->parseCommand($text);
        if ($cmd) {
            if ($cmd['action'] === 'help') {
                $this->sendMessage($chatId, "Bot Pencatat Pengeluaran\n\nKirim: `50000 makan siang`\nHapus terakhir: `hapus`\nEdit: `edit terakhir jadi 70000`");
            } elseif ($cmd['action'] === 'delete_last') {
                $this->handleDeleteLast($textId, $chatId);
            } elseif ($cmd['action'] === 'edit_last') {
                $this->handleEditLast($textId, $chatId, $cmd['amount']);
            }
            return;
        }

        $parser = app(MessageParser::class);
        $pending = PendingInput::where('wa_id', $textId)->latest()->first();

        if ($pending) {
            $this->handlePending($pending, $textId, $text, $chatId, $parser);
            return;
        }

        $parsed = $parser->parse($text);

        if (!$parsed['amount']) {
            $categoryName = $parsed['category'];
            $category = $categoryName ? Category::where('name', $categoryName)->first() : null;

            if ($category) {
                PendingInput::create([
                    'wa_id' => $textId,
                    'category_name' => $category->name,
                    'step' => 'awaiting_amount',
                ]);
                $this->sendMessage($chatId, "Berapa jumlah untuk {$category->emoji} {$category->name}?");
            } else {
                $this->sendMessage($chatId, "Gak nemu kategori \"{$text}\". Ketik jumlah aja dulu, nanti gw tanya kategorinya.\n\nContoh: `50000 makan siang`");
            }
            return;
        }

        if (!$parsed['category']) {
            PendingInput::create([
                'wa_id' => $textId,
                'amount' => $parsed['amount'],
                'step' => 'awaiting_category',
            ]);
            $categoryList = Category::all()->map(fn($c) => "{$c->emoji} {$c->name}")->join(', ');
            $this->sendMessage($chatId, "Rp " . number_format($parsed['amount'], 0, ',', '.') . " — mau dikategorikan apa?\n\n{$categoryList}");
            return;
        }

        $this->saveAndConfirm($textId, $parsed['amount'], $parsed['category'], $parsed['description'], $chatId);
    }

    private function handlePending(PendingInput $pending, string $textId, string $text, string $chatId, MessageParser $parser): void
    {
        if ($pending->step === 'awaiting_category') {
            $categoryName = $parser->matchCategory($text);
            if (!$categoryName) {
                $categoryList = Category::all()->map(fn($c) => "{$c->emoji} {$c->name}")->join(', ');
                $this->sendMessage($chatId, "Kategori \"{$text}\" gak ditemukan. Pilih salah satu:\n{$categoryList}");
                return;
            }

            $description = null;
            $remaining = trim(str_ireplace($categoryName, '', $text));
            if ($remaining) {
                $reParsed = $parser->parse($text);
                if ($reParsed['amount'] && $reParsed['amount'] !== $pending->amount) {
                    $pending->delete();
                    $this->handleUpdate(['message' => ['chat' => ['id' => $chatId], 'text' => $text]]);
                    return;
                }
                $description = $remaining === $categoryName ? null : $remaining;
            }

            $pending->delete();
            $this->saveAndConfirm($textId, $pending->amount, $categoryName, $description, $chatId);

        } elseif ($pending->step === 'awaiting_amount') {
            $parsed = $parser->parse($text);
            $amount = $parsed['amount'];
            if (!$amount) {
                $this->sendMessage($chatId, "Masukin jumlahnya dulu ya, contoh: `25000`");
                return;
            }
            $description = $parsed['description'];
            $pending->delete();
            $this->saveAndConfirm($textId, $amount, $pending->category_name, $description, $chatId);
        }
    }

    private function saveAndConfirm(string $textId, int $amount, string $categoryName, ?string $description, string $chatId): void
    {
        $category = Category::where('name', $categoryName)->first();
        if (!$category) {
            $this->sendMessage($chatId, "Kategori \"{$categoryName}\" gak ditemukan.");
            return;
        }

        Expense::create([
            'category_id' => $category->id,
            'amount' => $amount,
            'description' => $description,
            'date' => now()->toDateString(),
            'source' => 'telegram',
            'wa_id' => $textId,
        ]);

        $msg = "✅ Berhasil dicatat!\n{$category->emoji} {$category->name}: Rp " . number_format($amount, 0, ',', '.');
        if ($description) $msg .= "\n📝 {$description}";
        $this->sendMessage($chatId, $msg);
    }

    private function parseCommand(string $text): ?array
    {
        $lower = trim(mb_strtolower($text));

        if (in_array($lower, ['/start', '/help'])) {
            return [
                'action' => 'help',
            ];
        }

        if (preg_match('/^hapus(?:\s+terakhir)?$/', $lower)) {
            return ['action' => 'delete_last'];
        }

        if (preg_match('/^edit\s+terakhir\s+jadi\s+(\d[\d.,]*)/', $lower, $m)) {
            return ['action' => 'edit_last', 'amount' => (int) str_replace(['.', ','], '', $m[1])];
        }

        return null;
    }

    private function handleDeleteLast(string $textId, string $chatId): void
    {
        $expense = Expense::where('wa_id', $textId)->latest()->first();
        if (!$expense) {
            $this->sendMessage($chatId, 'Tidak ada pengeluaran yang bisa dihapus.');
            return;
        }
        $cat = $expense->category;
        $detail = "{$cat->emoji} {$cat->name}: Rp " . number_format($expense->amount, 0, ',', '.');
        $expense->delete();
        $this->sendMessage($chatId, "✅ Berhasil dihapus!\n{$detail}");
    }

    private function handleEditLast(string $textId, string $chatId, int $amount): void
    {
        $expense = Expense::where('wa_id', $textId)->latest()->first();
        if (!$expense) {
            $this->sendMessage($chatId, 'Tidak ada pengeluaran yang bisa diedit.');
            return;
        }
        $expense->update(['amount' => $amount]);
        $cat = $expense->category;
        $this->sendMessage($chatId, "✅ Diubah jadi Rp " . number_format($amount, 0, ',', '.') . "\n{$cat->emoji} {$cat->name}" . ($expense->description ? " — {$expense->description}" : ''));
    }
}
