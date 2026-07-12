<?php

namespace App\Services\Vendor;

use App\Models\ChatAdmin;
use App\Models\ChatStore;
use App\Models\ChatThread;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class VendorChatService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function inbox(string $userId, string $type = 'customer', int $perPage = 20): LengthAwarePaginator
    {
        $model = $type === 'admin' ? ChatAdmin::class : ChatStore::class;

        return $model::query()
            ->where('restaurantId', $userId)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function messages(string $userId, string $orderId, string $type = 'customer', int $perPage = 50): LengthAwarePaginator
    {
        $chatType = $type === 'admin' ? 'chat_admin' : 'chat_store';
        $inbox = $this->findInbox($userId, $orderId, $type);

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

    public function send(string $userId, array $data): array
    {
        $type = $data['chatType'] ?? $data['type'] ?? 'customer';
        $orderId = $data['orderId'];
        $receiverId = $data['receiverId'] ?? $data['customerId'] ?? null;
        $message = $data['message'] ?? '';
        $messageType = $data['messageType'] ?? 'text';

        $inbox = $this->findInbox($userId, $orderId, $type === 'admin' ? 'admin' : 'customer');

        if (! $inbox) {
            $inboxData = [
                'id' => $orderId,
                'orderId' => $orderId,
                'customerId' => $receiverId,
                'restaurantId' => $userId,
                'lastMessage' => $message,
                'lastSenderId' => $userId,
                'chatType' => $type === 'admin' ? 'admin' : 'store',
                'createdAt' => now(),
                'customerName' => $data['customerName'] ?? null,
                'customerProfileImage' => $data['customerProfileImage'] ?? null,
                'restaurantName' => $data['restaurantName'] ?? null,
                'restaurantProfileImage' => $data['restaurantProfileImage'] ?? null,
            ];
            $prototype = $type === 'admin' ? new ChatAdmin() : new ChatStore();
            $inbox = CatalogEntityWriter::write($prototype, $inboxData);
        } else {
            $inbox->update(['lastMessage' => $message, 'lastSenderId' => $userId, 'createdAt' => now()]);
        }

        $thread = CatalogEntityWriter::write(new ChatThread(), [
            'id' => (string) Str::uuid(),
            'chat_id' => $inbox->id,
            'chat_type' => $type === 'admin' ? 'chat_admin' : 'chat_store',
            'message' => $message,
            'messageType' => $messageType,
            'senderId' => $userId,
            'receiverId' => $receiverId,
            'orderId' => $orderId,
            'createdAt' => now(),
            'url' => $data['url'] ?? null,
            'videoThumbnail' => $data['videoThumbnail'] ?? null,
        ]);

        return $thread->toDocumentArray();
    }

    public function uploadMedia(string $userId, $file, string $mediaType = 'image'): array
    {
        $directory = $mediaType === 'video' ? 'chat/videos' : 'chat/images';
        $result = $this->storageService->upload($file, $directory, 'public');

        return ['url' => url($result['url']), 'mime' => $result['mime_type'], 'path' => $result['path']];
    }

    protected function findInbox(string $userId, string $orderId, string $type)
    {
        $model = $type === 'admin' ? ChatAdmin::class : ChatStore::class;

        return $model::query()
            ->where('restaurantId', $userId)
            ->where(fn ($q) => $q->where('orderId', $orderId)->orWhere('id', $orderId))
            ->first();
    }
}
