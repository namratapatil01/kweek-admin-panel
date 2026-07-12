<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\Provider\ProviderLanguageResource;
use App\Services\Provider\ProviderSettingsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProviderSettingsController extends Controller
{
    public function __construct(protected ProviderSettingsService $settingsService)
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
        return ApiResponse::success(
            ProviderLanguageResource::collection(
                collect($this->settingsService->languages())
            )->resolve(),
            'Languages retrieved'
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
