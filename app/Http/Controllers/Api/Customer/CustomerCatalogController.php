<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerCatalogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCatalogController extends Controller
{
    public function __construct(protected CustomerCatalogService $catalogService)
    {
    }

    public function sections(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->sections(),
            'Sections retrieved'
        );
    }

    public function categories(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->categories(
                $request->query('type', 'vendor'),
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Categories retrieved'
        );
    }

    public function vendors(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->vendors(
                $request->query('section_id') ?? $request->query('sectionId'),
                $request->query('category_id') ?? $request->query('categoryID'),
                (int) $request->query('per_page', 20)
            ),
            'Vendors retrieved'
        );
    }

    public function products(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->products(
                $request->query('section_id') ?? $request->query('sectionId'),
                $request->query('vendor_id') ?? $request->query('vendorID'),
                $request->query('category_id') ?? $request->query('categoryID'),
                (int) $request->query('per_page', 20)
            ),
            'Products retrieved'
        );
    }

    public function services(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->services(
                $request->query('section_id') ?? $request->query('sectionId'),
                $request->query('category_id') ?? $request->query('categoryId'),
                (int) $request->query('per_page', 20)
            ),
            'Services retrieved'
        );
    }

    public function brands(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->brands(
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Brands retrieved'
        );
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:1'],
            'type' => ['nullable', 'in:all,vendor,product,service'],
        ]);

        $results = $this->catalogService->search(
            $request->query('q'),
            $request->query('section_id') ?? $request->query('sectionId'),
            $request->query('type', 'all'),
            (int) $request->query('per_page', 20)
        );

        return ApiResponse::success($results, 'Search results');
    }

    public function show(Request $request, string $type, string $id): JsonResponse
    {
        $item = $this->catalogService->show($type, $id);

        if (! $item) {
            return ApiResponse::error('Item not found', 404);
        }

        return ApiResponse::success($item);
    }
}
