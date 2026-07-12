<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorDashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function __construct(protected VendorDashboardService $dashboardService)
    {
    }

    public function home(Request $request): JsonResponse
    {
        return ApiResponse::success($this->dashboardService->home($request->input('sectionId')));
    }

    public function dashboard(Request $request): JsonResponse
    {
        return ApiResponse::success($this->dashboardService->dashboard($request->user()));
    }
}
