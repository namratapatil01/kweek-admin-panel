<?php

namespace Tests\Unit\Services;

use App\Models\Section;
use App\Repositories\BaseRepository;
use App\Services\EntityCrudService;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

class EntityCrudServiceTest extends TestCase
{
    use UsesKweekTestSchema;

    private EntityCrudService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();

        $repository = new class(new Section()) extends BaseRepository {
            protected function filterableColumns(): array
            {
                return ['isActive'];
            }
        };

        $this->service = new EntityCrudService(
            $repository,
            ['id', 'name', 'isActive', 'serviceTypeFlag', 'payload']
        );
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        parent::tearDown();
    }

    public function test_store_persists_entity_to_mysql(): void
    {
        // Act
        $model = $this->service->store([
            'name' => 'Cab',
            'isActive' => true,
            'serviceTypeFlag' => 'cab-service',
        ]);

        // Assert
        $this->assertNotEmpty($model->id);
        $this->assertDatabaseHas('sections', ['name' => 'Cab']);
    }

    public function test_show_update_destroy_lifecycle(): void
    {
        // Arrange
        $created = $this->service->store([
            'id' => 'entity-1',
            'name' => 'Original',
            'isActive' => true,
            'serviceTypeFlag' => 'delivery-service',
        ]);

        // Act & Assert — Read
        $this->assertSame('entity-1', $this->service->show('entity-1')->id);

        // Update
        $updated = $this->service->update('entity-1', ['name' => 'Updated']);
        $this->assertSame('Updated', $updated->name);

        // Delete
        $this->service->destroy('entity-1');
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->show('entity-1');
    }

    public function test_store_moves_unknown_fields_to_payload(): void
    {
        // Act
        $model = $this->service->store([
            'id' => 'payload-test',
            'name' => 'Test',
            'isActive' => true,
            'serviceTypeFlag' => 'delivery-service',
            'customMeta' => ['tier' => 'gold'],
        ]);

        // Assert
        $this->assertSame('gold', $model->fresh()->payload['customMeta']['tier']);
    }

    public function test_list_returns_paginated_results(): void
    {
        // Arrange
        $this->service->store(['id' => 'a', 'name' => 'A', 'isActive' => true, 'serviceTypeFlag' => 'x']);
        $this->service->store(['id' => 'b', 'name' => 'B', 'isActive' => false, 'serviceTypeFlag' => 'x']);

        // Act
        $page = $this->service->list(['isActive' => 1], 10, 'created_at', 'desc');

        // Assert
        $this->assertCount(1, $page->items());
    }
}
