<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerDashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function __construct(protected CustomerDashboardService $dashboardService)
    {
    }

    public function dashboard(Request $request): JsonResponse
    {
        $data = $this->dashboardService->dashboard(
            $request->user()->id,
            $request->query('section_id') ?? $request->query('sectionId')
        );

        return ApiResponse::success($data);
    }

    public function home(Request $request): JsonResponse
    {
        $data = $this->dashboardService->home(
            $request->query('section_id') ?? $request->query('sectionId')
        );

        return ApiResponse::success($data);
    }
}
