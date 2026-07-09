<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorOrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorOrderController extends Controller
{
    public function __construct(protected VendorOrderService $orderService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->orderService->list($request->user(), $request->input('tab', 'new'), (int) $request->input('per_page', 20)),
            'Orders retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $order = $this->orderService->show($request->user(), $id);
        if (! $order) {
            return ApiResponse::error('Order not found', 404);
        }

        return ApiResponse::success($order);
    }

    public function accept(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'estimatedTimeToPrepare' => ['nullable', 'string'],
            'courierCompanyName' => ['nullable', 'string'],
            'courierTrackingId' => ['nullable', 'string'],
        ]);

        return ApiResponse::success($this->orderService->accept($request->user(), $id, $data), 'Order accepted');
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        return ApiResponse::success($this->orderService->reject($request->user(), $id, $data), 'Order rejected');
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        return ApiResponse::success($this->orderService->cancel($request->user(), $id, $data), 'Order cancelled');
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        return ApiResponse::success($this->orderService->complete($request->user(), $id), 'Order completed');
    }

    public function assignDriver(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['driverId' => ['required', 'string', 'max:64']]);

        return ApiResponse::success(
            $this->orderService->assignDriver($request->user(), $id, $data['driverId']),
            'Driver assigned'
        );
    }

    public function ship(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'courierCompanyName' => ['required', 'string', 'max:255'],
            'courierTrackingId' => ['nullable', 'string', 'max:255'],
        ]);

        return ApiResponse::success($this->orderService->ship($request->user(), $id, $data), 'Order shipped');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return ApiResponse::success($this->orderService->update($request->user(), $id, $request->all()), 'Order updated');
    }
}
