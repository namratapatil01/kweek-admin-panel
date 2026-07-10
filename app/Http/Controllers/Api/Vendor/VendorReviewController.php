<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorReviewService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorReviewController extends Controller
{
    public function __construct(protected VendorReviewService $reviewService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->reviewService->list($request->user(), (int) $request->input('per_page', 20)),
            'Reviews retrieved'
        );
    }

    public function forOrder(Request $request, string $orderId): JsonResponse
    {
        $review = $this->reviewService->forOrder($request->user(), $orderId, $request->input('productId'));
        if (! $review) {
            return ApiResponse::error('Review not found', 404);
        }

        return ApiResponse::success($review);
    }

    public function ratings(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->reviewService->ratings($request->user(), (int) $request->input('per_page', 20)),
            'Ratings retrieved'
        );
    }
}
