<?php

namespace App\Services\Customer;

use App\Models\Brand;
use App\Models\ProviderCategory;
use App\Models\ProviderService;
use App\Models\Section;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\VendorProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

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
            default => null,
        };

        return $model?->toDocumentArray();
    }

    protected function paginateDocuments(Builder $query, int $perPage = 20): LengthAwarePaginator
    {
        return $query->paginate($perPage)->through(fn ($item) => $item->toDocumentArray());
    }
}
