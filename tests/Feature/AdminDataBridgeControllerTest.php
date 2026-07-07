<?php

namespace Tests\Feature;

use App\Models\Section;
use App\Models\User;
use App\Services\DocumentStoreService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

/**
 * Feature tests for the MySQL admin data bridge (legacy Firestore client replacement).
 */
class AdminDataBridgeControllerTest extends TestCase
{
    use UsesKweekTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_document_returns_mysql_row_as_json(): void
    {
        // Arrange
        Section::query()->create([
            'id' => 'sec-1',
            'name' => 'Restaurants',
            'isActive' => true,
            'serviceTypeFlag' => 'delivery-service',
        ]);

        // Act
        $response = $this->getJson('/admin-data/document/sections/sec-1');

        // Assert
        $response->assertOk()
            ->assertJsonPath('data.id', 'sec-1')
            ->assertJsonPath('data.name', 'Restaurants');
    }

    public function test_query_validates_collection_and_returns_data(): void
    {
        // Arrange
        Section::query()->create([
            'id' => 'sec-1', 'name' => 'A', 'isActive' => true, 'serviceTypeFlag' => 'delivery-service',
        ]);

        // Act
        $response = $this->postJson('/admin-data/query', [
            'collection' => 'sections',
            'filters' => [['field' => 'isActive', 'op' => '==', 'value' => 1]],
            'limit' => 10,
        ]);

        // Assert
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_query_validation_error_for_missing_collection(): void
    {
        $response = $this->postJson('/admin-data/query', []);
        $response->assertStatus(422);
    }

    public function test_upsert_creates_document_in_mysql(): void
    {
        // Act
        $response = $this->postJson('/admin-data/upsert', [
            'collection' => 'sections',
            'id' => 'new-sec',
            'data' => [
                'name' => 'New Section',
                'isActive' => true,
                'serviceTypeFlag' => 'delivery-service',
            ],
        ]);

        // Assert
        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('sections', ['id' => 'new-sec', 'name' => 'New Section']);
    }

    public function test_upsert_merge_combines_existing_document(): void
    {
        // Arrange
        $this->postJson('/admin-data/upsert', [
            'collection' => 'settings',
            'id' => 'globalSettings',
            'data' => ['applicationName' => 'KWEEK', 'color' => '#000'],
        ]);

        // Act
        $response = $this->postJson('/admin-data/upsert', [
            'collection' => 'settings',
            'id' => 'globalSettings',
            'merge' => true,
            'data' => ['applicationName' => 'KWEEK Pro'],
        ]);

        // Assert
        $response->assertOk();
        $doc = app(DocumentStoreService::class)->getDocument('settings', 'globalSettings');
        $this->assertSame('KWEEK Pro', $doc['applicationName']);
        $this->assertSame('#000', $doc['color']);
    }

    public function test_delete_document_removes_row(): void
    {
        // Arrange
        Section::query()->create([
            'id' => 'delete-me', 'name' => 'X', 'isActive' => true, 'serviceTypeFlag' => 'x',
        ]);

        // Act
        $response = $this->deleteJson('/admin-data/document/sections/delete-me');

        // Assert
        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('sections', ['id' => 'delete-me']);
    }

    public function test_upload_stores_file_using_storage_service(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('banner.jpg');

        $response = $this->postJson('/admin-data/upload', [
            'file' => $file,
            'directory' => 'images',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['url', 'path']);
    }

    public function test_delete_file_requires_path_or_url(): void
    {
        $response = $this->postJson('/admin-data/delete-file', []);
        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_delete_file_by_storage_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/test.jpg', 'content');

        $response = $this->postJson('/admin-data/delete-file', [
            'url' => '/storage/images/test.jpg',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        Storage::disk('public')->assertMissing('images/test.jpg');
    }

    public function test_upsert_validation_error_for_missing_fields(): void
    {
        $response = $this->postJson('/admin-data/upsert', ['collection' => 'sections']);
        $response->assertStatus(422);
    }
}
