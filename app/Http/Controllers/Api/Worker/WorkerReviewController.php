<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Worker\WorkerReviewService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerReviewController extends Controller
{
    public function __construct(protected WorkerReviewService $reviewService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->reviewService->list($user, (int) $request->input('per_page', 20)),
            'Reviews retrieved'
        );
    }

    public function forOrder(Request $request, string $orderId): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $review = $this->reviewService->forOrder($user, $orderId);

        if (! $review) {
            return ApiResponse::error('Review not found', 404);
        }

        return ApiResponse::success($review);
    }

    public function ratings(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->reviewService->ratings($user, (int) $request->input('per_page', 20)),
            'Ratings retrieved'
        );
    }
}
