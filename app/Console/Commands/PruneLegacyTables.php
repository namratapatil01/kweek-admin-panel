<?php

namespace App\Console\Commands;

use App\Support\LegacyTableRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PruneLegacyTables extends Command
{
    protected $signature = 'kweek:prune-legacy-tables
                            {--dry-run : List tables that would be dropped without modifying the database}';

    protected $description = 'Drop obsolete application tables and legacy fs_* Firebase staging tables';

    public function handle(): int
    {
        $summary = LegacyTableRegistry::summarize();
        $tables = LegacyTableRegistry::tablesToPrune();

        if ($tables === []) {
            $this->info('No obsolete or legacy staging tables found.');

            return self::SUCCESS;
        }

        $this->line('Obsolete application tables:');
        foreach ($summary['obsolete'] as $table) {
            $this->line("  - {$table}");
        }

        $this->line('Legacy fs_* staging tables: '.count($summary['staging']));
        foreach ($summary['staging'] as $table) {
            $this->line("  - {$table}");
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run only — no tables were dropped.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Drop '.count($tables).' table(s)?', true)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
                $this->line("Dropped {$table}");
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Legacy table prune complete.');

        return self::SUCCESS;
    }
}
