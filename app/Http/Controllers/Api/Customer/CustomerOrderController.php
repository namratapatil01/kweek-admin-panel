<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\CustomerOrderRequest;
use App\Services\Customer\CustomerOrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function __construct(protected CustomerOrderService $orderService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:vendor,parcel,rental,ride,provider,dine-in'],
        ]);

        return ApiResponse::paginated(
            $this->orderService->list(
                $request->user()->id,
                $request->query('type'),
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Orders retrieved'
        );
    }

    public function show(Request $request, string $type, string $id): JsonResponse
    {
        $order = $this->orderService->show($request->user()->id, $type, $id);

        if (! $order) {
            return ApiResponse::error('Order not found', 404);
        }

        return ApiResponse::success($order);
    }

    public function store(CustomerOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $type = $data['type'];
        unset($data['type']);

        $order = $this->orderService->create($request->user()->id, $type, $data);

        return ApiResponse::success($order, 'Order created', 201);
    }

    public function updateStatus(Request $request, string $type, string $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'max:64'],
        ]);

        $order = $this->orderService->updateStatus(
            $request->user()->id,
            $type,
            $id,
            $request->input('status')
        );

        if (! $order) {
            return ApiResponse::error('Order not found', 404);
        }

        return ApiResponse::success($order, 'Order status updated');
    }
}
