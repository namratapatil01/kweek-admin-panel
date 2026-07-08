<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Provider\ProviderDashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderDashboardController extends Controller
{
    public function __construct(protected ProviderDashboardService $dashboardService)
    {
    }

    public function home(): JsonResponse
    {
        return ApiResponse::success($this->dashboardService->home());
    }

    public function dashboard(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->dashboardService->dashboard($user));
    }
}
