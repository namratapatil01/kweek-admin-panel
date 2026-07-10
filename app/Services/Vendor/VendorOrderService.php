<?php

namespace App\Services\Vendor;

use App\Models\AppUser;
use App\Models\VendorOrder;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorOrderService
{
    public const STATUS_PLACED = 'Order Placed';
    public const STATUS_ACCEPTED = 'Order Accepted';
    public const STATUS_REJECTED = 'Order Rejected';
    public const STATUS_CANCELLED = 'Order Cancelled';
    public const STATUS_DRIVER_PENDING = 'Driver Pending';
    public const STATUS_DRIVER_ACCEPTED = 'Driver Accepted';
    public const STATUS_DRIVER_REJECTED = 'Driver Rejected';
    public const STATUS_SHIPPED = 'Order Shipped';
    public const STATUS_IN_TRANSIT = 'In Transit';
    public const STATUS_COMPLETED = 'Order Completed';

    public function list(AppUser $user, ?string $tab = 'new', int $perPage = 20): LengthAwarePaginator
    {
        $vendorId = $this->requireVendorId($user);
        $query = VendorOrder::query()->where('vendorID', $vendorId);

        match ($tab) {
            'new', 'placed' => $query->where('status', self::STATUS_PLACED),
            'active' => $query->whereIn('status', [
                self::STATUS_ACCEPTED, self::STATUS_DRIVER_PENDING, self::STATUS_DRIVER_ACCEPTED,
                self::STATUS_SHIPPED, self::STATUS_IN_TRANSIT,
            ]),
            'completed', 'history' => $query->where('status', self::STATUS_COMPLETED),
            'cancelled' => $query->whereIn('status', [
                self::STATUS_CANCELLED, self::STATUS_REJECTED, self::STATUS_DRIVER_REJECTED,
            ]),
            default => null,
        };

        return $query->orderByDesc('createdAt')
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(AppUser $user, string $id): ?array
    {
        $order = $this->findOwnedOrder($user, $id);

        return $order?->toDocumentArray();
    }

    public function accept(AppUser $user, string $id, array $data = []): array
    {
        $order = $this->requireOrder($user, $id);

        if ($order->status !== self::STATUS_PLACED) {
            throw ValidationException::withMessages(['status' => ['Only placed orders can be accepted.']]);
        }

        $payload = $order->payload ?? [];
        if (! empty($data['estimatedTimeToPrepare'])) {
            $payload['estimatedTimeToPrepare'] = $data['estimatedTimeToPrepare'];
        }
        if (! empty($data['courierCompanyName'])) {
            $payload['courierCompanyName'] = $data['courierCompanyName'];
            $payload['courierTrackingId'] = $data['courierTrackingId'] ?? null;
        }

        $status = ! empty($data['courierCompanyName']) ? self::STATUS_SHIPPED : self::STATUS_ACCEPTED;

        $order->update([
            'status' => $status,
            'payload' => $payload,
        ]);

        $order = $order->fresh();
        $this->creditWalletOnAccept($user, $order);

        return $order->toDocumentArray();
    }

    public function reject(AppUser $user, string $id, array $data = []): array
    {
        $order = $this->requireOrder($user, $id);
        $payload = $order->payload ?? [];
        $payload['reason'] = $data['reason'] ?? 'Rejected by vendor';

        $order->update(['status' => self::STATUS_REJECTED, 'payload' => $payload]);

        return $order->fresh()->toDocumentArray();
    }

    public function cancel(AppUser $user, string $id, array $data = []): array
    {
        $order = $this->requireOrder($user, $id);
        $payload = $order->payload ?? [];
        $payload['reason'] = $data['reason'] ?? 'Cancelled by vendor';

        $order->update(['status' => self::STATUS_CANCELLED, 'payload' => $payload]);

        return $order->fresh()->toDocumentArray();
    }

    public function complete(AppUser $user, string $id): array
    {
        $order = $this->requireOrder($user, $id);

        if (! in_array($order->status, [
            self::STATUS_ACCEPTED, self::STATUS_DRIVER_ACCEPTED,
            self::STATUS_SHIPPED, self::STATUS_IN_TRANSIT,
        ], true)) {
            throw ValidationException::withMessages(['status' => ['Order cannot be completed in current status.']]);
        }

        $order->update([
            'status' => self::STATUS_COMPLETED,
            'paymentStatus' => $order->paymentStatus ?: 'success',
        ]);

        return $order->fresh()->toDocumentArray();
    }

    public function assignDriver(AppUser $user, string $id, string $driverId): array
    {
        $order = $this->requireOrder($user, $id);
        $vendorId = $this->requireVendorId($user);

        $driver = AppUser::query()
            ->where('id', $driverId)
            ->where('role', 'driver')
            ->where('vendorID', $vendorId)
            ->first();

        if (! $driver) {
            throw ValidationException::withMessages(['driverId' => ['Driver not found for this store.']]);
        }

        $order->update([
            'driverID' => $driverId,
            'status' => self::STATUS_IN_TRANSIT,
        ]);

        $driver->mergePayload([
            'inProgressOrderID' => array_values(array_unique(array_merge(
                (array) ($driver->payload['inProgressOrderID'] ?? []),
                [$order->id]
            ))),
        ]);
        $driver->save();

        return $order->fresh()->toDocumentArray();
    }

    public function ship(AppUser $user, string $id, array $data): array
    {
        $order = $this->requireOrder($user, $id);
        $payload = $order->payload ?? [];
        $payload['courierCompanyName'] = $data['courierCompanyName'] ?? $data['courierCompany'] ?? null;
        $payload['courierTrackingId'] = $data['courierTrackingId'] ?? $data['trackingId'] ?? null;

        $order->update(['status' => self::STATUS_SHIPPED, 'payload' => $payload]);

        return $order->fresh()->toDocumentArray();
    }

    public function update(AppUser $user, string $id, array $data): array
    {
        $order = $this->requireOrder($user, $id);
        $allowed = ['status', 'driverID', 'paymentStatus', 'notes'];
        $update = array_intersect_key($data, array_flip($allowed));
        $payload = array_merge($order->payload ?? [], $data['payload'] ?? []);

        if (isset($data['courierCompanyName']) || isset($data['courierTrackingId'])) {
            $payload['courierCompanyName'] = $data['courierCompanyName'] ?? $payload['courierCompanyName'] ?? null;
            $payload['courierTrackingId'] = $data['courierTrackingId'] ?? $payload['courierTrackingId'] ?? null;
        }

        $order->update(array_merge($update, ['payload' => $payload]));

        return $order->fresh()->toDocumentArray();
    }

    protected function creditWalletOnAccept(AppUser $user, VendorOrder $order): void
    {
        $doc = $order->toDocumentArray();
        $subTotal = (float) ($doc['subTotal'] ?? $order->subTotal ?? 0);
        $discount = (float) ($doc['discount'] ?? 0);
        $specialDiscount = (float) data_get($doc, 'specialDiscount.special_discount', 0);

        $amount = max(0, $subTotal - $discount - $specialDiscount);
        if ($amount <= 0) {
            return;
        }

        $user->increment('wallet_amount', $amount);

        Wallet::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'amount' => $amount,
            'isTopUp' => true,
            'payment_method' => 'Wallet',
            'payment_status' => 'success',
            'note' => 'Order Amount credited',
            'order_id' => $order->id,
            'transactionUser' => 'vendor',
            'date' => now(),
        ]);
    }

    protected function requireVendorId(AppUser $user): string
    {
        if (! $user->vendorID) {
            throw ValidationException::withMessages(['vendorID' => ['Store not set up yet.']]);
        }

        return $user->vendorID;
    }

    protected function findOwnedOrder(AppUser $user, string $id): ?VendorOrder
    {
        if (! $user->vendorID) {
            return null;
        }

        return VendorOrder::query()->where('id', $id)->where('vendorID', $user->vendorID)->first();
    }

    protected function requireOrder(AppUser $user, string $id): VendorOrder
    {
        $order = $this->findOwnedOrder($user, $id);
        if (! $order) {
            throw ValidationException::withMessages(['id' => ['Order not found.']]);
        }

        return $order;
    }
}
