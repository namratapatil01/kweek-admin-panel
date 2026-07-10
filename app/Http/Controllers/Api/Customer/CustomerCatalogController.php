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

    public function nearestVendors(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius' => ['nullable', 'numeric', 'min:0.1'],
            'dine_in' => ['nullable', 'boolean'],
        ]);

        return ApiResponse::paginated(
            $this->catalogService->nearestVendors(
                (float) $request->query('latitude'),
                (float) $request->query('longitude'),
                $request->query('section_id') ?? $request->query('sectionId'),
                $request->query('category_id') ?? $request->query('categoryID'),
                $request->query('radius') ? (float) $request->query('radius') : null,
                $request->boolean('dine_in'),
                (int) $request->query('per_page', 20)
            ),
            'Nearest vendors retrieved'
        );
    }

    public function advertisements(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->advertisements(
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Advertisements retrieved'
        );
    }

    public function banners(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->banners(
                $request->query('section_id') ?? $request->query('sectionId'),
                $request->query('type'),
                (int) $request->query('per_page', 20)
            ),
            'Banners retrieved'
        );
    }

    public function stories(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->stories(
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Stories retrieved'
        );
    }

    public function zones(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->zones((int) $request->query('per_page', 50)),
            'Zones retrieved'
        );
    }

    public function taxes(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->taxes(
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 50)
            ),
            'Taxes retrieved'
        );
    }

    public function parcelCategories(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->parcelCategories((int) $request->query('per_page', 20)),
            'Parcel categories retrieved'
        );
    }

    public function parcelWeights(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->parcelWeights((int) $request->query('per_page', 20)),
            'Parcel weights retrieved'
        );
    }

    public function vehicleTypes(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->vehicleTypes(
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Vehicle types retrieved'
        );
    }

    public function popularDestinations(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->popularDestinations(
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Popular destinations retrieved'
        );
    }

    public function rentalVehicleTypes(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->rentalVehicleTypes(
                $request->query('section_id') ?? $request->query('sectionId'),
                (int) $request->query('per_page', 20)
            ),
            'Rental vehicle types retrieved'
        );
    }

    public function rentalPackages(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->rentalPackages(
                $request->query('vehicle_id') ?? $request->query('vehicleId'),
                (int) $request->query('per_page', 20)
            ),
            'Rental packages retrieved'
        );
    }

    public function providerWorkers(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->providerWorkers(
                $request->query('provider_id') ?? $request->query('providerId'),
                (int) $request->query('per_page', 20)
            ),
            'Provider workers retrieved'
        );
    }

    public function reviewAttributes(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->reviewAttributes(
                $request->query('vendor_id') ?? $request->query('vendorId'),
                (int) $request->query('per_page', 50)
            ),
            'Review attributes retrieved'
        );
    }

    public function vendorAttributes(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            $this->catalogService->vendorAttributes(
                $request->query('vendor_id') ?? $request->query('vendorId'),
                (int) $request->query('per_page', 50)
            ),
            'Vendor attributes retrieved'
        );
    }

    public function vendorCuisines(Request $request, string $vendorId): JsonResponse
    {
        return ApiResponse::success(
            $this->catalogService->vendorCuisines($vendorId)->values(),
            'Vendor cuisines retrieved'
        );
    }
}
