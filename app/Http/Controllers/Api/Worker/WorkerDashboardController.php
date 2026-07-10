<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Worker\WorkerDashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerDashboardController extends Controller
{
    public function __construct(protected WorkerDashboardService $dashboardService)
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
