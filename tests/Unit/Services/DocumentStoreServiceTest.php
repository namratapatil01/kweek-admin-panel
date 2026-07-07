<?php

namespace Tests\Unit\Services;

use App\Models\Section;
use App\Services\DocumentStoreService;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

/**
 * Tests the MySQL document store that replaced legacy Firebase/Firestore client operations.
 */
class DocumentStoreServiceTest extends TestCase
{
    use UsesKweekTestSchema;

    private DocumentStoreService $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();
        $this->store = new DocumentStoreService();
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        parent::tearDown();
    }

    public function test_create_and_read_setting_document(): void
    {
        // Arrange & Act
        $result = $this->store->upsertDocument('settings', 'globalSettings', [
            'applicationName' => 'KWEEK',
        ]);

        // Assert
        $this->assertTrue($result['success']);
        $doc = $this->store->getDocument('settings', 'globalSettings');
        $this->assertSame('KWEEK', $doc['applicationName']);
    }

    public function test_create_read_update_delete_section_crud(): void
    {
        // Create
        $create = $this->store->upsertDocument('sections', 'sec-1', [
            'name' => 'Restaurants',
            'serviceTypeFlag' => 'delivery-service',
            'isActive' => true,
        ]);
        $this->assertTrue($create['success']);

        // Read
        $doc = $this->store->getDocument('sections', 'sec-1');
        $this->assertSame('Restaurants', $doc['name']);

        // Update (merge via upsert)
        $this->store->upsertDocument('sections', 'sec-1', [
            'name' => 'Food',
            'serviceTypeFlag' => 'delivery-service',
            'isActive' => true,
        ]);
        $this->assertSame('Food', $this->store->getDocument('sections', 'sec-1')['name']);

        // Delete
        $this->assertTrue($this->store->deleteDocument('sections', 'sec-1'));
        $this->assertNull($this->store->getDocument('sections', 'sec-1'));
    }

    public function test_query_documents_with_equality_filter(): void
    {
        // Arrange
        Section::query()->create([
            'id' => 'active-1',
            'name' => 'Active',
            'isActive' => true,
            'serviceTypeFlag' => 'delivery-service',
        ]);
        Section::query()->create([
            'id' => 'inactive-1',
            'name' => 'Inactive',
            'isActive' => false,
            'serviceTypeFlag' => 'delivery-service',
        ]);

        // Act
        $results = $this->store->queryDocuments('sections', [
            ['field' => 'isActive', 'op' => '==', 'value' => 1],
        ], 10);

        // Assert
        $this->assertCount(1, $results);
        $this->assertSame('active-1', $results[0]['id']);
    }

    public function test_query_for_bridge_supports_comparison_operators(): void
    {
        // Arrange
        Section::query()->create([
            'id' => 's1',
            'name' => 'A',
            'referralAmount' => 5,
            'isActive' => true,
            'serviceTypeFlag' => 'delivery-service',
        ]);
        Section::query()->create([
            'id' => 's2',
            'name' => 'B',
            'referralAmount' => 15,
            'isActive' => true,
            'serviceTypeFlag' => 'delivery-service',
        ]);

        // Act
        $results = $this->store->queryForBridge('sections', [
            ['field' => 'referralAmount', 'op' => '>=', 'value' => 10],
        ], 10, 'referralAmount', 'asc');

        // Assert
        $this->assertCount(1, $results);
        $this->assertSame('s2', $results[0]['id']);
    }

    public function test_upsert_unknown_collection_returns_failure(): void
    {
        $result = $this->store->upsertDocument('nonexistent_collection', 'id-1', ['foo' => 'bar']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Unknown collection', $result['message']);
    }

    public function test_get_document_returns_null_for_unknown_collection(): void
    {
        $this->assertNull($this->store->getDocument('unknown', 'id-1'));
    }

    public function test_delete_document_returns_false_for_unknown_collection(): void
    {
        $this->assertFalse($this->store->deleteDocument('unknown', 'id-1'));
    }

    public function test_nested_fields_overflow_into_payload_on_upsert(): void
    {
        // Arrange & Act
        $this->store->upsertDocument('sections', 'sec-payload', [
            'name' => 'Test',
            'isActive' => true,
            'serviceTypeFlag' => 'delivery-service',
            'adminCommision' => ['commission' => 12, 'type' => 'percentage'],
        ]);

        // Assert
        $section = Section::query()->find('sec-payload');
        $this->assertIsArray($section->payload);
        $this->assertSame(12, $section->payload['adminCommision']['commission']);
    }
}
