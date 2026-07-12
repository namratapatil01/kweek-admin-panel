<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorMiscService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorMiscController extends Controller
{
    public function __construct(protected VendorMiscService $miscService)
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
        return ApiResponse::success($this->miscService->catalog($request->input('sectionId')));
    }

    public function notifications(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->miscService->notifications((int) $request->input('per_page', 20)),
            'Notifications retrieved'
        );
    }

    public function documents(): JsonResponse
    {
        return ApiResponse::success($this->miscService->documents());
    }

    public function documentStatus(Request $request): JsonResponse
    {
        return ApiResponse::success($this->miscService->getDocumentVerification($request->user()->id));
    }

    public function submitDocuments(Request $request): JsonResponse
    {
        $data = $request->validate(['documents' => ['required', 'array']]);

        return ApiResponse::success($this->miscService->submitDocuments($request->user(), $data['documents']), 'Documents submitted');
    }

    public function uploadDocument(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'side' => ['nullable', 'string', 'in:front,back'],
        ]);

        return ApiResponse::success(
            $this->miscService->uploadDocumentFile($request->user(), $request->file('file'), $request->input('side', 'front')),
            'Document uploaded'
        );
    }
}
