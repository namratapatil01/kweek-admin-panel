<?php

namespace App\Services\Provider;

use App\Models\AppNotification;
use App\Models\AppUser;
use App\Models\CmsPage;
use App\Models\Document;
use App\Models\DocumentVerify;
use App\Services\Provider\ProviderEmailService;
use App\Services\Provider\ProviderNotificationService;
use App\Services\SettingsService;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class ProviderMiscService
{
    public function __construct(
        protected SettingsService $settingsService,
        protected FileStorageService $storageService,
        protected ProviderEmailService $emailService,
        protected ProviderNotificationService $notificationService
    ) {
    }

    public function notifications(int $perPage = 20): LengthAwarePaginator
    {
        return AppNotification::query()
            ->where(function ($q) {
                $q->where('role', 'provider')->orWhereNull('role');
            })
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

    public function documents(): array
    {
        return Document::query()
            ->where('enable', true)
            ->where(function ($q) {
                $q->where('type', 'provider')
                    ->orWhere('type', 'vendor')
                    ->orWhereNull('type');
            })
            ->get()
            ->map(fn ($item) => $item->toDocumentArray())
            ->values()
            ->all();
    }

    public function getDocumentVerification(string $providerId): ?array
    {
        $record = DocumentVerify::query()->find($providerId);

        return $record?->toDocumentArray();
    }

    public function submitDocuments(AppUser $provider, array $documents): array
    {
        $existing = DocumentVerify::query()->find($provider->id);

        $data = [
            'id' => $provider->id,
            'type' => 'provider',
            'documents' => $documents,
            'status' => 'pending',
        ];

        if ($existing) {
            $record = CatalogEntityWriter::write(new DocumentVerify(), $data, $existing);
        } else {
            $record = CatalogEntityWriter::write(new DocumentVerify(), $data);
        }

        $provider->update(['isDocumentVerify' => false]);

        return $record->toDocumentArray();
    }

    public function uploadDocumentFile(AppUser $provider, $file, string $side = 'front'): array
    {
        $result = $this->storageService->upload(
            $file,
            'provider/documents/' . $provider->id,
            'public'
        );

        return [
            'side' => $side,
            'url' => url($result['url']),
            'path' => $result['path'],
            'mime' => $result['mime_type'],
        ];
    }

    public function notificationContent(string $type): array
    {
        return $this->notificationService->notificationContent($type);
    }

    public function emailTemplate(string $type): ?array
    {
        return $this->emailService->emailTemplate($type);
    }
}
