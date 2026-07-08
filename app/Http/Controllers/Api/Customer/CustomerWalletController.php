<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerWalletService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerWalletController extends Controller
{
    public function __construct(protected CustomerWalletService $walletService)
    {
    }

    public function balance(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->walletService->balance($request->user()),
            'Wallet balance retrieved'
        );
    }

    public function transactions(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->walletService->transactions(
                $request->user()->id,
                (int) $request->query('per_page', 20)
            ),
            'Wallet transactions retrieved'
        );
    }

    public function topUp(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'max:64'],
            'payment_status' => ['nullable', 'string', 'max:64'],
            'note' => ['nullable', 'string', 'max:255'],
            'serviceType' => ['nullable', 'string', 'max:64'],
            'order_id' => ['nullable', 'string', 'max:64'],
        ]);

        $result = $this->walletService->topUp($request->user()->id, $request->all());

        return ApiResponse::success($result, 'Wallet topped up', 201);
    }
}
