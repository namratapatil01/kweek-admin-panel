<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Worker\WorkerChatService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerChatController extends Controller
{
    public function __construct(protected WorkerChatService $chatService)
    {
    }

    public function inbox(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->chatService->inbox($user, (int) $request->input('per_page', 20)),
            'Inbox retrieved'
        );
    }

    public function messages(Request $request, string $orderId): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->chatService->messages($user, $orderId, (int) $request->input('per_page', 50)),
            'Messages retrieved'
        );
    }

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orderId' => ['required', 'string', 'max:64'],
            'receiverId' => ['nullable', 'string', 'max:64'],
            'customerId' => ['nullable', 'string', 'max:64'],
            'message' => ['required_without:url', 'string'],
            'messageType' => ['nullable', 'string', 'max:32'],
            'url' => ['nullable', 'array'],
            'videoThumbnail' => ['nullable', 'string'],
            'customerName' => ['nullable', 'string'],
            'customerProfileImage' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->chatService->send($user, $data),
            'Message sent',
            201
        );
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'mediaType' => ['nullable', 'in:image,video'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->chatService->uploadMedia(
                $user,
                $request->file('file'),
                $request->input('mediaType', 'image')
            ),
            'Media uploaded',
            201
        );
    }
}
