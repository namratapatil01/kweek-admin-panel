<?php

namespace App\Services\Driver;

use App\Models\AppNotification;
use App\Models\AppUser;
use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\CmsPage;
use App\Models\Document;
use App\Models\DocumentVerify;
use App\Models\DynamicNotification;
use App\Models\ParcelCategory;
use App\Models\ParcelWeight;
use App\Models\RentalPackage;
use App\Models\RentalVehicleType;
use App\Models\Section;
use App\Models\VehicleType;
use App\Models\Vendor;
use App\Models\Zone;
use App\Services\SettingsService;
use App\Services\Storage\FileStorageService;
use App\Support\CatalogEntityWriter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class DriverMiscService
{
    public function __construct(
        protected SettingsService $settingsService,
        protected FileStorageService $storageService
    ) {
    }

    public function notifications(int $perPage = 20): LengthAwarePaginator
    {
        return AppNotification::query()
            ->where(function ($q) {
                $q->where('role', 'driver')->orWhereNull('role');
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

    public function catalog(?string $serviceType = null): array
    {
        $sectionsQuery = Section::query()->where('isActive', true);
        if ($serviceType) {
            $sectionsQuery->where(function ($q) use ($serviceType) {
                $q->where('serviceTypeFlag', $serviceType)
                    ->orWhere('serviceType', $serviceType);
            });
        }

        return [
            'sections' => $sectionsQuery->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'zones' => Zone::query()->where('publish', true)->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'vehicle_types' => VehicleType::query()->where('isActive', true)->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'rental_vehicle_types' => RentalVehicleType::query()->where('isActive', true)->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'car_makes' => CarMake::query()->where('isActive', true)->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'car_models' => CarModel::query()->where('isActive', true)->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'parcel_categories' => ParcelCategory::query()->where('publish', true)->orderBy('set_order')->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'parcel_weights' => ParcelWeight::query()->where('publish', true)->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
            'rental_packages' => RentalPackage::query()->where('isActive', true)->get()->map(fn ($item) => $item->toDocumentArray())->values()->all(),
        ];
    }

    public function vendor(string $vendorId): ?array
    {
        return Vendor::query()->find($vendorId)?->toDocumentArray();
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

    public function markNotificationRead(string $notificationId): ?array
    {
        $notification = AppNotification::query()->find($notificationId);

        if (! $notification) {
            return null;
        }

        $notification->mergePayload(['isRead' => true]);
        $notification->save();

        return $notification->fresh()->toDocumentArray();
    }

    public function documents(?string $serviceType = null): array
    {
        return Document::query()
            ->where('enable', true)
            ->where(function ($q) use ($serviceType) {
                $q->where('type', 'driver')->orWhereNull('type');
                if ($serviceType) {
                    $q->orWhere('type', $serviceType);
                }
            })
            ->get()
            ->map(fn ($item) => $item->toDocumentArray())
            ->values()
            ->all();
    }

    public function getDocumentVerification(string $driverId): ?array
    {
        return DocumentVerify::query()->find($driverId)?->toDocumentArray();
    }

    public function submitDocuments(AppUser $driver, array $documents): array
    {
        $existing = DocumentVerify::query()->find($driver->id);

        $data = [
            'id' => $driver->id,
            'type' => 'driver',
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

    public function uploadDocumentFile(AppUser $driver, $file, string $side = 'front'): array
    {
        $result = $this->storageService->upload(
            $file,
            'driver/documents/' . $driver->id,
            'public'
        );

        return [
            'side' => $side,
            'url' => url($result['url']),
            'path' => $result['path'],
            'mime' => $result['mime_type'],
        ];
    }
}
