<?php

namespace App\Services\Customer;

use App\Models\Advertisement;
use App\Models\BannerItem;
use App\Models\Brand;
use App\Models\ParcelCategory;
use App\Models\ParcelWeight;
use App\Models\PopularDestination;
use App\Models\ProviderCategory;
use App\Models\ProviderService;
use App\Models\ProviderWorker;
use App\Models\RentalPackage;
use App\Models\RentalVehicleType;
use App\Models\ReviewAttribute;
use App\Models\Section;
use App\Models\Story;
use App\Models\Tax;
use App\Models\VehicleType;
use App\Models\Vendor;
use App\Models\VendorAttribute;
use App\Models\VendorCategory;
use App\Models\VendorProduct;
use App\Models\Zone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CustomerCatalogService
{
    public function sections(): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            Section::query()->where('isActive', true)->orderBy('name')
        );
    }

    public function categories(string $type, ?string $sectionId = null, int $perPage = 20): LengthAwarePaginator
    {
        return match ($type) {
            'provider' => $this->paginateDocuments(
                ProviderCategory::query()
                    ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
                    ->where('publish', true)
                    ->orderBy('title')
            , $perPage),
            default => $this->paginateDocuments(
                VendorCategory::query()
                    ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                    ->where('publish', true)
                    ->orderBy('title')
            , $perPage),
        };
    }

    public function vendors(?string $sectionId = null, ?string $categoryId = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = Vendor::query()
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->orderBy('title');

        if ($categoryId) {
            $query->whereJsonContains('categoryID', $categoryId);
        }

        return $this->paginateDocuments($query, $perPage);
    }

    public function products(?string $sectionId = null, ?string $vendorId = null, ?string $categoryId = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = VendorProduct::query()
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->when($vendorId, fn ($q) => $q->where('vendorID', $vendorId))
            ->when($categoryId, fn ($q) => $q->where('categoryID', $categoryId))
            ->where('publish', true)
            ->orderByDesc('createdAt');

        return $this->paginateDocuments($query, $perPage);
    }

    public function services(?string $sectionId = null, ?string $categoryId = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = ProviderService::query()
            ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
            ->when($categoryId, fn ($q) => $q->where('categoryId', $categoryId))
            ->where('publish', true)
            ->orderBy('name');

        return $this->paginateDocuments($query, $perPage);
    }

    public function brands(?string $sectionId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            Brand::query()
                ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
                ->published()
                ->orderBy('title')
        , $perPage);
    }

    public function search(string $query, ?string $sectionId = null, ?string $type = 'all', int $perPage = 20): array
    {
        $like = '%' . $query . '%';
        $results = [];

        if (in_array($type, ['all', 'vendor'], true)) {
            $results['vendors'] = Vendor::query()
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->where('title', 'like', $like)
                ->limit($perPage)
                ->get()
                ->map(fn ($v) => $v->toDocumentArray());
        }

        if (in_array($type, ['all', 'product'], true)) {
            $results['products'] = VendorProduct::query()
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->where('publish', true)
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)->orWhere('title', 'like', $like);
                })
                ->limit($perPage)
                ->get()
                ->map(fn ($p) => $p->toDocumentArray());
        }

        if (in_array($type, ['all', 'service'], true)) {
            $results['services'] = ProviderService::query()
                ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
                ->where('publish', true)
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)->orWhere('title', 'like', $like);
                })
                ->limit($perPage)
                ->get()
                ->map(fn ($s) => $s->toDocumentArray());
        }

        return $results;
    }

    public function show(string $type, string $id): ?array
    {
        $model = match ($type) {
            'vendor' => Vendor::query()->find($id),
            'product' => VendorProduct::query()->find($id),
            'service' => ProviderService::query()->find($id),
            'category' => VendorCategory::query()->find($id),
            'provider-category' => ProviderCategory::query()->find($id),
            'brand' => Brand::query()->find($id),
            'worker' => ProviderWorker::query()->find($id),
            default => null,
        };

        return $model?->toDocumentArray();
    }

    public function nearestVendors(
        float $latitude,
        float $longitude,
        ?string $sectionId = null,
        ?string $categoryId = null,
        ?float $radiusKm = null,
        bool $dineInOnly = false,
        int $perPage = 20
    ): LengthAwarePaginator {
        $radiusKm = $radiusKm ?? $this->sectionRadiusKm($sectionId);

        $query = Vendor::query()
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->when($categoryId, fn ($q) => $q->whereJsonContains('categoryID', $categoryId))
            ->when($dineInOnly, fn ($q) => $q->where(function ($q) {
                $q->where('dine_in_active', true)
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.enabledDiveInFuture')) IN ('true', '1', 1)");
            }))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw('vendors.*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance', [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');

        return $this->paginateDocuments($query, $perPage);
    }

    public function advertisements(?string $sectionId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            Advertisement::query()
                ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
                ->orderByDesc('createdAt'),
            $perPage
        );
    }

    public function banners(?string $sectionId = null, ?string $type = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            BannerItem::query()
                ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
                ->when($type, fn ($q) => $q->where('type', $type))
                ->published()
                ->orderByRaw("CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.set_order')), '0') AS UNSIGNED)"),
            $perPage
        );
    }

    public function stories(?string $sectionId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            Story::query()
                ->when($sectionId, fn ($q) => $q->where('sectionID', $sectionId))
                ->orderByDesc('createdAt'),
            $perPage
        );
    }

    public function zones(int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            Zone::query()->where('publish', true)->orderBy('name'),
            $perPage
        );
    }

    public function taxes(?string $sectionId = null, int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            Tax::query()
                ->when($sectionId, fn ($q) => $q->where('sectionId', $sectionId))
                ->orderBy('title'),
            $perPage
        );
    }

    public function parcelCategories(int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            ParcelCategory::query()->where('publish', true)->orderBy('title'),
            $perPage
        );
    }

    public function parcelWeights(int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            ParcelWeight::query()->orderBy('title'),
            $perPage
        );
    }

    public function vehicleTypes(?string $sectionId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            VehicleType::query()
                ->when($sectionId, fn ($q) => $q->where(function ($q) use ($sectionId) {
                    $q->where('section_id', $sectionId)->orWhere('sectionId', $sectionId);
                }))
                ->orderBy('name'),
            $perPage
        );
    }

    public function popularDestinations(?string $sectionId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            PopularDestination::query()
                ->when($sectionId, fn ($q) => $q->where(function ($q) use ($sectionId) {
                    $q->where('section_id', $sectionId)->orWhere('sectionId', $sectionId);
                }))
                ->orderBy('title'),
            $perPage
        );
    }

    public function rentalVehicleTypes(?string $sectionId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            RentalVehicleType::query()
                ->when($sectionId, fn ($q) => $q->where(function ($q) use ($sectionId) {
                    $q->where('section_id', $sectionId)->orWhere('sectionId', $sectionId);
                }))
                ->orderBy('name'),
            $perPage
        );
    }

    public function rentalPackages(?string $vehicleId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            RentalPackage::query()
                ->when($vehicleId, fn ($q) => $q->where('vehicleId', $vehicleId))
                ->orderBy('title'),
            $perPage
        );
    }

    public function providerWorkers(?string $providerId = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            ProviderWorker::query()
                ->when($providerId, fn ($q) => $q->where('providerId', $providerId))
                ->orderBy('firstName'),
            $perPage
        );
    }

    public function reviewAttributes(?string $vendorId = null, int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            ReviewAttribute::query()
                ->when($vendorId, fn ($q) => $q->where('vendorId', $vendorId))
                ->orderBy('title'),
            $perPage
        );
    }

    public function vendorAttributes(?string $vendorId = null, int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateDocuments(
            VendorAttribute::query()
                ->when($vendorId, fn ($q) => $q->where('vendorId', $vendorId))
                ->orderBy('title'),
            $perPage
        );
    }

    public function vendorCuisines(string $vendorId): Collection
    {
        $products = VendorProduct::query()
            ->where('vendorID', $vendorId)
            ->where('publish', true)
            ->get();

        $categoryIds = $products
            ->pluck('categoryID')
            ->filter()
            ->unique()
            ->values();

        return VendorCategory::query()
            ->whereIn('id', $categoryIds)
            ->get()
            ->map(fn ($c) => $c->toDocumentArray());
    }

    protected function sectionRadiusKm(?string $sectionId): float
    {
        if (! $sectionId) {
            return 50.0;
        }

        $section = Section::query()->find($sectionId);

        return (float) ($section?->nearByRadius ?? 50);
    }

    protected function paginateDocuments(Builder $query, int $perPage = 20): LengthAwarePaginator
    {
        return $query->paginate($perPage)->through(fn ($item) => $item->toDocumentArray());
    }
}
