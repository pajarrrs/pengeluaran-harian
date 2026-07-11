<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function sendToAll(string $title, string $body): void
    {
        $publicKey = env('VAPID_PUBLIC_KEY');
        $privateKey = env('VAPID_PRIVATE_KEY');
        if (!$publicKey || !$privateKey) return;

        $subs = PushSubscription::all();
        foreach ($subs as $sub) {
            try {
                $this->send($sub, $title, $body, $publicKey, $privateKey);
            } catch (\Exception $e) {
                Log::error('Push send failed', ['error' => $e->getMessage()]);
            }
        }
    }

    private function send(PushSubscription $sub, string $title, string $body, string $publicKey, string $privateKey): void
    {
        $payload = json_encode(['title' => $title, 'body' => $body]);
        $info = json_decode(json_encode($sub), false);

        // Manual Web Push encryption
        $data = $this->encrypt($payload, $info->p256dh, $info->auth);
        if (!$data) return;

        $headers = [
            'Content-Type: application/octet-stream',
            'TTH: ' . $this->base64UrlEncode(hash('sha256', $data['ciphertext'], true)),
            'Content-Encoding: aes128gcm',
            'Encryption: salt=' . $this->base64UrlEncode($data['salt']),
            'Crypto-Key: dh=' . $this->base64UrlEncode($data['localPublicKey']),
        ];

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $data['ciphertext'],
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($sub->endpoint, false, $ctx);

        // Remove invalid subscriptions
        if ($http_response_header ?? false) {
            $status = explode(' ', $http_response_header[0])[1] ?? '';
            if ($status === '410' || $status === '404') {
                $sub->delete();
            }
        }
    }

    private function encrypt(string $payload, string $p256dh, string $auth): ?array
    {
        $localKey = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        if (!$localKey) return null;

        $localPub = openssl_pkey_get_details($localKey)['key'];
        $remotePub = $this->convertPublicKey($p256dh);

        $sharedSecret = null;
        openssl_pkey_derive($remotePub, $localKey, $sharedSecret);

        $salt = random_bytes(16);
        $info = $this->base64UrlDecode($auth);
        $prk = hash_hmac('sha256', $info, $sharedSecret, true);

        $cekInfo = "Content-Encoding: auth\0";
        $cek = hash_hmac('sha256', $cekInfo, $prk, true);

        $nonce = hash_hmac('sha256', 'Content-Encoding: nonce' . "\0" . $this->pad($salt, 1), $cek, true);

        $ciphertext = '';
        $key = hash_hmac('sha256', $nonce . "\x01", $cek, true);
        $iv = substr($nonce, 0, 12);

        // Simple XOR for payload (matches Web Push encryption for small payloads)
        $padLen = 0;
        $plaintext = $payload . "\x00\x00";
        $cipher = openssl_encrypt($plaintext, 'aes-128-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) return null;

        return [
            'salt' => $salt,
            'localPublicKey' => $this->getUncompressedECKey($localPub),
            'ciphertext' => $salt . $this->getUncompressedECKey($localPub) . pack('N', 0) . $cipher . $tag,
        ];
    }

    private function convertPublicKey(string $base64): string
    {
        $bin = $this->base64UrlDecode($base64);
        return "\x04" . $bin;
    }

    private function getUncompressedECKey(string $pem): string
    {
        $lines = explode("\n", $pem);
        array_shift($lines);
        array_pop($lines);
        $bin = base64_decode(implode('', $lines));
        if (ord($bin[0]) === 0x04) return substr($bin, 1);
        return $bin;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function pad(string $data, int $len): string
    {
        while (strlen($data) < $len) $data .= "\x00";
        return $data;
    }
}
