<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerSettingsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerSettingsController extends Controller
{
    public function __construct(protected CustomerSettingsService $settingsService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->settingsService->publicSettings(), 'Settings retrieved');
    }

    public function payment(Request $request): JsonResponse
    {
        return ApiResponse::success($this->settingsService->paymentSettings(), 'Payment settings retrieved');
    }

    public function languages(Request $request): JsonResponse
    {
        return ApiResponse::success($this->settingsService->languages(), 'Languages retrieved');
    }

    public function deliveryCharge(Request $request): JsonResponse
    {
        return ApiResponse::success($this->settingsService->deliveryCharge(), 'Delivery charge retrieved');
    }

    public function show(Request $request, string $key): JsonResponse
    {
        $setting = $this->settingsService->setting($key);

        if (! $setting) {
            return ApiResponse::error('Setting not found', 404);
        }

        return ApiResponse::success($setting);
    }
}
