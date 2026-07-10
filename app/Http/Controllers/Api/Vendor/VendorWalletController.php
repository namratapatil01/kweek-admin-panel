<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorWalletService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorWalletController extends Controller
{
    public function __construct(protected VendorWalletService $walletService)
    {
    }

    public function balance(Request $request): JsonResponse
    {
        return ApiResponse::success($this->walletService->balance($request->user()));
    }

    public function transactions(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->walletService->transactions($request->user()->id, (int) $request->input('per_page', 20)),
            'Transactions retrieved'
        );
    }

    public function earnings(Request $request): JsonResponse
    {
        return ApiResponse::success($this->walletService->earnings($request->user()->id));
    }

    public function withdraw(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'withdrawMethod' => ['nullable', 'string', 'max:64'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        return ApiResponse::success($this->walletService->withdraw($request->user(), $data), 'Withdrawal request submitted');
    }

    public function payoutHistory(Request $request): JsonResponse
    {
        $vendorId = $request->user()->vendorID ?? $request->user()->id;

        return ApiResponse::paginated(
            $this->walletService->payoutHistory($vendorId, (int) $request->input('per_page', 20)),
            'Payout history retrieved'
        );
    }

    public function getWithdrawMethod(Request $request): JsonResponse
    {
        return ApiResponse::success($this->walletService->getWithdrawMethod($request->user()->id));
    }

    public function saveWithdrawMethod(Request $request): JsonResponse
    {
        $data = $request->validate([
            'flutterwave' => ['nullable', 'array'],
            'stripe' => ['nullable', 'array'],
            'razorpay' => ['nullable', 'array'],
            'paypal' => ['nullable', 'array'],
        ]);

        return ApiResponse::success(
            $this->walletService->saveWithdrawMethod($request->user()->id, $data),
            'Withdraw method saved'
        );
    }
}
