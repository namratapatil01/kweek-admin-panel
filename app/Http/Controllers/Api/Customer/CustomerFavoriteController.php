<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerFavoriteService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFavoriteController extends Controller
{
    public function __construct(protected CustomerFavoriteService $favoriteService)
    {
    }

    public function index(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'type' => ['nullable'],
        ]);

        if (! in_array($type, ['vendor', 'item', 'service', 'provider'], true)) {
            return ApiResponse::error('Invalid favorite type', 422);
        }

        return ApiResponse::paginated(
            $this->favoriteService->list(
                $request->user()->id,
                $type,
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Favorites retrieved'
        );
    }

    public function store(Request $request, string $type): JsonResponse
    {
        if (! in_array($type, ['vendor', 'item', 'service', 'provider'], true)) {
            return ApiResponse::error('Invalid favorite type', 422);
        }

        $favorite = $this->favoriteService->add(
            $request->user()->id,
            $type,
            $request->all()
        );

        return ApiResponse::success($favorite, 'Favorite added', 201);
    }

    public function destroy(Request $request, string $type, string $id): JsonResponse
    {
        if (! in_array($type, ['vendor', 'item', 'service', 'provider'], true)) {
            return ApiResponse::error('Invalid favorite type', 422);
        }

        $deleted = $this->favoriteService->remove($request->user()->id, $type, $id);

        if (! $deleted) {
            return ApiResponse::error('Favorite not found', 404);
        }

        return ApiResponse::success(null, 'Favorite removed');
    }
}
