<?php

namespace App\Services\Provider;

use App\Models\DynamicNotification;
use App\Services\Notifications\FcmNotificationService;

class ProviderNotificationService
{
    public function __construct(protected FcmNotificationService $fcmService)
    {
    }

    public function sendBookingNotification(?string $fcmToken, string $type, string $orderId): bool
    {
        $content = $this->notificationContent($type);

        return $this->fcmService->send(
            $fcmToken,
            (string) ($content['subject'] ?? $type),
            (string) ($content['message'] ?? $type),
            [
                'type' => 'provider_order',
                'orderId' => $orderId,
                'notification_type' => $type,
            ]
        );
    }

    public function sendChatNotification(?string $fcmToken, string $orderId, string $message): bool
    {
        return $this->fcmService->send(
            $fcmToken,
            'New message',
            $message,
            [
                'type' => 'provider_order',
                'orderId' => $orderId,
            ]
        );
    }

    public function notificationContent(string $type): array
    {
        $notification = DynamicNotification::query()
            ->where('type', $type)
            ->first();

        if ($notification) {
            return $notification->toDocumentArray();
        }

        return [
            'id' => '',
            'type' => $type,
            'subject' => ucfirst(str_replace('_', ' ', $type)),
            'message' => $type,
        ];
    }
}
