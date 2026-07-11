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
        $push->sendToAll('🔔 Test Notifikasi', 'Push notification berhasil!');
        return back()->with('success', 'Push notification terkirim!');
    }
}
