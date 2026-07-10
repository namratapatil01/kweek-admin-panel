<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Customer\CustomerChatService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerChatController extends Controller
{
    public function __construct(protected CustomerChatService $chatService)
    {
    }

    public function inbox(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'type' => ['nullable', 'in:store,driver,provider,worker'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->chatService->inbox($user, $type, (int) $request->input('per_page', 20)),
            'Inbox retrieved'
        );
    }

    public function messages(Request $request, string $type, string $orderId): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->chatService->messages($user, $type, $orderId, (int) $request->input('per_page', 50)),
            'Messages retrieved'
        );
    }

    public function send(Request $request, string $type): JsonResponse
    {
        $data = $request->validate([
            'orderId' => ['required', 'string', 'max:64'],
            'message' => ['nullable', 'string'],
            'messageType' => ['nullable', 'string', 'max:32'],
            'receiverId' => ['nullable', 'string', 'max:64'],
            'restaurantId' => ['nullable', 'string', 'max:64'],
            'restaurantName' => ['nullable', 'string', 'max:255'],
            'restaurantProfileImage' => ['nullable', 'string'],
            'url' => ['nullable', 'string'],
            'videoThumbnail' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->chatService->send($user, $type, $data), 'Message sent');
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
