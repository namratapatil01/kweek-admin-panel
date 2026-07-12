<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Services\Worker\WorkerSettingsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class WorkerSettingsController extends Controller
{
    public function __construct(protected WorkerSettingsService $settingsService)
    {
    }

    public function index(): JsonResponse
    {
        return ApiResponse::success($this->settingsService->index(), 'Settings retrieved');
    }

    public function languages(): JsonResponse
    {
        return ApiResponse::success($this->settingsService->languages(), 'Languages retrieved');
    }

    public function show(string $key): JsonResponse
    {
        $setting = $this->settingsService->setting($key);

        if (! $setting) {
            return ApiResponse::error('Setting not found', 404);
        }

        return ApiResponse::success($setting);
    }
}
