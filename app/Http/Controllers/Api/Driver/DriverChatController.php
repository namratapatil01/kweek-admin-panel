<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Driver\DriverChatService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverChatController extends Controller
{
    public function __construct(protected DriverChatService $chatService)
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
            'message' => ['nullable', 'string'],
            'messageType' => ['nullable', 'string', 'max:32'],
            'receiverId' => ['nullable', 'string', 'max:64'],
            'customerId' => ['nullable', 'string', 'max:64'],
            'customerName' => ['nullable', 'string', 'max:255'],
            'customerProfileImage' => ['nullable', 'string'],
            'url' => ['nullable', 'string'],
            'videoThumbnail' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->chatService->send($user, $data), 'Message sent');
    }

    public function upload(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'mediaType' => ['nullable', 'string', 'in:image,video'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->chatService->uploadMedia($user, $request->file('file'), $data['mediaType'] ?? 'image'),
            'Media uploaded'
        );
    }
}
