<?php

namespace App\Services\Notifications;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    public function __construct(protected SettingsService $settingsService)
    {
    }

    public function send(?string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (! $fcmToken) {
            return false;
        }

        $serverKey = (string) config('services.fcm.server_key', env('FCM_SERVER_KEY', ''));

        if ($serverKey === '') {
            Log::warning('FCM not configured: missing FCM_SERVER_KEY');

            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => array_merge([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'status' => 'done',
            ], $data),
        ]);

        return $response->successful();
    }

    public function sendToUser(?string $fcmToken, string $type, array $payload = []): bool
    {
        $content = $this->settingsService->get('dynamic_notification_' . $type, null);

        if (! is_array($content)) {
            $content = [
                'subject' => ucfirst(str_replace('_', ' ', $type)),
                'message' => $type,
            ];
        }

        return $this->send(
            $fcmToken,
            (string) ($content['subject'] ?? $type),
            (string) ($content['message'] ?? $type),
            $payload
        );
    }
}
