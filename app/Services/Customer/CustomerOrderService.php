<?php

namespace App\Services\Customer;

use App\Models\BookedTable;
use App\Models\ParcelOrder;
use App\Models\ProviderOrder;
use App\Models\RentalOrder;
use App\Models\Ride;
use App\Models\VendorOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerOrderService
{
    protected array $orderModels = [
        'vendor' => VendorOrder::class,
        'parcel' => ParcelOrder::class,
        'rental' => RentalOrder::class,
        'ride' => Ride::class,
        'provider' => ProviderOrder::class,
        'dine-in' => BookedTable::class,
    ];

    public function list(string $customerId, string $type, ?string $sectionId = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->queryForType($type)
            ->where('authorID', $customerId)
            ->when($sectionId, fn ($q) => $q->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)->orWhere('sectionId', $sectionId);
            }))
            ->orderByDesc('createdAt');

        return $query->paginate($perPage)->through(fn ($item) => $item->toDocumentArray());
    }

    public function show(string $customerId, string $type, string $id): ?array
    {
        $order = $this->queryForType($type)
            ->where('authorID', $customerId)
            ->find($id);

        return $order?->toDocumentArray();
    }

    public function create(string $customerId, string $type, array $data): array
    {
        $modelClass = $this->orderModels[$type] ?? null;

        if (! $modelClass) {
            throw new \InvalidArgumentException("Unsupported order type: {$type}");
        }

        $data['id'] = $data['id'] ?? (string) Str::uuid();
        $data['authorID'] = $customerId;
        $data['createdAt'] = $data['createdAt'] ?? now();

        if (! isset($data['section_id']) && isset($data['sectionId'])) {
            $data['section_id'] = $data['sectionId'];
        }

        if (! isset($data['sectionId']) && isset($data['section_id'])) {
            $data['sectionId'] = $data['section_id'];
        }

        /** @var Model $order */
        $order = $modelClass::query()->create($data);

        return $order->toDocumentArray();
    }

    public function updateStatus(string $customerId, string $type, string $id, string $status): ?array
    {
        $order = $this->queryForType($type)
            ->where('authorID', $customerId)
            ->find($id);

        if (! $order) {
            return null;
        }

        $order->update(['status' => $status]);

        return $order->fresh()->toDocumentArray();
    }

    protected function queryForType(string $type): Builder
    {
        $modelClass = $this->orderModels[$type] ?? null;

        if (! $modelClass) {
            throw new \InvalidArgumentException("Unsupported order type: {$type}");
        }

        return $modelClass::query();
    }
}
