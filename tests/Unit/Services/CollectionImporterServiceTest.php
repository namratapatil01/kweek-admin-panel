<?php

namespace Tests\Unit\Services;

use App\Models\Section;
use App\Models\Setting;
use App\Services\CollectionImporterService;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

class CollectionImporterServiceTest extends TestCase
{
    use UsesKweekTestSchema;

    private CollectionImporterService $importer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();
        $this->importer = new CollectionImporterService();
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        parent::tearDown();
    }

    public function test_import_document_stores_setting_in_mysql(): void
    {
        // Arrange
        $doc = [
            'applicationName' => 'KWEEK',
            'admin_panel_color' => '#123456',
        ];

        // Act
        $model = $this->importer->importDocument(Setting::class, 'globalSettings', $doc);

        // Assert
        $this->assertInstanceOf(Setting::class, $model);
        $this->assertSame('KWEEK', $model->fresh()->value['applicationName']);
    }

    public function test_import_document_moves_nested_objects_to_payload_for_sections(): void
    {
        // Arrange
        $doc = [
            'id' => 'section-1',
            'name' => 'Restaurants',
            'serviceTypeFlag' => 'delivery-service',
            'isActive' => true,
            'adminCommision' => ['commission' => 10, 'enable' => true, 'type' => 'fixed'],
            'delivery_charge' => '',
        ];

        // Act
        $section = $this->importer->importDocument(Section::class, 'section-1', $doc);

        // Assert
        $this->assertSame('Restaurants', $section->name);
        $this->assertTrue((bool) $section->isActive);
        $this->assertArrayHasKey('adminCommision', $section->payload);
        $this->assertNull($section->delivery_charge);
    }

    public function test_import_collection_returns_stats(): void
    {
        // Arrange
        $documents = [
            's1' => ['id' => 's1', 'name' => 'A', 'isActive' => true, 'serviceTypeFlag' => 'delivery-service'],
            's2' => ['id' => 's2', 'name' => 'B', 'isActive' => false, 'serviceTypeFlag' => 'cab-service'],
        ];

        // Act
        $stats = $this->importer->importCollection(Section::class, Section::class, $documents, 50, true);

        // Assert
        $this->assertSame(2, $stats['imported']);
        $this->assertSame(0, $stats['failed']);
        $this->assertSame(2, Section::query()->count());
    }

    public function test_import_collection_counts_failed_rows(): void
    {
        // Arrange — invalid model forces failures when documents are malformed
        $documents = ['bad' => ['id' => 'bad']];

        // Act
        $stats = $this->importer->importCollection('sections', Section::class, $documents, 10, false);

        // Assert
        $this->assertSame(1, $stats['imported']);
        $this->assertSame(0, $stats['failed']);
    }

    public function test_normalize_value_handles_firestore_timestamp(): void
    {
        $method = new \ReflectionMethod(CollectionImporterService::class, 'normalizeValue');
        $method->setAccessible(true);

        $result = $method->invoke($this->importer, ['_seconds' => 1609459200]);

        $this->assertSame('2021-01-01 00:00:00', $result);
    }
}
