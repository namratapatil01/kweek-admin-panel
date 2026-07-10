<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerCashbackService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCashbackController extends Controller
{
    public function __construct(protected CustomerCashbackService $cashbackService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->cashbackService->list((int) $request->query('per_page', 20)),
            'Cashback offers retrieved'
        );
    }

    public function redeemed(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->cashbackService->redeemed(
                $request->user()->id,
                $request->query('cashback_id') ?? $request->query('cashbackId'),
                (int) $request->query('per_page', 20)
            ),
            'Redeemed cashbacks retrieved'
        );
    }

    public function redeem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cashbackId' => ['required', 'string', 'max:64'],
            'orderId' => ['nullable', 'string', 'max:64'],
        ]);

        $redeem = $this->cashbackService->redeem($request->user()->id, $data);

        return ApiResponse::success($redeem, 'Cashback redeemed', 201);
    }
}
