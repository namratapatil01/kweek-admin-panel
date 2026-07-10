<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Driver\DriverTrackingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverTrackingController extends Controller
{
    public function __construct(protected DriverTrackingService $trackingService)
    {
    }

    public function order(Request $request, string $type, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $data = $this->trackingService->trackOrder($user, $type, $id);

        if (! $data) {
            return ApiResponse::error('Order not found', 404);
        }

        return ApiResponse::success($data);
    }
}
