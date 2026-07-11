<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\PendingInput;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected MessageParser $parser;
    protected WhatsAppService $wa;

    public function __construct(MessageParser $parser, WhatsAppService $wa)
    {
        $this->parser = $parser;
        $this->wa = $wa;
    }

    // Meta webhook verification (GET)
    public function verify(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    // Receive messages (POST)
    public function receive(Request $request)
    {
        Log::info('WhatsApp webhook received', $request->all());

        $entry = $request->input('entry.0');
        if (!$entry) return response('OK', 200);

        $changes = $entry['changes'][0]['value'] ?? [];

        // Handle message status updates (ignore)
        if (!isset($changes['messages'])) return response('OK', 200);

        $message = $changes['messages'][0] ?? [];
        $waId = $message['from'] ?? '';
        $text = $message['text']['body'] ?? '';
        $phoneNumberId = $changes['metadata']['phone_number_id'] ?? '';

        if (empty($text) || empty($waId)) return response('OK', 200);

        $this->handleMessage($waId, $text, $phoneNumberId);

        return response('OK', 200);
    }

    protected function handleMessage(string $waId, string $text, string $phoneNumberId): void
    {
        // Check for commands first
        $cmd = $this->wa->handleCommand($waId, $text);
        if ($cmd) {
            $this->wa->sendMessage($phoneNumberId, $waId, $cmd['message']);
            return;
        }

        $pending = PendingInput::where('wa_id', $waId)->latest()->first();

        // Check if user has a pending input
        if ($pending) {
            $this->handlePending($pending, $waId, $text, $phoneNumberId);
            return;
        }

        $parsed = $this->parser->parse($text);

        // No amount at all — maybe they sent a category name first
        if (!$parsed['amount']) {
            $categoryName = $parsed['category'];
            $category = $categoryName ? Category::where('name', $categoryName)->first() : null;

            if ($category) {
                PendingInput::create([
                    'wa_id' => $waId,
                    'category_name' => $category->name,
                    'step' => 'awaiting_amount',
                ]);
                $this->wa->sendMessage($phoneNumberId, $waId, "Berapa jumlah untuk {$category->emoji} {$category->name}?");
            } else {
                $this->wa->sendMessage($phoneNumberId, $waId, "Gak nemu kategori \"{$text}\". Ketik jumlah aja dulu, nanti gw tanya kategorinya.\n\nContoh: *50000 makan siang*");
            }
            return;
        }

        // Have amount, check for category
        if (!$parsed['category']) {
            // No category matched — save pending, ask for category
            PendingInput::create([
                'wa_id' => $waId,
                'amount' => $parsed['amount'],
                'step' => 'awaiting_category',
            ]);

            $categoryList = Category::all()->map(fn($c) => "{$c->emoji} {$c->name}")->join(', ');
            $this->wa->sendMessage($phoneNumberId, $waId, "Rp " . number_format($parsed['amount'], 0, ',', '.') . " — mau dikategorikan apa?\n\n{$categoryList}");
            return;
        }

        // Have both amount and category
        $this->saveAndConfirm($waId, $parsed['amount'], $parsed['category'], $parsed['description'], $phoneNumberId);
    }

    protected function handlePending(PendingInput $pending, string $waId, string $text, string $phoneNumberId): void
    {
        if ($pending->step === 'awaiting_category') {
            // User replied with category name
            $categoryName = $this->parser->matchCategory($text);

            if (!$categoryName) {
                $categoryList = Category::all()->map(fn($c) => "{$c->emoji} {$c->name}")->join(', ');
                $this->wa->sendMessage($phoneNumberId, $waId, "Kategori \"{$text}\" gak ditemukan. Pilih salah satu:\n{$categoryList}");
                return;
            }

            $description = null;
            // Check if there's extra text after category
            $remaining = trim(str_ireplace($categoryName, '', $text));
            if ($remaining) {
                // Try parsing again in case there's a new amount
                $reParsed = $this->parser->parse($text);
                if ($reParsed['amount'] && $reParsed['amount'] !== $pending->amount) {
                    // User sent a new amount + category — use the new one
                    $pending->delete();
                    $this->handleMessage($waId, $text, $phoneNumberId);
                    return;
                }
                $description = $remaining === $categoryName ? null : $remaining;
            }

            $pending->delete();
            $this->saveAndConfirm($waId, $pending->amount, $categoryName, $description, $phoneNumberId);

        } elseif ($pending->step === 'awaiting_amount') {
            // User replied with amount
            $parsed = $this->parser->parse($text);
            $amount = $parsed['amount'];

            if (!$amount) {
                $this->wa->sendMessage($phoneNumberId, $waId, "Masukin jumlahnya dulu ya, contoh: *25000*");
                return;
            }

            $description = $parsed['description'];
            $pending->delete();
            $this->saveAndConfirm($waId, $amount, $pending->category_name, $description, $phoneNumberId);
        }
    }

    protected function saveAndConfirm(string $waId, int $amount, string $categoryName, ?string $description, string $phoneNumberId): void
    {
        $category = Category::where('name', $categoryName)->first();

        if (!$category) {
            $this->wa->sendMessage($phoneNumberId, $waId, "Kategori \"{$categoryName}\" gak ditemukan.");
            return;
        }

        Expense::create([
            'category_id' => $category->id,
            'amount' => $amount,
            'description' => $description,
            'date' => now()->toDateString(),
            'source' => 'whatsapp',
            'wa_id' => $waId,
        ]);

        $msg = "✅ Berhasil dicatat!\n{$category->emoji} {$category->name}: Rp " . number_format($amount, 0, ',', '.');
        if ($description) {
            $msg .= "\n📝 {$description}";
        }

        $this->wa->sendMessage($phoneNumberId, $waId, $msg);
    }
}
