<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorChatService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorChatController extends Controller
{
    public function __construct(protected VendorChatService $chatService)
    {
    }

    public function inbox(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->chatService->inbox($request->user()->id, $request->input('type', 'customer'), (int) $request->input('per_page', 20)),
            'Inbox retrieved'
        );
    }

    public function messages(Request $request, string $orderId): JsonResponse
    {
        return ApiResponse::paginated(
            $this->chatService->messages($request->user()->id, $orderId, $request->input('type', 'customer'), (int) $request->input('per_page', 50)),
            'Messages retrieved'
        );
    }

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orderId' => ['required', 'string', 'max:64'],
            'message' => ['nullable', 'string'],
            'messageType' => ['nullable', 'string', 'max:32'],
            'chatType' => ['nullable', 'string', 'in:customer,admin,store'],
            'type' => ['nullable', 'string', 'in:customer,admin,store'],
            'receiverId' => ['nullable', 'string', 'max:64'],
            'customerId' => ['nullable', 'string', 'max:64'],
            'url' => ['nullable', 'string'],
        ]);

        if (empty($data['chatType']) && ! empty($data['type'])) {
            $data['chatType'] = $data['type'];
        }

        return ApiResponse::success($this->chatService->send($request->user()->id, $data), 'Message sent');
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'mediaType' => ['nullable', 'string', 'in:image,video'],
        ]);

        return ApiResponse::success(
            $this->chatService->uploadMedia($request->user()->id, $request->file('file'), $request->input('mediaType', 'image')),
            'Media uploaded'
        );
    }
}
