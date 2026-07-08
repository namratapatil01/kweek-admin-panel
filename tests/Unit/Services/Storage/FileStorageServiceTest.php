<?php

namespace Tests\Unit\Services\Storage;

use App\Services\Storage\FileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * File storage tests (replaces legacy Firebase Storage operations).
 */
class FileStorageServiceTest extends TestCase
{
    private FileStorageService $storage;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['kweek.upload.max_size_kb' => 1024, 'kweek.upload.allowed_mimes' => ['image/jpeg', 'image/png']]);
        $this->storage = new FileStorageService();
    }

    public function test_upload_stores_file_and_returns_metadata(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('photo.jpg');

        // Act
        $result = $this->storage->upload($file, 'images', 'public');

        // Assert
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('url', $result);
        Storage::disk('public')->assertExists($result['path']);
    }

    public function test_upload_rejects_disallowed_mime_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $file = UploadedFile::fake()->create('document.exe', 100, 'application/octet-stream');
        $this->storage->upload($file);
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $file = UploadedFile::fake()->create('large.jpg', 3000, 'image/jpeg');
        $this->storage->upload($file);
    }

    public function test_delete_removes_existing_file(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('photo.jpg');
        $uploaded = $this->storage->upload($file, 'images', 'public');

        // Act
        $deleted = $this->storage->delete($uploaded['path']);

        // Assert
        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing($uploaded['path']);
    }
}
