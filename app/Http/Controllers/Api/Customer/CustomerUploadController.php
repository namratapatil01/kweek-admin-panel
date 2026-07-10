<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Storage\FileStorageService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerUploadController extends Controller
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
            $data['directory'] ?? 'uploads',
            $data['visibility'] ?? 'public'
        );

        return ApiResponse::success($result, 'Uploaded', 201);
    }
}
