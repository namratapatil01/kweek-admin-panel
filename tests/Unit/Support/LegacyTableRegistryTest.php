<?php

namespace Tests\Unit\Support;

use App\Support\LegacyTableRegistry;
use PHPUnit\Framework\TestCase;

class LegacyTableRegistryTest extends TestCase
{
    public function test_obsolete_application_tables_list(): void
    {
        $tables = LegacyTableRegistry::obsoleteApplicationTables();

        $this->assertContains('heartbeats', $tables);
        $this->assertContains('reports', $tables);
    }
}
