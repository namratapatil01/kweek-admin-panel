<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Worker\WorkerJobService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerJobController extends Controller
{
    public function __construct(protected WorkerJobService $jobService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->jobService->list(
                $user,
                $request->input('tab', 'upcoming'),
                (int) $request->input('per_page', 20)
            ),
            'Jobs retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $job = $this->jobService->show($user, $id);

        if (! $job) {
            return ApiResponse::error('Job not found', 404);
        }

        return ApiResponse::success($job);
    }

    public function accept(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->jobService->accept($user, $id), 'Job accepted');
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->jobService->reject($user, $id, $data), 'Job rejected');
    }

    public function start(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->jobService->start($user, $id), 'Job started');
    }

    public function stopTimer(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->jobService->stopTimer($user, $id), 'Timer stopped');
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
            $this->jobService->addExtraCharges($user, $id, $data),
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
            $this->jobService->complete($user, $id, $data['otp'] ?? null),
            'Job completed'
        );
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
            'otp' => ['nullable', 'string'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->jobService->updateStatus($user, $id, $data['status'], $data),
            'Job status updated'
        );
    }
}
