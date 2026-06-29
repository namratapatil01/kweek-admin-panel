<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Storage\FileStorageService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
            'directory' => ['sometimes', 'string', 'max:120'],
            'visibility' => ['sometimes', 'in:public,private'],
        ]);

        $result = $this->storageService->upload(
            $request->file('file'),
            $request->input('directory', 'uploads'),
            $request->input('visibility', 'public')
        );

        return ApiResponse::success($result, 'Uploaded', 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $this->storageService->delete($request->input('path'));

        return ApiResponse::success(null, 'Deleted');
    }
}
