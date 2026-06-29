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
