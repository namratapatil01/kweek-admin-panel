<?php

namespace App\Services\Provider;

use App\Models\AppUser;
use App\Models\ChatDriver;
use App\Models\ChatProvider;
use App\Models\ChatStore;
use App\Models\ChatThread;
use App\Models\ChatWorker;
use App\Support\CatalogEntityWriter;
use App\Services\Storage\FileStorageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProviderChatService
{
    public function __construct(
        protected FileStorageService $storageService,
        protected ProviderNotificationService $notificationService
    ) {
    }

    public function inbox(string $providerId, string $type = 'customer', int $perPage = 20): LengthAwarePaginator
    {
        $model = $this->inboxModelClass($type);

        return $model::query()
            ->where('restaurantId', $providerId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function messages(string $providerId, string $orderId, string $type = 'customer', int $perPage = 50, ?string $since = null): LengthAwarePaginator
    {
        $chatType = $this->chatTypeKey($type);
        $inbox = $this->findInbox($providerId, $orderId, $type);

        $query = ChatThread::query()
            ->where('orderId', $orderId)
            ->where(function ($q) use ($chatType, $inbox) {
                $q->where('chat_type', $chatType);
                if ($inbox) {
                    $q->orWhere('chat_id', $inbox->id);
                }
            });

        if ($since) {
            try {
                $query->where('createdAt', '>=', \Illuminate\Support\Carbon::parse($since));
            } catch (\Throwable) {
                // ignore invalid since
            }
        }

        return $query->orderBy('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function send(string $providerId, array $data): array
    {
        $type = $data['chatType'] ?? $data['type'] ?? 'customer';
        $orderId = $data['orderId'];
        $receiverId = $data['receiverId'] ?? $data['customerId'] ?? null;
        $message = $data['message'] ?? '';
        $messageType = $data['messageType'] ?? 'text';

        $inbox = $this->findInbox($providerId, $orderId, $type);

        if (! $inbox) {
            $inboxData = [
                'id' => $orderId,
                'orderId' => $orderId,
                'customerId' => $receiverId,
                'restaurantId' => $providerId,
                'lastMessage' => $message,
                'lastSenderId' => $providerId,
                'chatType' => $type,
                'createdAt' => now(),
                'customerName' => $data['customerName'] ?? null,
                'customerProfileImage' => $data['customerProfileImage'] ?? null,
                'restaurantName' => $data['restaurantName'] ?? null,
                'restaurantProfileImage' => $data['restaurantProfileImage'] ?? null,
            ];

            $prototype = $this->inboxPrototype($type);
            $inbox = CatalogEntityWriter::write($prototype, $inboxData);
        } else {
            $inbox->update([
                'lastMessage' => $message,
                'lastSenderId' => $providerId,
                'createdAt' => now(),
            ]);
        }

        $thread = CatalogEntityWriter::write(new ChatThread(), [
            'id' => (string) Str::uuid(),
            'chat_id' => $inbox->id,
            'chat_type' => $this->chatTypeKey($type),
            'message' => $message,
            'messageType' => $messageType,
            'senderId' => $providerId,
            'receiverId' => $receiverId,
            'orderId' => $orderId,
            'createdAt' => now(),
            'url' => $data['url'] ?? null,
            'videoThumbnail' => $data['videoThumbnail'] ?? null,
        ]);

        if ($receiverId) {
            $recipient = AppUser::query()->find($receiverId);
            if ($recipient?->fcmToken) {
                $this->notificationService->sendChatNotification(
                    $recipient->fcmToken,
                    $orderId,
                    $message
                );
            }
        }

        return $thread->toDocumentArray();
    }

    public function uploadMedia(string $providerId, $file, string $mediaType = 'image'): array
    {
        $directory = $mediaType === 'video' ? 'chat/videos' : 'chat/images';
        $result = $this->storageService->upload($file, $directory, 'public');

        return [
            'url' => url($result['url']),
            'mime' => $result['mime_type'],
            'path' => $result['path'],
        ];
    }

    protected function findInbox(string $providerId, string $orderId, string $type)
    {
        $model = $this->inboxModelClass($type);

        return $model::query()
            ->where('restaurantId', $providerId)
            ->where(function ($q) use ($orderId) {
                $q->where('orderId', $orderId)->orWhere('id', $orderId);
            })
            ->first();
    }

    protected function inboxModelClass(string $type): string
    {
        return match ($type) {
            'worker' => ChatWorker::class,
            'driver' => ChatDriver::class,
            'store' => ChatStore::class,
            default => ChatProvider::class,
        };
    }

    protected function inboxPrototype(string $type)
    {
        return match ($type) {
            'worker' => new ChatWorker(),
            'driver' => new ChatDriver(),
            'store' => new ChatStore(),
            default => new ChatProvider(),
        };
    }

    protected function chatTypeKey(string $type): string
    {
        return match ($type) {
            'worker' => 'chat_worker',
            'driver' => 'chat_driver',
            'store' => 'chat_store',
            default => 'chat_provider',
        };
    }
}
