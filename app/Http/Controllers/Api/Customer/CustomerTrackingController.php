<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerTrackingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerTrackingController extends Controller
{
    public function __construct(protected CustomerTrackingService $trackingService)
    {
    }

    public function order(Request $request, string $type, string $id): JsonResponse
    {
        $tracking = $this->trackingService->trackOrder($request->user()->id, $type, $id);

        if (! $tracking) {
            return ApiResponse::error('Order not found', 404);
        }

        return ApiResponse::success($tracking, 'Tracking data retrieved');
    }

    public function driverLocation(Request $request, string $driverId): JsonResponse
    {
        $location = $this->trackingService->driverLocation($driverId);

        if (! $location) {
            return ApiResponse::error('Driver not found', 404);
        }

        return ApiResponse::success($location, 'Driver location retrieved');
    }
}
