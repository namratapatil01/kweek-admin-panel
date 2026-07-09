<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerMiscService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerMiscController extends Controller
{
    public function __construct(protected CustomerMiscService $miscService)
    {
    }

    public function notifications(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->miscService->notifications('customer', (int) $request->query('per_page', 20)),
            'Notifications retrieved'
        );
    }

    public function referral(Request $request): JsonResponse
    {
        $referral = $this->miscService->referral($request->user()->id);

        return ApiResponse::success($referral, 'Referral data retrieved');
    }

    public function giftCards(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->miscService->giftCards((int) $request->query('per_page', 20)),
            'Gift cards retrieved'
        );
    }

    public function purchaseGiftCard(Request $request): JsonResponse
    {
        $purchase = $this->miscService->purchaseGiftCard($request->user()->id, $request->all());

        return ApiResponse::success($purchase, 'Gift card purchased', 201);
    }

    public function complaints(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'driverId' => ['nullable', 'string', 'max:64'],
        ]);

        $complaint = $this->miscService->createComplaint($request->user()->id, $request->all());

        return ApiResponse::success($complaint, 'Complaint submitted', 201);
    }

    public function sos(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => ['required', 'string', 'max:64'],
            'latLong' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'max:64'],
        ]);

        $sos = $this->miscService->createSos($request->user()->id, $request->all());

        return ApiResponse::success($sos, 'SOS alert sent', 201);
    }
}
