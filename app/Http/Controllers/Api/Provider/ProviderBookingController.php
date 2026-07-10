<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Provider\ProviderBookingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderBookingController extends Controller
{
    public function __construct(protected ProviderBookingService $bookingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->bookingService->list(
                $user->id,
                $request->input('tab', 'new'),
                (int) $request->input('per_page', 20),
                $request->input('since')
            ),
            'Bookings retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $booking = $this->bookingService->show($user->id, $id);

        if (! $booking) {
            return ApiResponse::error('Booking not found', 404);
        }

        return ApiResponse::success($booking);
    }

    public function accept(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'newScheduleDateTime' => ['nullable', 'string'],
            'scheduleDateTime' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->bookingService->accept($user, $id, $data),
            'Booking accepted'
        );
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->bookingService->reject($user, $id, $data),
            'Booking rejected'
        );
    }

    public function assignWorker(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'workerId' => ['required', 'string', 'max:64'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->bookingService->assignWorker($user, $id, $data['workerId']),
            'Worker assigned'
        );
    }

    public function start(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->bookingService->start($user, $id),
            'Booking started'
        );
    }

    public function stopTimer(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->bookingService->stopTimer($user, $id),
            'Timer stopped'
        );
    }

    public function extraCharges(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'extraCharges' => ['required_without:amount', 'numeric', 'min:0'],
            'amount' => ['required_without:extraCharges', 'numeric', 'min:0'],
            'extraChargesDescription' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->bookingService->addExtraCharges($user, $id, $data),
            'Extra charges added'
        );
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'otp' => ['nullable', 'string', 'max:10'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->bookingService->complete($user, $id, $data['otp'] ?? null),
            'Booking completed'
        );
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
            'otp' => ['nullable', 'string'],
            'workerId' => ['nullable', 'string'],
            'newScheduleDateTime' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->bookingService->updateStatus($user, $id, $data['status'], $data),
            'Booking status updated'
        );
    }
}
