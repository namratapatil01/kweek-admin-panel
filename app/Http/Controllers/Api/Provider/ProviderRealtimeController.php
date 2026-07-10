<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Services\Provider\ProviderRealtimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderRealtimeController extends Controller
{
    public function __construct(protected ProviderRealtimeService $realtimeService)
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
