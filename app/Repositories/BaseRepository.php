<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseRepository
{
    public function __construct(protected Model $model)
    {
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function find(string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(string $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model->fresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15, string $sortBy = 'created_at', string $sortDir = 'desc'): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->query(), $filters);

        $sortColumn = in_array($sortBy, $this->sortableColumns(), true) ? $sortBy : 'created_at';
        $direction = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortColumn, $direction)->paginate(min($perPage, 100));
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($field, $this->filterableColumns(), true)) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        if (! empty($filters['search']) && ! empty($this->searchableColumns())) {
            $search = (string) $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchableColumns() as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        return $query;
    }

    protected function filterableColumns(): array
    {
        return [];
    }

    protected function searchableColumns(): array
    {
        return [];
    }

    protected function sortableColumns(): array
    {
        return ['created_at', 'updated_at', 'createdAt'];
    }
}
