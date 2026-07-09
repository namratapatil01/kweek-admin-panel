<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorStoryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorStoryController extends Controller
{
    public function __construct(protected VendorStoryService $storyService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success($this->storyService->show($request->user()));
    }

    public function store(Request $request): JsonResponse
    {
        return ApiResponse::success($this->storyService->save($request->user(), $request->all()), 'Story saved');
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->storyService->delete($request->user());

        return ApiResponse::success(null, 'Story deleted');
    }

    public function uploadMedia(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'mediaType' => ['nullable', 'string', 'in:image,video'],
        ]);

        return ApiResponse::success(
            $this->storyService->uploadMedia($request->user(), $request->file('file'), $request->input('mediaType', 'image')),
            'Media uploaded'
        );
    }
}
