<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Worker\WorkerMiscService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerMiscController extends Controller
{
    public function __construct(protected WorkerMiscService $miscService)
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

    public function earnings(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->miscService->earningsSummary($user));
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
}
