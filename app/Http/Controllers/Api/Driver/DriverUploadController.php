<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\Storage\FileStorageService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverUploadController extends Controller
{
    public function __construct(protected FileStorageService $storageService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'directory' => ['sometimes', 'string', 'max:120'],
            'visibility' => ['sometimes', 'in:public,private'],
        ]);

        $result = $this->storageService->upload(
            $request->file('file'),
            $data['directory'] ?? 'uploads/driver',
            $data['visibility'] ?? 'public'
        );

        return ApiResponse::success([
            'url' => url($result['url']),
            'path' => $result['path'],
            'mime' => $result['mime_type'],
        ], 'Uploaded', 201);
    }
}
