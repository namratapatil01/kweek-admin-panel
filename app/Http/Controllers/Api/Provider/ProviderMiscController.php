<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Provider\ProviderMiscService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderMiscController extends Controller
{
    public function __construct(protected ProviderMiscService $miscService)
    {
    }

    public function notifications(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->miscService->notifications((int) $request->input('per_page', 20)),
            'Notifications retrieved'
        );
    }

    public function terms(): JsonResponse
    {
        return ApiResponse::success($this->miscService->terms());
    }

    public function privacy(): JsonResponse
    {
        return ApiResponse::success($this->miscService->privacy());
    }

    public function documents(): JsonResponse
    {
        return ApiResponse::success($this->miscService->documents());
    }

    public function documentStatus(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->miscService->getDocumentVerification($user->id));
    }

    public function submitDocuments(Request $request): JsonResponse
    {
        $data = $request->validate([
            'documents' => ['required', 'array'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->miscService->submitDocuments($user, $data['documents']),
            'Documents submitted',
            201
        );
    }

    public function uploadDocument(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'side' => ['nullable', 'string', 'max:32'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->miscService->uploadDocumentFile(
                $user,
                $request->file('file'),
                $request->input('side', 'front')
            ),
            'Document uploaded',
            201
        );
    }

    public function notificationTemplate(string $type): JsonResponse
    {
        return ApiResponse::success(
            $this->miscService->notificationContent($type),
            'Notification template retrieved'
        );
    }

    public function emailTemplate(string $type): JsonResponse
    {
        $template = $this->miscService->emailTemplate($type);

        if (! $template) {
            return ApiResponse::error('Email template not found', 404);
        }

        return ApiResponse::success($template, 'Email template retrieved');
    }
}
