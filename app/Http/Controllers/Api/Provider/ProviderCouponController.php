<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Provider\ProviderCouponService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderCouponController extends Controller
{
    public function __construct(protected ProviderCouponService $couponService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->couponService->list($user->id, (int) $request->input('per_page', 20)),
            'Coupons retrieved'
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $coupon = $this->couponService->show($user->id, $id);

        if (! $coupon) {
            return ApiResponse::error('Coupon not found', 404);
        }

        return ApiResponse::success($coupon);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'discount' => ['required', 'numeric', 'min:0'],
            'discountType' => ['nullable', 'string', 'max:32'],
            'expiresAt' => ['nullable', 'string'],
            'isEnabled' => ['nullable', 'boolean'],
            'isPublic' => ['nullable', 'boolean'],
            'image' => ['nullable', 'string'],
            'sectionId' => ['nullable', 'string', 'max:64'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->couponService->create($user, $data),
            'Coupon created',
            201
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:64'],
            'discount' => ['sometimes', 'numeric', 'min:0'],
            'discountType' => ['nullable', 'string', 'max:32'],
            'expiresAt' => ['nullable', 'string'],
            'isEnabled' => ['nullable', 'boolean'],
            'isPublic' => ['nullable', 'boolean'],
            'image' => ['nullable', 'string'],
            'sectionId' => ['nullable', 'string', 'max:64'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $coupon = $this->couponService->update($user->id, $id, $data);

        if (! $coupon) {
            return ApiResponse::error('Coupon not found', 404);
        }

        return ApiResponse::success($coupon, 'Coupon updated');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        if (! $this->couponService->delete($user->id, $id)) {
            return ApiResponse::error('Coupon not found', 404);
        }

        return ApiResponse::success(null, 'Coupon deleted');
    }

    public function uploadImage(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();
        $coupon = $this->couponService->uploadImage($user->id, $id, $request->file('image'));

        if (! $coupon) {
            return ApiResponse::error('Coupon not found', 404);
        }

        return ApiResponse::success($coupon, 'Coupon image uploaded');
    }
}
