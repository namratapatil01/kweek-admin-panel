<?php

namespace App\Services\Driver;

use App\Models\AppUser;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\Ride;
use App\Models\VendorOrder;
use App\Services\Notifications\FcmNotificationService;
use App\Services\SettingsService;
use App\Support\GeoQuery;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorInstance;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DriverOrderService
{
    public function __construct(
        protected DriverOrderCompletionService $completionService,
        protected FcmNotificationService $fcmService,
        protected SettingsService $settingsService
    ) {
    }
    public const STATUS_PLACED = 'Order Placed';
    public const STATUS_ACCEPTED = 'Order Accepted';
    public const STATUS_REJECTED = 'Order Rejected';
    public const STATUS_DRIVER_PENDING = 'Driver Pending';
    public const STATUS_DRIVER_ACCEPTED = 'Driver Accepted';
    public const STATUS_DRIVER_REJECTED = 'Driver Rejected';
    public const STATUS_SHIPPED = 'Order Shipped';
    public const STATUS_IN_TRANSIT = 'In Transit';
    public const STATUS_COMPLETED = 'Order Completed';
    public const STATUS_CANCELLED = 'Order Cancelled';

    protected array $models = [
        'vendor' => VendorOrder::class,
        'ride' => Ride::class,
        'parcel' => ParcelOrder::class,
        'rental' => RentalOrder::class,
    ];

    public function list(AppUser $driver, ?string $type = null, ?string $tab = 'active', int $perPage = 20): LengthAwarePaginator
    {
        $type = $type ?: $this->resolveOrderType($driver);
        $statuses = $this->statusesForTab($tab, $type);

        if ($tab === 'available') {
            $query = $this->availableQuery($driver, $type, $statuses);
        } else {
            $query = $this->queryForDriver($driver, $type)->whereIn('status', $statuses);
        }

        return $query->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(AppUser $driver, string $type, string $id): ?array
    {
        $order = $this->findOrder($driver, $type, $id);

        return $order?->toDocumentArray();
    }

    public function accept(AppUser $driver, string $type, string $id, array $data = []): array
    {
        $order = $this->requireOrder($driver, $type, $id, allowAvailable: true);

        $allowedStatuses = [self::STATUS_DRIVER_PENDING, self::STATUS_ACCEPTED, self::STATUS_PLACED];
        if (! in_array($order->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages(['status' => ['Order cannot be accepted in current status.']]);
        }

        $this->assertWalletMinimum($driver);

        $updates = [
            'status' => self::STATUS_DRIVER_ACCEPTED,
        ];
        $this->setDriverId($type, $updates, $driver->id);

        $payload = $order->payload ?? [];
        $rejected = $payload['rejectedByDrivers'] ?? $order->rejectedByDrivers ?? [];
        if (! is_array($rejected)) {
            $rejected = [];
        }

        $order->update(array_merge($updates, [
            'payload' => array_merge($payload, ['rejectedByDrivers' => $rejected]),
        ]));

        $this->trackDriverOrder($driver, $id, accept: true);

        $this->fcmService->send(
            data_get($order->fresh()->toDocumentArray(), 'author.fcmToken'),
            'Driver accepted',
            'A driver accepted your order',
            ['type' => $type . '_order', 'orderId' => $id]
        );

        return $order->fresh()->toDocumentArray();
    }

    public function reject(AppUser $driver, string $type, string $id, array $data = []): array
    {
        $order = $this->requireOrder($driver, $type, $id, allowAvailable: true);

        $payload = $order->payload ?? [];
        $rejected = $payload['rejectedByDrivers'] ?? $order->rejectedByDrivers ?? [];
        if (! is_array($rejected)) {
            $rejected = [];
        }
        $rejected[] = $driver->id;

        $updates = ['status' => self::STATUS_DRIVER_REJECTED];
        if ($this->driverAssigned($order, $driver->id)) {
            $updates = ['status' => self::STATUS_ACCEPTED];
            $this->clearDriverId($type, $updates);
        }

        $order->update(array_merge($updates, [
            'payload' => array_merge($payload, [
                'rejectedByDrivers' => array_values(array_unique($rejected)),
                'reason' => $data['reason'] ?? 'Rejected by driver',
            ]),
        ]));

        $this->trackDriverOrder($driver, $id, accept: false);

        return $order->fresh()->toDocumentArray();
    }

    public function start(AppUser $driver, string $type, string $id, ?string $otp = null, array $extra = []): array
    {
        $order = $this->requireOrder($driver, $type, $id);

        if (! in_array($order->status, [self::STATUS_DRIVER_ACCEPTED, self::STATUS_SHIPPED], true)) {
            throw ValidationException::withMessages(['status' => ['Order cannot be started in current status.']]);
        }

        $payload = $order->payload ?? [];
        $orderOtp = (string) ($payload['otpCode'] ?? $payload['otp'] ?? $order->otpCode ?? '');
        if ($orderOtp !== '' && in_array($type, ['ride', 'rental'], true) && (string) $otp !== $orderOtp) {
            throw ValidationException::withMessages(['otp' => ['Invalid OTP.']]);
        }

        $payload['startTime'] = $payload['startTime'] ?? now()->toIso8601String();

        if ($type === 'rental' && isset($extra['startKilometerReading'])) {
            $payload['startKitoMetersReading'] = $extra['startKilometerReading'];
        }

        $order->update([
            'status' => self::STATUS_IN_TRANSIT,
            'payload' => $payload,
        ]);

        $this->fcmService->send(
            data_get($order->fresh()->toDocumentArray(), 'author.fcmToken'),
            'Trip started',
            'Your driver has started the trip',
            ['type' => $type . '_order', 'orderId' => $id]
        );

        return $order->fresh()->toDocumentArray();
    }

    public function complete(AppUser $driver, string $type, string $id, ?string $otp = null, array $extra = []): array
    {
        $order = $this->requireOrder($driver, $type, $id);

        if (! in_array($order->status, [self::STATUS_IN_TRANSIT, self::STATUS_DRIVER_ACCEPTED, self::STATUS_SHIPPED], true)) {
            throw ValidationException::withMessages(['status' => ['Order cannot be completed in current status.']]);
        }

        $payload = $order->payload ?? [];
        $payload['endTime'] = now()->toIso8601String();

        if ($type === 'rental' && isset($extra['endKilometerReading'])) {
            $payload['endKitoMetersReading'] = $extra['endKilometerReading'];
        }

        $order->update([
            'status' => self::STATUS_COMPLETED,
            'paymentStatus' => $order->paymentStatus ?: 'success',
            'payload' => $payload,
        ]);

        $order = $order->fresh();
        $this->completionService->afterComplete($driver, $order, $type, $extra);
        $this->trackDriverOrder($driver, $id, complete: true);

        return $order->toDocumentArray();
    }

    public function updateStatus(AppUser $driver, string $type, string $id, string $status, array $extra = []): array
    {
        return match ($status) {
            self::STATUS_DRIVER_ACCEPTED => $this->accept($driver, $type, $id, $extra),
            self::STATUS_DRIVER_REJECTED => $this->reject($driver, $type, $id, $extra),
            self::STATUS_IN_TRANSIT => $this->start($driver, $type, $id, $extra['otp'] ?? null, $extra),
            self::STATUS_COMPLETED => $this->complete($driver, $type, $id, $extra['otp'] ?? null, $extra),
            default => throw ValidationException::withMessages(['status' => ['Unsupported status transition.']]),
        };
    }

    public function queryForDriver(AppUser $driver, string $type): Builder
    {
        $model = $this->models[$type] ?? null;
        if (! $model) {
            throw new \InvalidArgumentException("Unsupported order type: {$type}");
        }

        $driverId = $driver->id;

        return $model::query()->where(function ($q) use ($driverId, $type) {
            if ($type === 'vendor') {
                $q->where('driverID', $driverId);
            } else {
                $q->where('driverId', $driverId)->orWhere('driverID', $driverId);
            }
        });
    }

    public function statusesForTab(string $tab, string $type): array
    {
        return match ($tab) {
            'pending' => [self::STATUS_DRIVER_PENDING, self::STATUS_ACCEPTED],
            'active' => [self::STATUS_DRIVER_ACCEPTED, self::STATUS_SHIPPED, self::STATUS_IN_TRANSIT],
            'completed', 'history' => [self::STATUS_COMPLETED],
            'cancelled' => [self::STATUS_CANCELLED, self::STATUS_REJECTED, self::STATUS_DRIVER_REJECTED],
            'available' => [self::STATUS_PLACED, self::STATUS_ACCEPTED],
            default => [self::STATUS_DRIVER_ACCEPTED, self::STATUS_IN_TRANSIT],
        };
    }

    protected function resolveOrderType(AppUser $driver): string
    {
        return match ($driver->serviceType) {
            'cab-service' => 'ride',
            'parcel_delivery' => 'parcel',
            'rental-service' => 'rental',
            default => 'vendor',
        };
    }

    protected function findOrder(AppUser $driver, string $type, string $id): ?Model
    {
        $model = $this->models[$type] ?? null;
        if (! $model) {
            return null;
        }

        return $model::query()->where('id', $id)->first();
    }

    public function availableQuery(AppUser $driver, string $type, array $statuses): Builder
    {
        $model = $this->models[$type];

        return $model::query()
            ->whereIn('status', $statuses)
            ->where(function ($q) use ($type) {
                if ($type === 'vendor') {
                    $q->whereNull('driverID')->orWhere('driverID', '');
                } else {
                    $q->whereNull('driverId')->orWhere('driverId', '');
                }
            })
            ->where(function ($q) use ($driver) {
                $q->whereNull('payload->rejectedByDrivers')
                    ->orWhereJsonDoesntContain('payload->rejectedByDrivers', $driver->id);
            });
    }

    protected function requireOrder(AppUser $driver, string $type, string $id, bool $allowAvailable = false): Model
    {
        $order = $this->findOrder($driver, $type, $id);

        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Order not found.']]);
        }

        if (! $allowAvailable && ! $this->driverAssigned($order, $driver->id)) {
            throw ValidationException::withMessages(['id' => ['Order is not assigned to this driver.']]);
        }

        return $order;
    }

    protected function driverAssigned(Model $order, string $driverId): bool
    {
        return ($order->driverID ?? null) === $driverId
            || ($order->driverId ?? null) === $driverId;
    }

    protected function setDriverId(string $type, array &$updates, string $driverId): void
    {
        if ($type === 'vendor') {
            $updates['driverID'] = $driverId;
        } else {
            $updates['driverId'] = $driverId;
        }
    }

    protected function clearDriverId(string $type, array &$updates): void
    {
        if ($type === 'vendor') {
            $updates['driverID'] = null;
        } else {
            $updates['driverId'] = null;
        }
    }

    public function searchParcel(AppUser $driver, array $filters): LengthAwarePaginator
    {
        $statuses = $this->statusesForTab('available', 'parcel');
        $query = $this->availableQuery($driver, 'parcel', $statuses)->orderByDesc('createdAt');

        if (! empty($filters['zoneId'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('payload->senderZoneId', $filters['zoneId'])
                    ->orWhere('payload->receiverZoneId', $filters['zoneId']);
            });
        }

        $items = $query->get()->map(fn ($item) => $item->toDocumentArray());

        if (isset($filters['latitude'], $filters['longitude'])) {
            $radius = (float) ($filters['radius'] ?? $this->settingsService->get('DriverNearBy', [])['parcelRadius'] ?? 50);
            $items = GeoQuery::filterByRadius($items, (float) $filters['latitude'], (float) $filters['longitude'], $radius);
        }

        return $this->paginateCollection($items, (int) ($filters['per_page'] ?? 20), (int) ($filters['page'] ?? 1));
    }

    public function searchRental(AppUser $driver, array $filters): LengthAwarePaginator
    {
        $statuses = $this->statusesForTab('available', 'rental');
        $query = $this->availableQuery($driver, 'rental', $statuses)->orderByDesc('createdAt');

        if (! empty($filters['sectionId'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('sectionId', $filters['sectionId'])
                    ->orWhere('section_id', $filters['sectionId']);
            });
        }

        if (! empty($filters['vehicleType'])) {
            $query->where('vehicleId', $filters['vehicleType']);
        }

        if (! empty($filters['zoneId'])) {
            $query->where('payload->zoneId', $filters['zoneId']);
        }

        $items = $query->get()->map(fn ($item) => $item->toDocumentArray());

        if (isset($filters['latitude'], $filters['longitude'])) {
            $radius = (float) ($filters['radius'] ?? $this->settingsService->get('DriverNearBy', [])['rentalRadius'] ?? 50);
            $items = GeoQuery::filterByRadius($items, (float) $filters['latitude'], (float) $filters['longitude'], $radius);
        }

        return $this->paginateCollection($items, (int) ($filters['per_page'] ?? 20), (int) ($filters['page'] ?? 1));
    }

    public function stream(AppUser $driver, array $filters): array
    {
        $type = $filters['type'] ?? $this->resolveOrderType($driver);
        $since = ! empty($filters['since']) ? Carbon::parse($filters['since']) : null;
        $ids = ! empty($filters['ids']) ? array_filter(explode(',', (string) $filters['ids'])) : [];

        $orders = collect();

        if ($ids !== []) {
            $model = $this->models[$type];
            $orders = $model::query()->whereIn('id', $ids)->get()->map(fn ($item) => $item->toDocumentArray());
        } else {
            $active = $this->list($driver, $type, 'active', 50);
            $available = $this->list($driver, $type, 'available', 50);
            $orders = collect($active->items())->merge($available->items());
        }

        if ($since) {
            $orders = $orders->filter(function (array $order) use ($since) {
                $updated = $order['updated_at'] ?? $order['createdAt'] ?? null;

                return $updated && Carbon::parse($updated)->greaterThan($since);
            })->values();
        }

        return [
            'driver' => $driver->fresh()->toDocumentArray(),
            'orders' => $orders->values()->all(),
            'serverTime' => now()->toIso8601String(),
        ];
    }

    protected function paginateCollection(Collection $items, int $perPage, int $page): LengthAwarePaginator
    {
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new PaginatorInstance($results, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    protected function assertWalletMinimum(AppUser $driver): void
    {
        $settings = $this->settingsService->get('DriverNearBy', []);
        $minimum = (float) ($driver->isOwner
            ? ($settings['ownerMinimumDepositToRideAccept'] ?? 0)
            : ($settings['minimumDepositToRideAccept'] ?? 0));

        if ($minimum > 0 && (float) ($driver->wallet_amount ?? 0) < $minimum) {
            throw ValidationException::withMessages([
                'wallet_amount' => ['Insufficient wallet balance to accept this order.'],
            ]);
        }
    }

    protected function trackDriverOrder(AppUser $driver, string $orderId, bool $accept = false, bool $complete = false): void
    {
        $payload = $driver->payload ?? [];
        $requests = $payload['orderRequestData'] ?? [];
        $inProgress = $payload['inProgressOrderID'] ?? [];

        if (! is_array($requests)) {
            $requests = [];
        }
        if (! is_array($inProgress)) {
            $inProgress = [];
        }

        if ($accept) {
            $requests = array_values(array_diff($requests, [$orderId]));
            if (! in_array($orderId, $inProgress, true)) {
                $inProgress[] = $orderId;
            }
        }

        if ($complete) {
            $inProgress = array_values(array_diff($inProgress, [$orderId]));
        }

        $driver->mergePayload([
            'orderRequestData' => $requests,
            'inProgressOrderID' => $inProgress,
        ]);
        $driver->save();
    }
}
