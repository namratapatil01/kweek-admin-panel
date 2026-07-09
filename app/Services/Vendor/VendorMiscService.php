<?php

namespace App\Services\Vendor;

use App\Models\AppNotification;
use App\Models\AppUser;
use App\Models\Brand;
use App\Models\CmsPage;
use App\Models\Document;
use App\Models\DocumentVerify;
use App\Models\VendorAttribute;
use App\Models\VendorCategory;
use App\Models\Zone;
use App\Services\SettingsService;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorMiscService
{
    public function __construct(
        protected SettingsService $settingsService,
        protected FileStorageService $storageService
    ) {
    }

    public function notifications(int $perPage = 20): LengthAwarePaginator
    {
        return AppNotification::query()
            ->where(fn ($q) => $q->where('role', 'vendor')->orWhereNull('role'))
            ->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function terms(): mixed
    {
        return $this->settingsService->get('termsAndConditions', [])
            ?: CmsPage::query()->where('slug', 'terms')->first()?->toDocumentArray();
    }

    public function privacy(): mixed
    {
        return $this->settingsService->get('privacyPolicy', [])
            ?: CmsPage::query()->where('slug', 'privacy')->first()?->toDocumentArray();
    }

    public function catalog(?string $sectionId = null): array
    {
        $categoriesQuery = VendorCategory::query()->where('publish', true);
        if ($sectionId) {
            $categoriesQuery->where('section_id', $sectionId);
        }

        return [
            'categories' => $categoriesQuery->get()->map(fn ($i) => $i->toDocumentArray())->values()->all(),
            'brands' => Brand::query()->get()->map(fn ($i) => $i->toDocumentArray())->values()->all(),
            'attributes' => VendorAttribute::query()->get()->map(fn ($i) => $i->toDocumentArray())->values()->all(),
            'zones' => Zone::query()->where('publish', true)->get()->map(fn ($i) => $i->toDocumentArray())->values()->all(),
        ];
    }

    public function documents(): array
    {
        return Document::query()
            ->where('enable', true)
            ->where(fn ($q) => $q->where('type', 'vendor')->orWhereNull('type'))
            ->get()
            ->map(fn ($i) => $i->toDocumentArray())
            ->values()
            ->all();
    }

    public function getDocumentVerification(string $userId): ?array
    {
        return DocumentVerify::query()->find($userId)?->toDocumentArray();
    }

    public function submitDocuments(AppUser $user, array $documents): array
    {
        $existing = DocumentVerify::query()->find($user->id);
        $data = [
            'id' => $user->id,
            'type' => 'restaurant',
            'documents' => $documents,
            'status' => 'pending',
        ];

        $record = $existing
            ? CatalogEntityWriter::write(new DocumentVerify(), $data, $existing)
            : CatalogEntityWriter::write(new DocumentVerify(), $data);

        return $record->toDocumentArray();
    }

    public function uploadDocumentFile(AppUser $user, $file, string $side = 'front'): array
    {
        $result = $this->storageService->upload($file, 'driverDocument/' . $user->id, 'public');

        return [
            'side' => $side,
            'url' => url($result['url']),
            'path' => $result['path'],
            'mime' => $result['mime_type'],
        ];
    }
}
