<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Driver\DriverWalletService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverWalletController extends Controller
{
    public function __construct(protected DriverWalletService $walletService)
    {
    }

    public function balance(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->walletService->balance($user));
    }

    public function transactions(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->walletService->transactions($user->id, (int) $request->input('per_page', 20)),
            'Transactions retrieved'
        );
    }

    public function earnings(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->walletService->earnings($user->id));
    }

    public function withdraw(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'withdrawMethod' => ['nullable', 'string', 'max:64'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->walletService->withdraw($user, $data),
            'Withdrawal request submitted'
        );
    }

    public function payoutHistory(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->walletService->payoutHistory($user->id, (int) $request->input('per_page', 20)),
            'Payout history retrieved'
        );
    }

    public function getWithdrawMethod(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success($this->walletService->getWithdrawMethod($user->id));
    }

    public function saveWithdrawMethod(Request $request): JsonResponse
    {
        $data = $request->validate([
            'flutterwave' => ['nullable', 'array'],
            'stripe' => ['nullable', 'array'],
            'razorpay' => ['nullable', 'array'],
            'paypal' => ['nullable', 'array'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->walletService->saveWithdrawMethod($user->id, $data),
            'Withdraw method saved'
        );
    }
}
