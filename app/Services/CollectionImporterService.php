<?php

namespace App\Services;

use App\Models\ChatThread;
use App\Models\Setting;
use App\Support\PayloadMapper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CollectionImporterService
{
    /** @var array<string, array<int, string>> */
    protected array $columnCache = [];

  public function importCollection(
        string $collectionName,
        string $modelClass,
        array $documents,
        int $chunkSize = 200,
        bool $truncate = true
    ): array {
        $stats = ['imported' => 0, 'failed' => 0, 'skipped' => 0];

        if ($documents === []) {
            return $stats;
        }

        /** @var Model $model */
        $model = new $modelClass();
        $table = $model->getTable();

        if ($truncate) {
            DB::table($table)->truncate();
        }

        $chunks = array_chunk($documents, $chunkSize, true);

        foreach ($chunks as $chunk) {
            DB::beginTransaction();

            try {
                foreach ($chunk as $docId => $doc) {
                    try {
                        $this->importDocument($modelClass, $docId, $doc);
                        $stats['imported']++;
                    } catch (Throwable $e) {
                        $stats['failed']++;
                        $this->logFailure($collectionName, (string) $docId, $e);
                    }
                }

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return $stats;
    }

    public function importDocument(string $modelClass, string $docId, array $doc): Model
    {
        unset($doc['__collections__']);

        $doc['id'] = $doc['id'] ?? $docId;
        $attributes = $this->mapDocument($modelClass, $doc);

        /** @var Model $model */
        $model = $modelClass::query()->updateOrCreate(
            ['id' => $attributes['id']],
            $attributes
        );

        $this->importSubcollections($docId, $doc);

        return $model;
    }

    protected function mapDocument(string $modelClass, array $doc): array
    {
        /** @var Model $model */
        $model = new $modelClass();
        $columns = $this->tableColumns($model->getTable());
        $jsonColumns = $this->jsonColumns($model);
        $attributes = [];
        $overflow = [];

        if ($modelClass === Setting::class) {
            $id = $doc['id'];
            unset($doc['id']);

            return [
                'id' => $id,
                'value' => $this->normalizeValue($doc),
            ];
        }

        foreach ($doc as $key => $value) {
            if ($key === '__collections__') {
                continue;
            }

            $normalized = $this->normalizeValue($value);

            if (in_array($key, $columns, true)) {
                if (is_array($normalized) && ! in_array($key, $jsonColumns, true)) {
                    $overflow[$key] = $normalized;

                    continue;
                }

                $attributes[$key] = $normalized;
            } else {
                $overflow[$key] = $normalized;
            }
        }

        if (in_array('payload', $columns, true) && $overflow !== []) {
            $existing = is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [];
            $attributes['payload'] = array_merge($existing, $overflow);
        }

        $this->applyGeoFields($attributes, $columns);
        $attributes = $this->sanitizeAttributes($attributes, $model->getTable());

        return $attributes;
    }

    /** @return array<int, string> */
    protected function jsonColumns(Model $model): array
    {
        $jsonColumns = ['payload', 'value'];

        foreach ($model->getCasts() as $column => $cast) {
            if (in_array($cast, ['array', 'json', 'object', 'collection'], true)) {
                $jsonColumns[] = $column;
            }
        }

        return array_values(array_unique($jsonColumns));
    }

    protected function sanitizeAttributes(array $attributes, string $table): array
    {
        static $columnMeta = [];

        if (! isset($columnMeta[$table])) {
            $columnMeta[$table] = $this->loadColumnMeta($table);
        }

        $overflow = is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [];

        foreach ($attributes as $key => $value) {
            if ($key === 'payload') {
                continue;
            }

            $meta = $columnMeta[$table][$key] ?? null;
            if ($meta === null) {
                continue;
            }

            if ($value === '' || $value === []) {
                $value = null;
            }

            $type = $meta['type'];

            if ($value === null) {
                if (! $meta['nullable']) {
                    if ($type === 'tinyint' && str_contains((string) $columnMeta[$table][$key]['type'] ?? '', 'bool')) {
                        $attributes[$key] = false;
                    } elseif ($type === 'tinyint') {
                        $attributes[$key] = 0;
                    } elseif (in_array($type, ['int', 'bigint', 'smallint', 'decimal', 'float', 'double'], true)) {
                        $attributes[$key] = 0;
                    } elseif ($meta['default'] !== null) {
                        $attributes[$key] = $meta['default'];
                    } else {
                        $attributes[$key] = in_array($type, ['varchar', 'text', 'longtext', 'mediumtext'], true) ? '' : false;
                    }
                } else {
                    $attributes[$key] = null;
                }

                continue;
            }

            if (in_array($type, ['int', 'bigint', 'smallint', 'tinyint'], true)) {
                if (is_bool($value)) {
                    $attributes[$key] = $value ? 1 : 0;
                } else {
                    $attributes[$key] = is_numeric($value) ? (int) $value : 0;
                }

                continue;
            }

            if (in_array($type, ['decimal', 'float', 'double'], true)) {
                $attributes[$key] = is_numeric($value) ? $value : 0;

                continue;
            }

            if ($type === 'tinyint' && is_bool($value)) {
                $attributes[$key] = $value;

                continue;
            }

            if (is_string($value) && $meta['length'] !== null && strlen($value) > $meta['length']) {
                $overflow[$key] = $value;
                $attributes[$key] = null;

                continue;
            }

            if (is_array($value) && $type !== 'json') {
                $overflow[$key] = $value;
                unset($attributes[$key]);
            }
        }

        if ($overflow !== []) {
            $existing = is_array($attributes['payload'] ?? null) ? $attributes['payload'] : [];
            $attributes['payload'] = array_merge($existing, $overflow);
        }

        return $attributes;
    }

    protected function applyGeoFields(array &$attributes, array $columns): void
    {
        $location = $attributes['location'] ?? null;

        if (! is_array($location)) {
            return;
        }

        if (in_array('latitude', $columns, true) && empty($attributes['latitude'])) {
            $attributes['latitude'] = $location['latitude'] ?? $location['_latitude'] ?? null;
        }

        if (in_array('longitude', $columns, true) && empty($attributes['longitude'])) {
            $attributes['longitude'] = $location['longitude'] ?? $location['_longitude'] ?? null;
        }
    }

    /** @return array<string, array{type: string, length: ?int, nullable: bool, default: mixed}> */
    protected function loadColumnMeta(string $table): array
    {
        $meta = [];

        if (DB::connection()->getDriverName() === 'sqlite') {
            foreach (DB::select("PRAGMA table_info('{$table}')") as $column) {
                $type = strtolower((string) $column->type);
                $meta[$column->name] = [
                    'type' => preg_replace('/\(.*/', '', $type),
                    'length' => null,
                    'nullable' => (int) $column->notnull === 0,
                    'default' => $column->dflt_value,
                ];
            }

            return $meta;
        }

        foreach (DB::select('SHOW COLUMNS FROM `'.$table.'`') as $column) {
            $type = strtolower((string) $column->Type);
            $length = null;
            if (preg_match('/varchar\((\d+)\)/', $type, $matches)) {
                $length = (int) $matches[1];
            }
            $meta[$column->Field] = [
                'type' => preg_replace('/\(.*/', '', $type),
                'length' => $length,
                'nullable' => strtoupper((string) $column->Null) === 'YES',
                'default' => $column->Default,
            ];
        }

        return $meta;
    }

    protected function importSubcollections(string $parentId, array $doc): void
    {
        $subcollections = $doc['__collections__'] ?? [];
        if ($subcollections === []) {
            return;
        }

        foreach ($subcollections as $subName => $messages) {
            if ($subName !== 'thread' || ! is_array($messages)) {
                continue;
            }

            foreach ($messages as $messageId => $messageDoc) {
                try {
                    $attributes = $this->mapDocument(ChatThread::class, array_merge($messageDoc, [
                        'id' => $messageDoc['id'] ?? $messageId,
                        'chat_id' => $parentId,
                        'chat_type' => 'chat_admin',
                    ]));

                    ChatThread::query()->updateOrCreate(
                        ['id' => $attributes['id']],
                        $attributes
                    );
                } catch (Throwable $e) {
                    $this->logFailure('chat_threads', (string) $messageId, $e);
                }
            }
        }
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            if (($value['__datatype__'] ?? null) === 'timestamp') {
                return PayloadMapper::parseTimestamp($value);
            }

            if (($value['__datatype__'] ?? null) === 'geopoint') {
                return [
                    'latitude' => $value['value']['_latitude'] ?? null,
                    'longitude' => $value['value']['_longitude'] ?? null,
                ];
            }

            if (isset($value['_latitude'], $value['_longitude'])) {
                return [
                    'latitude' => $value['_latitude'],
                    'longitude' => $value['_longitude'],
                ];
            }

            if (isset($value['_seconds'])) {
                return PayloadMapper::parseTimestamp($value);
            }

            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        return $value;
    }

    /** @return array<int, string> */
    protected function tableColumns(string $table): array
    {
        if (! isset($this->columnCache[$table])) {
            $this->columnCache[$table] = Schema::getColumnListing($table);
        }

        return $this->columnCache[$table];
    }

    protected function logFailure(string $collection, string $docId, Throwable $e): void
    {
        Log::channel('single')->error('Collection import failed', [
            'collection' => $collection,
            'document_id' => $docId,
            'message' => $e->getMessage(),
        ]);
    }
}
