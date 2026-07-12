<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Provider\ProviderChatSendRequest;
use App\Models\AppUser;
use App\Services\Provider\ProviderChatService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderChatController extends Controller
{
    public function __construct(protected ProviderChatService $chatService)
    {
    }

    public function inbox(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->chatService->inbox(
                $user->id,
                $request->input('type', 'customer'),
                (int) $request->input('per_page', 20)
            ),
            'Inbox retrieved'
        );
    }

    public function messages(Request $request, string $orderId): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->chatService->messages(
                $user->id,
                $orderId,
                $request->input('type', 'customer'),
                (int) $request->input('per_page', 50),
                $request->input('since')
            ),
            'Messages retrieved'
        );
    }

    public function send(ProviderChatSendRequest $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->chatService->send($user->id, $request->validated()),
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
                $user->id,
                $request->file('file'),
                $request->input('mediaType', 'image')
            ),
            'Media uploaded',
            201
        );
    }
}
