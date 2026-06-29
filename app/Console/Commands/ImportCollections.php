<?php

namespace App\Console\Commands;

use App\Services\CollectionImporterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class ImportCollections extends Command
{
    protected $signature = 'kweek:import-collections
                            {--file=collections.json : Path to JSON export}
                            {--only= : Comma-separated collection names to import}
                            {--chunk=200 : Batch size per transaction}
                            {--no-truncate : Upsert without truncating tables}';

    protected $description = 'Import collection JSON export into MySQL';

    public function handle(CollectionImporterService $importer): int
    {
        $filePath = $this->resolveFilePath();

        if (! File::exists($filePath)) {
            $this->error("Export file not found: {$filePath}");
            $this->line('Place collections.json in the project root (see docs/DEPLOYMENT.md).');

            return self::FAILURE;
        }

        $this->info('Loading export file...');
        $data = json_decode(File::get($filePath), true);

        if (! is_array($data) || ! isset($data['__collections__'])) {
            $this->error('Invalid export format. Expected root key __collections__.');

            return self::FAILURE;
        }

        $collections = $data['__collections__'];
        $registry = config('kweek_entities', []);
        $only = $this->option('only')
            ? array_map('trim', explode(',', $this->option('only')))
            : null;
        $chunk = max(50, (int) $this->option('chunk'));
        $truncate = ! $this->option('no-truncate');

        $totals = ['imported' => 0, 'failed' => 0];

        foreach ($registry as $collectionName => $meta) {
            if ($only !== null && ! in_array($collectionName, $only, true)) {
                continue;
            }

            $modelClass = $meta['model'] ?? null;
            if (! $modelClass || ! class_exists($modelClass)) {
                $this->warn("Skipping [{$collectionName}] — model missing.");
                continue;
            }

            $documents = $collections[$collectionName] ?? [];
            $count = is_array($documents) ? count($documents) : 0;

            if ($count === 0) {
                $this->line("Skipping [{$collectionName}] — no documents in export.");
                continue;
            }

            $this->info("Importing [{$collectionName}] ({$count} documents)...");

            try {
                $stats = $importer->importCollection(
                    $collectionName,
                    $modelClass,
                    $documents,
                    $chunk,
                    $truncate
                );

                $totals['imported'] += $stats['imported'];
                $totals['failed'] += $stats['failed'];

                $this->line("  ✓ imported={$stats['imported']} failed={$stats['failed']}");
            } catch (Throwable $e) {
                $this->error("  ✗ {$collectionName}: {$e->getMessage()}");
                $totals['failed'] += $count;
            }
        }

        $this->newLine();
        $this->info("Import complete. imported={$totals['imported']} failed={$totals['failed']}");
        $this->line('Failed rows are logged to storage/logs/laravel.log');

        return $totals['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function resolveFilePath(): string
    {
        $file = (string) $this->option('file');

        return str_starts_with($file, DIRECTORY_SEPARATOR)
            ? $file
            : base_path($file);
    }
}
