<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use App\Support\PayloadMapper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EntityCrudService
{
    public function __construct(
        protected BaseRepository $repository,
        protected array $fillableColumns = [],
        protected array $jsonColumns = ['payload'],
    ) {
    }

    public function list(array $filters, int $perPage, string $sortBy, string $sortDir): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage, $sortBy, $sortDir);
    }

    public function show(string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    public function store(array $data): Model
    {
        $attributes = $this->normalizeAttributes($data, true);

        return $this->repository->create($attributes);
    }

    public function update(string $id, array $data): Model
    {
        $model = $this->repository->findOrFail($id);
        $attributes = $this->normalizeAttributes($data, false);

        return $this->repository->update($model, $attributes);
    }

    public function destroy(string $id): void
    {
        $model = $this->repository->findOrFail($id);
        $this->repository->delete($model);
    }

    protected function normalizeAttributes(array $data, bool $isCreate): array
    {
        if ($isCreate && empty($data['id'])) {
            $data['id'] = (string) Str::uuid();
        }

        $mapped = PayloadMapper::map($data, $this->fillableColumns, $this->jsonColumns);

        if (! empty($mapped['overflow'])) {
            $existingPayload = is_array($mapped['attributes']['payload'] ?? null)
                ? $mapped['attributes']['payload']
                : [];
            $mapped['attributes']['payload'] = array_merge($existingPayload, $mapped['overflow']);
        }

        return $mapped['attributes'];
    }
}
