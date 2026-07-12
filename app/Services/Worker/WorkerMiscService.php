<?php

namespace App\Services\Worker;

use App\Models\AppNotification;
use App\Models\AppUser;
use App\Models\CmsPage;
use App\Models\Document;
use App\Models\DocumentVerify;
use App\Models\DynamicNotification;
use App\Models\ProviderOrder;
use App\Services\SettingsService;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkerMiscService
{
    public function __construct(
        protected WorkerProfileService $profileService,
        protected SettingsService $settingsService,
        protected FileStorageService $storageService
    ) {
    }

    public function notifications(int $perPage = 20): LengthAwarePaginator
    {
        return AppNotification::query()
            ->where(function ($q) {
                $q->where('role', 'worker')->orWhereNull('role');
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

    /**
     * Flutter has no worker wallet UI — expose completed-job summary only.
     */
    public function earningsSummary(AppUser $user): array
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $doc = $worker->toDocumentArray();

        $completedCount = ProviderOrder::query()
            ->where('workerId', $worker->id)
            ->where('status', 'Order Completed')
            ->count();

        return [
            'salary' => $doc['salary'] ?? null,
            'completed_jobs' => $completedCount,
            'reviewsCount' => (int) ($doc['reviewsCount'] ?? 0),
            'reviewsSum' => (float) ($doc['reviewsSum'] ?? 0),
            'note' => 'Job payments credit the parent provider wallet, not the worker.',
        ];
    }

    public function documents(): array
    {
        return Document::query()
            ->where('enable', true)
            ->where(function ($q) {
                $q->where('type', 'worker')
                    ->orWhere('type', 'driver')
                    ->orWhereNull('type');
            })
            ->get()
            ->map(fn ($item) => $item->toDocumentArray())
            ->values()
            ->all();
    }

    public function getDocumentVerification(string $workerId): ?array
    {
        return DocumentVerify::query()->find($workerId)?->toDocumentArray();
    }

    public function submitDocuments(AppUser $user, array $documents): array
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $existing = DocumentVerify::query()->find($worker->id);

        $data = [
            'id' => $worker->id,
            'type' => 'worker',
            'documents' => $documents,
            'status' => 'pending',
        ];

        if ($existing) {
            $record = CatalogEntityWriter::write(new DocumentVerify(), $data, $existing);
        } else {
            $record = CatalogEntityWriter::write(new DocumentVerify(), $data);
        }

        return $record->toDocumentArray();
    }

    public function uploadDocumentFile(AppUser $user, $file, string $side = 'front'): array
    {
        $worker = $this->profileService->getWorkerOrFail($user);
        $result = $this->storageService->upload(
            $file,
            'worker/documents/' . $worker->id,
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
        $notification = DynamicNotification::query()->where('type', $type)->first();

        if ($notification) {
            return $notification->toDocumentArray();
        }

        return [
            'id' => '',
            'type' => $type,
            'subject' => 'setup notification',
            'message' => 'Notification setup is pending',
        ];
    }
}
