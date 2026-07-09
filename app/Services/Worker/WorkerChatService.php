<?php

namespace App\Services\Worker;

use App\Models\ChatThread;
use App\Models\ChatWorker;
use App\Models\AppUser;
use App\Support\CatalogEntityWriter;
use App\Services\Storage\FileStorageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class WorkerChatService
{
    public function __construct(
        protected WorkerProfileService $profileService,
        protected FileStorageService $storageService
    ) {
    }

    public function inbox(AppUser $user, int $perPage = 20): LengthAwarePaginator
    {
        $worker = $this->profileService->getWorkerOrFail($user);

        return ChatWorker::query()
            ->where('restaurantId', $worker->id)
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function messages(AppUser $user, string $orderId, int $perPage = 50): LengthAwarePaginator
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $inbox = ChatWorker::query()
            ->where('restaurantId', $worker->id)
            ->where(function ($q) use ($orderId) {
                $q->where('orderId', $orderId)->orWhere('id', $orderId);
            })
            ->first();

        return ChatThread::query()
            ->where('orderId', $orderId)
            ->where(function ($q) use ($inbox) {
                $q->where('chat_type', 'chat_worker');
                if ($inbox) {
                    $q->orWhere('chat_id', $inbox->id);
                }
            })
            ->orderBy('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function send(AppUser $user, array $data): array
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $workerDoc = $worker->toDocumentArray();
        $orderId = $data['orderId'];
        $receiverId = $data['receiverId'] ?? $data['customerId'] ?? null;
        $message = $data['message'] ?? '';
        $messageType = $data['messageType'] ?? 'text';

        $inbox = ChatWorker::query()
            ->where('restaurantId', $worker->id)
            ->where(function ($q) use ($orderId) {
                $q->where('orderId', $orderId)->orWhere('id', $orderId);
            })
            ->first();

        if (! $inbox) {
            $inbox = CatalogEntityWriter::write(new ChatWorker(), [
                'id' => $orderId,
                'orderId' => $orderId,
                'customerId' => $receiverId,
                'restaurantId' => $worker->id,
                'restaurantName' => trim(($workerDoc['firstName'] ?? '') . ' ' . ($workerDoc['lastName'] ?? '')),
                'restaurantProfileImage' => $workerDoc['profilePictureURL'] ?? null,
                'lastMessage' => $message,
                'lastSenderId' => $worker->id,
                'chatType' => 'Worker',
                'createdAt' => now(),
                'customerName' => $data['customerName'] ?? null,
                'customerProfileImage' => $data['customerProfileImage'] ?? null,
            ]);
        } else {
            $inbox->update([
                'lastMessage' => $message,
                'lastSenderId' => $worker->id,
                'createdAt' => now(),
            ]);
        }

        $thread = CatalogEntityWriter::write(new ChatThread(), [
            'id' => (string) Str::uuid(),
            'chat_id' => $inbox->id,
            'chat_type' => 'chat_worker',
            'message' => $message,
            'messageType' => $messageType,
            'senderId' => $worker->id,
            'receiverId' => $receiverId,
            'orderId' => $orderId,
            'createdAt' => now(),
            'url' => $data['url'] ?? null,
            'videoThumbnail' => $data['videoThumbnail'] ?? null,
        ]);

        return $thread->toDocumentArray();
    }

    public function uploadMedia(AppUser $user, $file, string $mediaType = 'image'): array
    {
        $this->profileService->getWorkerOrFail($user);
        $directory = $mediaType === 'video' ? 'chat/videos' : 'chat/images';
        $result = $this->storageService->upload($file, $directory, 'public');

        return [
            'url' => url($result['url']),
            'mime' => $result['mime_type'],
            'path' => $result['path'],
        ];
    }
}
