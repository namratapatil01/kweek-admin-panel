<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Driver\DriverMiscService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverMiscController extends Controller
{
    public function __construct(protected DriverMiscService $miscService)
    {
    }

    public function terms(): JsonResponse
    {
        return ApiResponse::success($this->miscService->terms());
    }

    public function privacy(): JsonResponse
    {
        return ApiResponse::success($this->miscService->privacy());
    }

    public function catalog(Request $request): JsonResponse
    {
        return ApiResponse::success($this->miscService->catalog($request->input('serviceType')));
    }

    public function notifications(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->miscService->notifications((int) $request->input('per_page', 20)),
            'Notifications retrieved'
        );
    }

    public function documents(Request $request): JsonResponse
    {
        return ApiResponse::success($this->miscService->documents($request->input('serviceType')));
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
            'Documents submitted'
        );
    }

    public function uploadDocument(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'side' => ['nullable', 'string', 'in:front,back'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->miscService->uploadDocumentFile($user, $request->file('file'), $data['side'] ?? 'front'),
            'Document uploaded'
        );
    }

    public function vendor(string $vendorId): JsonResponse
    {
        $vendor = $this->miscService->vendor($vendorId);

        if (! $vendor) {
            return ApiResponse::error('Vendor not found', 404);
        }

        return ApiResponse::success($vendor);
    }

    public function notificationContent(string $type): JsonResponse
    {
        return ApiResponse::success($this->miscService->notificationContent($type));
    }

    public function markNotificationRead(string $id): JsonResponse
    {
        $notification = $this->miscService->markNotificationRead($id);

        if (! $notification) {
            return ApiResponse::error('Notification not found', 404);
        }

        return ApiResponse::success($notification, 'Notification marked as read');
    }
}
