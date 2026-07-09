<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorSubscriptionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorSubscriptionController extends Controller
{
    public function __construct(protected VendorSubscriptionService $subscriptionService)
    {
    }

    public function plans(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->subscriptionService->plans($request->input('sectionId'), (int) $request->input('per_page', 50)),
            'Plans retrieved'
        );
    }

    public function history(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->subscriptionService->history($request->user()->id, (int) $request->input('per_page', 20)),
            'Subscription history retrieved'
        );
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => ['required_without:subscriptionPlanId', 'string'],
            'subscriptionPlanId' => ['required_without:plan_id', 'string'],
            'payment_type' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
        ]);

        return ApiResponse::success($this->subscriptionService->subscribe($request->user(), $data), 'Subscribed successfully');
    }
}
