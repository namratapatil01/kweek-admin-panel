<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerCouponService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCouponController extends Controller
{
    public function __construct(protected CustomerCouponService $couponService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['nullable', 'in:vendor,parcel,rental,provider,cab'],
        ]);

        return ApiResponse::paginated(
            $this->couponService->list(
                $request->query('type', 'vendor'),
                $request->query('section_id') ?? $request->query('sectionId'),
                $request->query('vendor_id') ?? $request->query('vendorID'),
                (int) $request->query('per_page', 20)
            ),
            'Coupons retrieved'
        );
    }
}
