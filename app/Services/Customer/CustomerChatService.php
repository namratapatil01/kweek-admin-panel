<?php

namespace App\Services\Customer;

use App\Models\AppUser;
use App\Models\ChatDriver;
use App\Models\ChatProvider;
use App\Models\ChatStore;
use App\Models\ChatThread;
use App\Models\ChatWorker;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CustomerChatService
{
    protected array $inboxModels = [
        'store' => ChatStore::class,
        'driver' => ChatDriver::class,
        'provider' => ChatProvider::class,
        'worker' => ChatWorker::class,
    ];

    protected array $chatTypes = [
        'store' => 'chat_store',
        'driver' => 'chat_driver',
        'provider' => 'chat_provider',
        'worker' => 'chat_worker',
    ];

    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function inbox(AppUser $customer, string $type, int $perPage = 20): LengthAwarePaginator
    {
        $modelClass = $this->resolveInboxModel($type);

        return $modelClass::query()
            ->where('customerId', $customer->id)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function messages(AppUser $customer, string $type, string $orderId, int $perPage = 50): LengthAwarePaginator
    {
        $modelClass = $this->resolveInboxModel($type);
        $chatType = $this->chatTypes[$type];

        $inbox = $modelClass::query()
            ->where('customerId', $customer->id)
            ->where(function ($q) use ($orderId) {
                $q->where('orderId', $orderId)->orWhere('id', $orderId);
            })
            ->first();

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

    public function send(AppUser $customer, string $type, array $data): array
    {
        $modelClass = $this->resolveInboxModel($type);
        $chatType = $this->chatTypes[$type];
        $customerDoc = $customer->toDocumentArray();

        $orderId = $data['orderId'];
        $receiverId = $data['receiverId'] ?? $data['restaurantId'] ?? null;
        $message = $data['message'] ?? '';
        $messageType = $data['messageType'] ?? 'text';

        $inbox = $modelClass::query()
            ->where('customerId', $customer->id)
            ->where(function ($q) use ($orderId) {
                $q->where('orderId', $orderId)->orWhere('id', $orderId);
            })
            ->first();

        if (! $inbox) {
            $inbox = CatalogEntityWriter::write(new $modelClass(), [
                'id' => $orderId,
                'orderId' => $orderId,
                'customerId' => $customer->id,
                'restaurantId' => $receiverId,
                'customerName' => trim(($customerDoc['firstName'] ?? '') . ' ' . ($customerDoc['lastName'] ?? '')),
                'customerProfileImage' => $customerDoc['profilePictureURL'] ?? null,
                'restaurantName' => $data['restaurantName'] ?? null,
                'restaurantProfileImage' => $data['restaurantProfileImage'] ?? null,
                'lastMessage' => $message,
                'lastSenderId' => $customer->id,
                'chatType' => ucfirst($type),
                'createdAt' => now(),
            ]);
        } else {
            $inbox->update([
                'lastMessage' => $message,
                'lastSenderId' => $customer->id,
                'createdAt' => now(),
            ]);
        }

        $thread = CatalogEntityWriter::write(new ChatThread(), [
            'id' => (string) Str::uuid(),
            'chat_id' => $inbox->id,
            'chat_type' => $chatType,
            'message' => $message,
            'messageType' => $messageType,
            'senderId' => $customer->id,
            'receiverId' => $receiverId,
            'orderId' => $orderId,
            'createdAt' => now(),
            'url' => $data['url'] ?? null,
            'videoThumbnail' => $data['videoThumbnail'] ?? null,
        ]);

        return $thread->toDocumentArray();
    }

    public function uploadMedia(AppUser $customer, $file, string $mediaType = 'image'): array
    {
        $directory = $mediaType === 'video' ? 'chat/videos' : 'chat/images';
        $result = $this->storageService->upload($file, $directory, 'public');

        return [
            'url' => url($result['url']),
            'mime' => $result['mime_type'],
            'path' => $result['path'],
        ];
    }

    protected function resolveInboxModel(string $type): string
    {
        $modelClass = $this->inboxModels[$type] ?? null;

        if (! $modelClass) {
            throw new InvalidArgumentException("Unsupported chat type: {$type}");
        }

        return $modelClass;
    }
}
