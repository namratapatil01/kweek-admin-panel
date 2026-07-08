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
                (int) $request->query('per_page', 20)
            ),
            'Reviews retrieved'
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
