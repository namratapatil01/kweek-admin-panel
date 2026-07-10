<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Worker\WorkerRealtimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerRealtimeController extends Controller
{
    public function __construct(protected WorkerRealtimeService $realtimeService)
    {
    }

    public function poll(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        return ApiResponse::success(
            $this->realtimeService->poll(
                $user,
                $request->input('since'),
                $request->input('tab')
            ),
            'Realtime poll successful'
        );
    }
}
