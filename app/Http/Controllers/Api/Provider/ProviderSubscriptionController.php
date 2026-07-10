<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Provider\ProviderConfirmSubscriptionPaymentRequest;
use App\Models\AppUser;
use App\Services\Provider\ProviderSubscriptionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderSubscriptionController extends Controller
{
    public function __construct(protected ProviderSubscriptionService $subscriptionService)
    {
    }

    public function plans(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->subscriptionService->plans(
                $request->input('sectionId') ?? $user->sectionId ?? $user->section_id,
                $request->has('isCommissionPlan')
                    ? filter_var($request->input('isCommissionPlan'), FILTER_VALIDATE_BOOLEAN)
                    : null,
                (int) $request->input('per_page', 50)
            ),
            'Subscription plans retrieved'
        );
    }

    public function history(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::paginated(
            $this->subscriptionService->history($user->id, (int) $request->input('per_page', 20)),
            'Subscription history retrieved'
        );
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => ['required_without:subscriptionPlanId', 'string', 'max:64'],
            'subscriptionPlanId' => ['required_without:plan_id', 'string', 'max:64'],
            'payment_type' => ['nullable', 'string', 'max:64'],
            'payment_method' => ['nullable', 'string', 'max:64'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->subscriptionService->subscribe($user, $data),
            'Subscription activated',
            201
        );
    }

    public function confirmPayment(ProviderConfirmSubscriptionPaymentRequest $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->subscriptionService->confirmPayment($user, $request->validated()),
            'Subscription payment confirmed',
            201
        );
    }
}
