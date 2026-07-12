<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorAdvertisementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorAdvertisementController extends Controller
{
    public function __construct(protected VendorAdvertisementService $advertisementService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->advertisementService->list($request->user(), (int) $request->input('per_page', 20)),
            'Advertisements retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $ad = $this->advertisementService->show($request->user(), $id);
        if (! $ad) {
            return ApiResponse::error('Advertisement not found', 404);
        }

        return ApiResponse::success($ad);
    }

    public function store(Request $request): JsonResponse
    {
        return ApiResponse::success($this->advertisementService->create($request->user(), $request->all()), 'Advertisement created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $ad = $this->advertisementService->update($request->user(), $id, $request->all());
        if (! $ad) {
            return ApiResponse::error('Advertisement not found', 404);
        }

        return ApiResponse::success($ad, 'Advertisement updated');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (! $this->advertisementService->delete($request->user(), $id)) {
            return ApiResponse::error('Advertisement not found', 404);
        }

        return ApiResponse::success(null, 'Advertisement deleted');
    }

    public function uploadMedia(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'type' => ['nullable', 'string', 'in:profile,cover,video'],
        ]);

        $ad = $this->advertisementService->uploadMedia($request->user(), $id, $request->file('file'), $request->input('type', 'profile'));
        if (! $ad) {
            return ApiResponse::error('Advertisement not found', 404);
        }

        return ApiResponse::success($ad, 'Media uploaded');
    }
}
