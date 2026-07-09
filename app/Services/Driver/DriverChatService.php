<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\ChatDriver;
use App\Models\ChatThread;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class DriverChatService
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function inbox(AppUser $driver, int $perPage = 20): LengthAwarePaginator
    {
        return ChatDriver::query()
            ->where('restaurantId', $driver->id)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function messages(AppUser $driver, string $orderId, int $perPage = 50): LengthAwarePaginator
    {
        $inbox = ChatDriver::query()
            ->where('restaurantId', $driver->id)
            ->where(function ($q) use ($orderId) {
                $q->where('orderId', $orderId)->orWhere('id', $orderId);
            })
            ->first();

        return ChatThread::query()
            ->where('orderId', $orderId)
            ->where(function ($q) use ($inbox) {
                $q->where('chat_type', 'chat_driver');
                if ($inbox) {
                    $q->orWhere('chat_id', $inbox->id);
                }
            })
            ->orderBy('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function send(AppUser $driver, array $data): array
    {
        $driverDoc = $driver->toDocumentArray();
        $orderId = $data['orderId'];
        $receiverId = $data['receiverId'] ?? $data['customerId'] ?? null;
        $message = $data['message'] ?? '';
        $messageType = $data['messageType'] ?? 'text';

        $inbox = ChatDriver::query()
            ->where('restaurantId', $driver->id)
            ->where(function ($q) use ($orderId) {
                $q->where('orderId', $orderId)->orWhere('id', $orderId);
            })
            ->first();

        if (! $inbox) {
            $inbox = CatalogEntityWriter::write(new ChatDriver(), [
                'id' => $orderId,
                'orderId' => $orderId,
                'customerId' => $receiverId,
                'restaurantId' => $driver->id,
                'restaurantName' => trim(($driverDoc['firstName'] ?? '') . ' ' . ($driverDoc['lastName'] ?? '')),
                'restaurantProfileImage' => $driverDoc['profilePictureURL'] ?? null,
                'lastMessage' => $message,
                'lastSenderId' => $driver->id,
                'chatType' => 'Driver',
                'createdAt' => now(),
                'customerName' => $data['customerName'] ?? null,
                'customerProfileImage' => $data['customerProfileImage'] ?? null,
            ]);
        } else {
            $inbox->update([
                'lastMessage' => $message,
                'lastSenderId' => $driver->id,
                'createdAt' => now(),
            ]);
        }

        $thread = CatalogEntityWriter::write(new ChatThread(), [
            'id' => (string) Str::uuid(),
            'chat_id' => $inbox->id,
            'chat_type' => 'chat_driver',
            'message' => $message,
            'messageType' => $messageType,
            'senderId' => $driver->id,
            'receiverId' => $receiverId,
            'orderId' => $orderId,
            'createdAt' => now(),
            'url' => $data['url'] ?? null,
            'videoThumbnail' => $data['videoThumbnail'] ?? null,
        ]);

        return $thread->toDocumentArray();
    }

    public function uploadMedia(AppUser $driver, $file, string $mediaType = 'image'): array
    {
        $directory = $mediaType === 'video' ? 'chat/videos' : 'chat/images';
        $result = $this->storageService->upload($file, $directory, 'public');

        return [
            'url' => url($result['url']),
            'mime' => $result['mime_type'],
            'path' => $result['path'],
        ];
    }
}
