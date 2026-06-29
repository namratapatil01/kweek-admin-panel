<?php

namespace App\Services\Admin;

use App\Support\PayloadMapper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminCrudService
{
    public function __construct(protected Model $model, protected array $config = [])
    {
    }

    public function paginate(array $filters, int $perPage = 15, string $sortBy = 'created_at', string $sortDir = 'desc'): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        foreach ($filters as $field => $value) {
            if ($value === null || $value === '' || in_array($field, ['search', 'per_page', 'sort_by', 'sort_dir', 'page'], true)) {
                continue;
            }

            if ($this->hasColumn($field)) {
                $query->where($field, $value);
            }
        }

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $columns = $this->config['searchable'] ?? ['title', 'name'];

            $query->where(function (Builder $q) use ($search, $columns) {
                foreach ($columns as $column) {
                    if ($this->hasColumn($column)) {
                        $q->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        $sortColumn = $this->hasColumn($sortBy) ? $sortBy : 'created_at';
        $direction = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortColumn, $direction)->paginate(min($perPage, 100));
    }

    public function datatable(array $filters, int $start, int $length, string $sortBy, string $sortDir): array
    {
        $query = $this->baseQuery();

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $columns = $this->config['searchable'] ?? ['title', 'name'];

            $query->where(function (Builder $q) use ($search, $columns) {
                foreach ($columns as $column) {
                    if ($this->hasColumn($column)) {
                        $q->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        foreach ($filters as $field => $value) {
            if ($value === null || $value === '' || in_array($field, ['search'], true)) {
                continue;
            }
            if ($this->hasColumn($field)) {
                $query->where($field, $value);
            }
        }

        $total = (clone $query)->count();
        $sortColumn = $this->hasColumn($sortBy) ? $sortBy : 'created_at';
        $direction = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $items = $query->orderBy($sortColumn, $direction)
            ->skip($start)
            ->take($length > 0 ? $length : 15)
            ->get();

        return ['total' => $total, 'items' => $items];
    }

    public function findOrFail(string $id): Model
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function store(array $data): Model
    {
        $attributes = $this->normalize($data, true);

        return $this->model->newQuery()->create($attributes);
    }

    public function update(string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $attributes = $this->normalize($data, false);
        $record->fill($attributes);
        $record->save();

        return $record->fresh();
    }

    public function destroy(string $id): void
    {
        $this->findOrFail($id)->delete();
    }

    public function bulkDestroy(array $ids): int
    {
        return $this->baseQuery()->whereIn('id', $ids)->delete();
    }

    protected function baseQuery(): Builder
    {
        $query = $this->model->newQuery();

        if (($this->config['scope'] ?? null) === 'customers') {
            $query->where('role', 'customer');
        }

        if (($this->config['scope'] ?? null) === 'vendors') {
            $query->where('role', 'vendor');
        }

        return $query;
    }

    protected function normalize(array $data, bool $isCreate): array
    {
        if ($isCreate && empty($data['id'])) {
            $data['id'] = (string) Str::uuid();
        }

        if (($this->config['scope'] ?? null) === 'customers' && $isCreate) {
            $data['role'] = 'customer';
        }

        $columns = Schema::getColumnListing($this->model->getTable());
        $mapped = PayloadMapper::map($data, $columns, ['payload', 'value', 'options', 'documents']);

        if (! empty($mapped['overflow']) && in_array('payload', $columns, true)) {
            $existing = is_array($mapped['attributes']['payload'] ?? null) ? $mapped['attributes']['payload'] : [];
            $mapped['attributes']['payload'] = array_merge($existing, $mapped['overflow']);
        }

        if (! empty($mapped['attributes']['password'])) {
            $mapped['attributes']['password'] = Hash::make((string) $mapped['attributes']['password']);
        }

        return $mapped['attributes'];
    }

    protected function hasColumn(string $column): bool
    {
        return in_array($column, Schema::getColumnListing($this->model->getTable()), true);
    }
}
