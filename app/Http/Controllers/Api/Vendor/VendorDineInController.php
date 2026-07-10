<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorDineInService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorDineInController extends Controller
{
    public function __construct(protected VendorDineInService $dineInService)
    {
    }

    public function bookings(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->dineInService->bookings($request->user(), $request->input('tab', 'upcoming'), (int) $request->input('per_page', 20)),
            'Bookings retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $booking = $this->dineInService->show($request->user(), $id);
        if (! $booking) {
            return ApiResponse::error('Booking not found', 404);
        }

        return ApiResponse::success($booking);
    }

    public function accept(Request $request, string $id): JsonResponse
    {
        return ApiResponse::success($this->dineInService->accept($request->user(), $id), 'Booking accepted');
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        return ApiResponse::success($this->dineInService->reject($request->user(), $id, $data), 'Booking rejected');
    }

    public function updateConfig(Request $request): JsonResponse
    {
        return ApiResponse::success($this->dineInService->updateDineInConfig($request->user(), $request->all()), 'Dine-in config updated');
    }
}
