<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyTableRegistry
{
    /**
     * Application tables that are no longer read or written by the codebase.
     *
     * @return array<int, string>
     */
    public static function obsoleteApplicationTables(): array
    {
        return [
            'heartbeats',
            'reports',
        ];
    }

    /**
     * Legacy Firebase SQL-import staging tables (fs_* prefix).
     *
     * @return array<int, string>
     */
    public static function legacyStagingTables(): array
    {
        return collect(Schema::getTableListing())
            ->filter(fn (string $table) => str_starts_with($table, 'fs_'))
            ->values()
            ->all();
    }

    /**
     * All tables safe to prune from the current MySQL schema.
     *
     * @return array<int, string>
     */
    public static function tablesToPrune(): array
    {
        return array_values(array_unique(array_merge(
            self::obsoleteApplicationTables(),
            self::legacyStagingTables()
        )));
    }

    /**
     * @return array{obsolete: array<int, string>, staging: array<int, string>}
     */
    public static function summarize(): array
    {
        $staging = self::legacyStagingTables();
        $obsolete = array_values(array_filter(
            self::obsoleteApplicationTables(),
            fn (string $table) => Schema::hasTable($table)
        ));

        return [
            'obsolete' => $obsolete,
            'staging' => $staging,
        ];
    }
}
