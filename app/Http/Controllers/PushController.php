<?php

namespace App\Http\Controllers;

use App\Services\PushNotificationService;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            ['p256dh' => $data['keys']['p256dh'], 'auth' => $data['keys']['auth']],
        );

        return response()->json(['ok' => true]);
    }

    public function test(PushNotificationService $push)
    {
        $count = PushSubscription::count();
        if ($count === 0) {
            return back()->with('error', 'Belum ada perangkat yang subscribe push notification. Buka halaman ini di HP dan izinkan notifikasi.');
        }
        try {
            $push->sendToAll('🔔 Test Notifikasi', 'Push notification berhasil!');
            return back()->with('success', "Push notification terkirim ke {$count} perangkat!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Push gagal: ' . $e->getMessage());
        }
    }
}
