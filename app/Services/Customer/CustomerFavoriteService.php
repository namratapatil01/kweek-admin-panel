<?php

namespace App\Services\Customer;

use App\Models\FavoriteItem;
use App\Models\FavoriteProvider;
use App\Models\FavoriteService;
use App\Models\FavoriteVendor;
use Illuminate\Support\Str;

class CustomerFavoriteService
{
    protected array $models = [
        'vendor' => FavoriteVendor::class,
        'item' => FavoriteItem::class,
        'service' => FavoriteService::class,
        'provider' => FavoriteProvider::class,
    ];

    public function list(string $customerId, string $type, ?string $sectionId = null, int $perPage = 20)
    {
        $modelClass = $this->models[$type];

        return $modelClass::query()
            ->where('user_id', $customerId)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->paginate($perPage)
            ->through(fn ($item) => $item->toDocumentArray());
    }

    public function add(string $customerId, string $type, array $data): array
    {
        $modelClass = $this->models[$type];

        $payload = array_merge($data, [
            'id' => $data['id'] ?? (string) Str::uuid(),
            'user_id' => $customerId,
        ]);

        $favorite = $modelClass::query()->create($payload);

        return $favorite->toDocumentArray();
    }

    public function remove(string $customerId, string $type, string $id): bool
    {
        $modelClass = $this->models[$type];

        return (bool) $modelClass::query()
            ->where('user_id', $customerId)
            ->where('id', $id)
            ->delete();
    }
}
