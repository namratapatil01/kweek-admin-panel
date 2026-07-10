<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Driver\DriverOrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverOrderController extends Controller
{
    public function __construct(protected DriverOrderService $orderService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->orderService->list(
                $user,
                $request->input('type'),
                $request->input('tab', 'active'),
                (int) $request->input('per_page', 20)
            ),
            'Orders retrieved'
        );
    }

    public function show(Request $request, string $type, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $order = $this->orderService->show($user, $type, $id);

        if (! $order) {
            return ApiResponse::error('Order not found', 404);
        }

        return ApiResponse::success($order);
    }

    public function accept(Request $request, string $type, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->orderService->accept($user, $type, $id, $request->all()),
            'Order accepted'
        );
    }

    public function reject(Request $request, string $type, string $id): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->orderService->reject($user, $type, $id, $data),
            'Order rejected'
        );
    }

    public function start(Request $request, string $type, string $id): JsonResponse
    {
        $data = $request->validate([
            'otp' => ['nullable', 'string', 'max:10'],
            'startKilometerReading' => ['nullable', 'numeric', 'min:0'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->orderService->start($user, $type, $id, $data['otp'] ?? null, $data),
            'Order started'
        );
    }

    public function complete(Request $request, string $type, string $id): JsonResponse
    {
        $data = $request->validate([
            'otp' => ['nullable', 'string', 'max:10'],
            'endKilometerReading' => ['nullable', 'numeric', 'min:0'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->orderService->complete($user, $type, $id, $data['otp'] ?? null, $data),
            'Order completed'
        );
    }

    public function searchParcel(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->orderService->searchParcel($user, $request->all()),
            'Parcel orders retrieved'
        );
    }

    public function searchRental(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->orderService->searchRental($user, $request->all()),
            'Rental orders retrieved'
        );
    }

    public function stream(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->orderService->stream($user, $request->all()),
            'Order stream retrieved'
        );
    }

    public function updateStatus(Request $request, string $type, string $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
            'otp' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->orderService->updateStatus($user, $type, $id, $data['status'], $data),
            'Order status updated'
        );
    }
}
