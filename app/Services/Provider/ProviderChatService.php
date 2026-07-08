<?php

namespace App\Services\Provider;

use App\Models\ChatProvider;
use App\Models\ChatThread;
use App\Models\ChatWorker;
use App\Support\CatalogEntityWriter;
use App\Services\Storage\FileStorageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProviderChatService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function inbox(string $providerId, string $type = 'customer', int $perPage = 20): LengthAwarePaginator
    {
        $model = $type === 'worker' ? ChatWorker::class : ChatProvider::class;

        return $model::query()
            ->where('restaurantId', $providerId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function messages(string $providerId, string $orderId, string $type = 'customer', int $perPage = 50): LengthAwarePaginator
    {
        $chatType = $type === 'worker' ? 'chat_worker' : 'chat_provider';

        $inbox = $this->findInbox($providerId, $orderId, $type);

        return ChatThread::query()
            ->where('orderId', $orderId)
            ->where(function ($q) use ($chatType, $inbox) {
                $q->where('chat_type', $chatType);
                if ($inbox) {
                    $q->orWhere('chat_id', $inbox->id);
                }
            })
            ->orderBy('createdAt')
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

        $inbox = $this->findInbox($providerId, $orderId, $type === 'worker' ? 'worker' : 'customer');

        if (! $inbox) {
            $inboxData = [
                'id' => $orderId,
                'orderId' => $orderId,
                'customerId' => $receiverId,
                'restaurantId' => $providerId,
                'lastMessage' => $message,
                'lastSenderId' => $providerId,
                'chatType' => $type === 'worker' ? 'worker' : 'provider',
                'createdAt' => now(),
                'customerName' => $data['customerName'] ?? null,
                'customerProfileImage' => $data['customerProfileImage'] ?? null,
                'restaurantName' => $data['restaurantName'] ?? null,
                'restaurantProfileImage' => $data['restaurantProfileImage'] ?? null,
            ];

            $prototype = $type === 'worker' ? new ChatWorker() : new ChatProvider();
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
            'chat_type' => $type === 'worker' ? 'chat_worker' : 'chat_provider',
            'message' => $message,
            'messageType' => $messageType,
            'senderId' => $providerId,
            'receiverId' => $receiverId,
            'orderId' => $orderId,
            'createdAt' => now(),
            'url' => $data['url'] ?? null,
            'videoThumbnail' => $data['videoThumbnail'] ?? null,
        ]);

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
        $model = $type === 'worker' ? ChatWorker::class : ChatProvider::class;

        return $model::query()
            ->where('restaurantId', $providerId)
            ->where(function ($q) use ($orderId) {
                $q->where('orderId', $orderId)->orWhere('id', $orderId);
            })
            ->first();
    }
}
