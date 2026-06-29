<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\PayloadMapper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * MySQL-backed document store for admin and API collections.
 * Collection names map to tables via config/kweek_entities.php.
 */
class DocumentStoreService
{
    public function upsertDocument(string $collection, string $documentId, array $data): array
    {
        try {
            if ($collection === 'settings') {
                app(SettingsService::class)->put($documentId, $data);

                return ['success' => true];
            }

            $meta = $this->resolveCollection($collection);
            if ($meta === null) {
                return $this->upsertFailure("Unknown collection [{$collection}].");
            }

            if ($meta['model'] !== null) {
                return $this->upsertModelDocument($meta['model'], $documentId, $data);
            }

            return $this->upsertTableDocument($meta['table'], $documentId, $data);
        } catch (\Throwable $e) {
            Log::warning('MySQL document upsert error', [
                'collection' => $collection,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);

            return $this->upsertFailure($e->getMessage());
        }
    }

    public function getDocument(string $collection, string $documentId): ?array
    {
        try {
            if ($collection === 'settings') {
                $setting = Setting::query()->find($documentId);

                return $setting?->toDocumentArray();
            }

            $meta = $this->resolveCollection($collection);
            if ($meta === null) {
                return null;
            }

            if ($meta['model'] !== null) {
                /** @var Model|null $model */
                $model = $meta['model']::query()->find($documentId);

                return $model && method_exists($model, 'toDocumentArray')
                    ? $model->toDocumentArray()
                    : ($model ? $this->rowToDocument($model->attributesToArray()) : null);
            }

            $row = DB::table($meta['table'])->where('id', $documentId)->first();

            return $row ? $this->rowToDocument((array) $row) : null;
        } catch (\Throwable $e) {
            Log::warning('MySQL document fetch error', [
                'collection' => $collection,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<int, array{field: string, op: string, value: mixed}>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function queryDocuments(
        string $collection,
        array $filters,
        int $limit = 10,
        bool $orderByCreatedAt = true
    ): array {
        try {
            if ($collection === 'settings') {
                return $this->querySettings($filters, $limit);
            }

            $meta = $this->resolveCollection($collection);
            if ($meta === null) {
                return [];
            }

            if ($meta['model'] !== null) {
                return $this->queryModelDocuments($meta['model'], $filters, $limit, $orderByCreatedAt);
            }

            return $this->queryTableDocuments($meta['table'], $filters, $limit, $orderByCreatedAt);
        } catch (\Throwable $e) {
            Log::warning('MySQL query error', [
                'collection' => $collection,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function deleteDocument(string $collection, string $documentId): bool
    {
        try {
            if ($collection === 'settings') {
                app(SettingsService::class)->forget($documentId);

                return true;
            }

            $meta = $this->resolveCollection($collection);
            if ($meta === null) {
                return false;
            }

            if ($meta['model'] !== null) {
                return (bool) $meta['model']::query()->where('id', $documentId)->delete();
            }

            return (bool) DB::table($meta['table'])->where('id', $documentId)->delete();
        } catch (\Throwable $e) {
            Log::warning('MySQL document delete error', [
                'collection' => $collection,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

  /**
     * @param  array<int, array{field: string, op: string, value: mixed}>  $filters
     */
    public function queryForBridge(
        string $collection,
        array $filters,
        int $limit = 500,
        ?string $orderBy = null,
        string $orderDir = 'desc',
        ?string $startAt = null,
        ?string $endAt = null
    ): array {
        if ($collection === 'settings') {
            return $this->querySettings($filters, $limit);
        }

        $meta = $this->resolveCollection($collection);
        if ($meta === null) {
            return [];
        }

        $query = $meta['model'] !== null
            ? $meta['model']::query()
            : DB::table($meta['table']);

        $columns = $meta['model'] !== null
            ? Schema::getColumnListing((new $meta['model'])->getTable())
            : Schema::getColumnListing($meta['table']);

        foreach ($filters as $filter) {
            $this->applyFilter($query, $filter, $columns);
        }

        if ($orderBy !== null && in_array($orderBy, $columns, true)) {
            $query->orderBy($orderBy, strtolower($orderDir) === 'asc' ? 'asc' : 'desc');
        } elseif (in_array('created_at', $columns, true)) {
            $query->orderBy('created_at', 'desc');
        } elseif (in_array('createdAt', $columns, true)) {
            $query->orderBy('createdAt', 'desc');
        }

        if ($startAt !== null && $endAt !== null && $orderBy !== null) {
            $query->whereBetween($orderBy, [$startAt, $endAt]);
        }

        $rows = $query->limit(min($limit, 1000))->get();

        return $rows->map(function ($row) use ($meta) {
            if ($meta['model'] !== null && $row instanceof Model) {
                return method_exists($row, 'toDocumentArray')
                    ? $row->toDocumentArray()
                    : $this->rowToDocument($row->attributesToArray());
            }

            return $this->rowToDocument((array) $row);
        })->all();
    }

    protected function upsertModelDocument(string $modelClass, string $documentId, array $data): array
    {
        /** @var Model $prototype */
        $prototype = new $modelClass();
        $fillable = $prototype->getFillable() !== []
            ? $prototype->getFillable()
            : array_diff(Schema::getColumnListing($prototype->getTable()), ['created_at', 'updated_at']);

        $mapped = PayloadMapper::map($data, $fillable, ['payload', 'value', 'options', 'documents']);
        $attributes = $mapped['attributes'];
        $attributes['id'] = $documentId;

        if (! empty($mapped['overflow'])) {
            $existing = is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [];
            $attributes['payload'] = array_merge($existing, $mapped['overflow']);
        }

        $modelClass::query()->updateOrCreate(['id' => $documentId], $attributes);

        return ['success' => true];
    }

    protected function upsertTableDocument(string $table, string $documentId, array $data): array
    {
        $columns = Schema::getColumnListing($table);
        $fillable = array_diff($columns, ['created_at', 'updated_at']);
        $mapped = PayloadMapper::map($data, $fillable, ['payload']);
        $attributes = $mapped['attributes'];
        $attributes['id'] = $documentId;

        if (! empty($mapped['overflow'])) {
            $existing = is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [];
            $attributes['payload'] = array_merge($existing, $mapped['overflow']);
        }

        $attributes['updated_at'] = now();
        if (! DB::table($table)->where('id', $documentId)->exists()) {
            $attributes['created_at'] = now();
        }

        DB::table($table)->updateOrInsert(['id' => $documentId], $attributes);

        return ['success' => true];
    }

    /**
     * @param  array<int, array{field: string, op: string, value: mixed}>  $filters
     */
    protected function queryModelDocuments(
        string $modelClass,
        array $filters,
        int $limit,
        bool $orderByCreatedAt
    ): array {
        /** @var Model $prototype */
        $prototype = new $modelClass();
        $query = $prototype->newQuery();
        $columns = Schema::getColumnListing($prototype->getTable());

        foreach ($filters as $filter) {
            $this->applyFilter($query, $filter, $columns);
        }

        if ($orderByCreatedAt) {
            if (in_array('created_at', $columns, true)) {
                $query->orderByDesc('created_at');
            } elseif (in_array('createdAt', $columns, true)) {
                $query->orderByDesc('createdAt');
            }
        }

        return $query->limit(min($limit, 1000))->get()->map(function (Model $model) {
            return method_exists($model, 'toDocumentArray')
                ? $model->toDocumentArray()
                : $this->rowToDocument($model->attributesToArray());
        })->all();
    }

    /**
     * @param  array<int, array{field: string, op: string, value: mixed}>  $filters
     */
    protected function queryTableDocuments(string $table, array $filters, int $limit, bool $orderByCreatedAt): array
    {
        $query = DB::table($table);
        $columns = Schema::getColumnListing($table);

        foreach ($filters as $filter) {
            $this->applyFilter($query, $filter, $columns);
        }

        if ($orderByCreatedAt && in_array('created_at', $columns, true)) {
            $query->orderByDesc('created_at');
        }

        return $query->limit(min($limit, 1000))->get()->map(function ($row) {
            return $this->rowToDocument((array) $row);
        })->all();
    }

    /**
     * @param  array<int, array{field: string, op: string, value: mixed}>  $filters
     */
    protected function querySettings(array $filters, int $limit): array
    {
        $query = Setting::query();

        foreach ($filters as $filter) {
            $field = (string) ($filter['field'] ?? '');
            $op = strtoupper((string) ($filter['op'] ?? 'EQUAL'));
            $value = $filter['value'] ?? null;

            if ($field === 'id' && in_array($op, ['EQUAL', '=='], true)) {
                $query->where('id', $value);
            }
        }

        return $query->limit(min($limit, 100))->get()->map(
            fn (Setting $setting) => $setting->toDocumentArray()
        )->all();
    }

    protected function applyFilter($query, array $filter, array $columns): void
    {
        $field = (string) ($filter['field'] ?? '');
        $op = strtoupper((string) ($filter['op'] ?? 'EQUAL'));
        $value = $filter['value'] ?? null;

        if ($field === '') {
            return;
        }

        $column = $this->resolveColumn($field, $columns);

        if (str_starts_with($column, 'json:')) {
            $jsonPath = substr($column, 5);
            if (in_array($op, ['IN', 'ARRAY_CONTAINS_ANY'], true)) {
                $query->where(function ($nested) use ($jsonPath, $value) {
                    foreach ((array) $value as $item) {
                        $nested->orWhereRaw(
                            'JSON_UNQUOTE(JSON_EXTRACT(payload, ?)) = ?',
                            [$jsonPath, $item]
                        );
                    }
                });

                return;
            }

            $operator = in_array($op, ['!=', 'NOT_EQUAL'], true) ? '!=' : '=';
            $query->whereRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(payload, ?)) ' . $operator . ' ?',
                [$jsonPath, $value]
            );

            return;
        }

        if (in_array($op, ['EQUAL', '=='], true)) {
            $query->where($column, $value);

            return;
        }

        if (in_array($op, ['IN', 'ARRAY_CONTAINS_ANY'], true)) {
            $query->whereIn($column, (array) $value);

            return;
        }

        if (in_array($op, ['!=', 'NOT_EQUAL'], true)) {
            $query->where($column, '!=', $value);
        }
    }

    protected function resolveColumn(string $field, array $columns): string
    {
        if (in_array($field, $columns, true)) {
            return $field;
        }

        $aliases = [
            'vendor.id' => 'vendorID',
            'driver.id' => 'driverID',
            'driverId' => 'driverID',
            'section_id' => 'sectionId',
        ];

        if (isset($aliases[$field]) && in_array($aliases[$field], $columns, true)) {
            return $aliases[$field];
        }

        if (str_contains($field, '.')) {
            return 'json:$.' . $field;
        }

        return $field;
    }

    protected function rowToDocument(array $row): array
    {
        $payload = $row['payload'] ?? null;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        unset($row['payload'], $row['created_at'], $row['updated_at']);

        if (isset($row['value']) && is_string($row['value'])) {
            $decoded = json_decode($row['value'], true);
            if (is_array($decoded)) {
                $row = array_merge(['id' => $row['id'] ?? null], $decoded);
            }
        }

        $document = is_array($payload) ? array_merge($row, $payload) : $row;
        unset($document['value']);

        return array_filter($document, static fn ($value) => $value !== null);
    }

    protected function resolveCollection(string $collection): ?array
    {
        $entities = config('kweek_entities', []);

        if (isset($entities[$collection])) {
            $meta = $entities[$collection];
            $model = $meta['model'] ?? null;

            return [
                'table' => $meta['table'] ?? null,
                'model' => ($model && class_exists($model)) ? $model : null,
            ];
        }

        $table = Str::snake($collection);
        if (Schema::hasTable($table)) {
            return ['table' => $table, 'model' => null];
        }

        return null;
    }

    protected function upsertFailure(string $message, array $details = []): array
    {
        Log::warning('MySQL document upsert failed', array_merge(['message' => $message], $details));

        return [
            'success' => false,
            'message' => $message,
            'details' => $details ?: null,
        ];
    }
}
