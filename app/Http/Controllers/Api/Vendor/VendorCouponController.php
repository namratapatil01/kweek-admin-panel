<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorCouponService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorCouponController extends Controller
{
    public function __construct(protected VendorCouponService $couponService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->couponService->list($request->user(), (int) $request->input('per_page', 20)),
            'Coupons retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $coupon = $this->couponService->show($request->user(), $id);
        if (! $coupon) {
            return ApiResponse::error('Coupon not found', 404);
        }

        return ApiResponse::success($coupon);
    }

    public function store(Request $request): JsonResponse
    {
        return ApiResponse::success($this->couponService->create($request->user(), $request->all()), 'Coupon created', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $coupon = $this->couponService->update($request->user(), $id, $request->all());
        if (! $coupon) {
            return ApiResponse::error('Coupon not found', 404);
        }

        return ApiResponse::success($coupon, 'Coupon updated');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (! $this->couponService->delete($request->user(), $id)) {
            return ApiResponse::error('Coupon not found', 404);
        }

        return ApiResponse::success(null, 'Coupon deleted');
    }

    public function uploadImage(Request $request, string $id): JsonResponse
    {
        $request->validate(['image' => ['required', 'file', 'image', 'max:5120']]);
        $coupon = $this->couponService->uploadImage($request->user(), $id, $request->file('image'));
        if (! $coupon) {
            return ApiResponse::error('Coupon not found', 404);
        }

        return ApiResponse::success($coupon, 'Coupon image uploaded');
    }
}
