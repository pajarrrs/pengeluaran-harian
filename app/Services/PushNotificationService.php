<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    public function sendToAll(string $title, string $body): void
    {
        $publicKey = env('VAPID_PUBLIC_KEY');
        $privateKey = env('VAPID_PRIVATE_KEY');
        if (!$publicKey || !$privateKey) return;

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => env('APP_URL', 'https://pengeluaran-harian.up.railway.app'),
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        $subs = PushSubscription::all();
        if ($subs->isEmpty()) return;

        foreach ($subs as $sub) {
            try {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'publicKey' => $sub->p256dh,
                        'authToken' => $sub->auth,
                    ]),
                    json_encode(['title' => $title, 'body' => $body]),
                );
            } catch (\Exception $e) {
                Log::error('Push queue failed', ['error' => $e->getMessage()]);
            }
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) continue;
            $endpoint = $report->getEndpoint();
            PushSubscription::where('endpoint', $endpoint)->delete();
            Log::error('Push failed', ['endpoint' => $endpoint, 'reason' => $report->getReason()]);
        }
    }
}
