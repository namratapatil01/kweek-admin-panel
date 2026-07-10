<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\Driver\DriverSettingsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverSettingsController extends Controller
{
    public function __construct(protected DriverSettingsService $settingsService)
    {
    }

    public function index(): JsonResponse
    {
        return ApiResponse::success($this->settingsService->index(), 'Settings retrieved');
    }

    public function payment(): JsonResponse
    {
        return ApiResponse::success($this->settingsService->paymentSettings(), 'Payment settings retrieved');
    }

    public function languages(): JsonResponse
    {
        return ApiResponse::success($this->settingsService->languages(), 'Languages retrieved');
    }

    public function taxes(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->settingsService->taxes($request->input('country')),
            'Taxes retrieved'
        );
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
