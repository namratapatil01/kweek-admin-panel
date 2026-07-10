<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\CustomerReferralRequest;
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

    public function validateReferral(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required_without:referralCode', 'string', 'max:64'],
            'referralCode' => ['required_without:code', 'string', 'max:64'],
        ]);

        $code = $request->query('code') ?? $request->query('referralCode');
        $referral = $this->miscService->validateReferralCode($code);

        if (! $referral) {
            return ApiResponse::error('Referral code not found', 404);
        }

        return ApiResponse::success($referral, 'Referral code is valid');
    }

    public function storeReferral(CustomerReferralRequest $request): JsonResponse
    {
        $referral = $this->miscService->createReferral(
            $request->user()->id,
            $request->validated()
        );

        return ApiResponse::success($referral, 'Referral created', 201);
    }

    public function notificationContent(Request $request, string $type): JsonResponse
    {
        return ApiResponse::success(
            $this->miscService->notificationContent($type),
            'Notification content retrieved'
        );
    }

    public function getComplaint(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => ['required', 'string', 'max:64'],
        ]);

        $complaint = $this->miscService->getComplaintByOrder(
            $request->user()->id,
            $request->query('orderId')
        );

        if (! $complaint) {
            return ApiResponse::success(null, 'No complaint found for this order');
        }

        return ApiResponse::success($complaint, 'Complaint retrieved');
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
        $request->validate([
            'giftId' => ['nullable', 'string', 'max:64'],
            'price' => ['nullable', 'numeric'],
            'payment_method' => ['nullable', 'string', 'max:64'],
        ]);

        $purchase = $this->miscService->purchaseGiftCard($request->user()->id, $request->all());

        return ApiResponse::success($purchase, 'Gift card purchased', 201);
    }

    public function giftCardHistory(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->miscService->giftCardHistory($request->user()->id, (int) $request->query('per_page', 20)),
            'Gift card history retrieved'
        );
    }

    public function redeemGiftCard(Request $request): JsonResponse
    {
        $request->validate([
            'giftCode' => ['required', 'string', 'max:64'],
        ]);

        $purchase = $this->miscService->redeemGiftCard(
            $request->user()->id,
            $request->input('giftCode')
        );

        return ApiResponse::success($purchase, 'Gift card redeemed');
    }

    public function processReferralRewards(Request $request): JsonResponse
    {
        $rewards = $this->miscService->processReferralRewards($request->user()->id);

        return ApiResponse::success($rewards, 'Referral rewards processed');
    }

    public function emailTemplate(Request $request, string $type): JsonResponse
    {
        $template = $this->miscService->emailTemplate($type);

        if (! $template) {
            return ApiResponse::error('Email template not found', 404);
        }

        return ApiResponse::success($template, 'Email template retrieved');
    }

    public function markNotificationRead(Request $request, string $id): JsonResponse
    {
        $notification = $this->miscService->markNotificationRead($id);

        if (! $notification) {
            return ApiResponse::error('Notification not found', 404);
        }

        return ApiResponse::success($notification, 'Notification marked as read');
    }

    public function storeComplaint(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'driverId' => ['nullable', 'string', 'max:64'],
            'driverID' => ['nullable', 'string', 'max:64'],
            'customerName' => ['nullable', 'string', 'max:255'],
            'driverName' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->miscService->complaintExists($request->user()->id, $request->input('orderId'))) {
            return ApiResponse::error('A complaint already exists for this order', 409);
        }

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
