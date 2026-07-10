<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerReviewService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    public function __construct(protected CustomerReviewService $reviewService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->reviewService->list(
                $request->user()->id,
                $request->query('order_id') ?? $request->query('orderid'),
                $request->query('vendor_id') ?? $request->query('VendorId'),
                $request->query('product_id') ?? $request->query('productId'),
                (int) $request->query('per_page', 20)
            ),
            'Reviews retrieved'
        );
    }

    public function vendorReviews(Request $request, string $vendorId): JsonResponse
    {
        return ApiResponse::paginated(
            $this->reviewService->vendorReviews($vendorId, (int) $request->query('per_page', 20)),
            'Vendor reviews retrieved'
        );
    }

    public function serviceReviews(Request $request, string $serviceId): JsonResponse
    {
        return ApiResponse::paginated(
            $this->reviewService->serviceReviews($serviceId, (int) $request->query('per_page', 20)),
            'Service reviews retrieved'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'orderid' => ['required', 'string', 'max:64'],
            'VendorId' => ['nullable', 'string', 'max:64'],
            'productId' => ['nullable', 'string', 'max:64'],
            'driverId' => ['nullable', 'string', 'max:64'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
            'reviewAttributes' => ['nullable', 'array'],
        ]);

        $review = $this->reviewService->create($request->user()->id, $request->all());

        return ApiResponse::success($review, 'Review submitted', 201);
    }

    public function storeRating(Request $request): JsonResponse
    {
        $request->validate([
            'orderid' => ['required', 'string', 'max:64'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $rating = $this->reviewService->createRating($request->user()->id, $request->all());

        return ApiResponse::success($rating, 'Rating submitted', 201);
    }
}
