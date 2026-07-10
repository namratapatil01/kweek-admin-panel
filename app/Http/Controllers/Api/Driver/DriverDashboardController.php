<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Driver\DriverDashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverDashboardController extends Controller
{
    public function __construct(protected DriverDashboardService $dashboardService)
    {
    }

    public function home(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->dashboardService->home($request->input('serviceType'))
        );
    }

    public function dashboard(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->dashboardService->dashboard($user));
    }
}
