<?php

namespace Tests\Unit\Repositories;

use App\Models\Section;
use App\Repositories\BaseRepository;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    use UsesKweekTestSchema;

    private BaseRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();

        $this->repository = new class(new Section()) extends BaseRepository {
            protected function filterableColumns(): array
            {
                return ['isActive', 'serviceTypeFlag'];
            }

            protected function searchableColumns(): array
            {
                return ['name'];
            }
        };
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        parent::tearDown();
    }

    public function test_create_read_update_delete(): void
    {
        // Create
        $model = $this->repository->create([
            'id' => 'repo-1',
            'name' => 'Fashion',
            'isActive' => true,
            'serviceTypeFlag' => 'ecommerce-service',
        ]);
        $this->assertSame('Fashion', $model->name);

        // Read
        $found = $this->repository->find('repo-1');
        $this->assertNotNull($found);

        // Update
        $updated = $this->repository->update($found, ['name' => 'Updated Fashion']);
        $this->assertSame('Updated Fashion', $updated->name);

        // Delete
        $this->assertTrue($this->repository->delete($updated));
        $this->assertNull($this->repository->find('repo-1'));
    }

    public function test_paginate_applies_filters_and_search(): void
    {
        // Arrange
        $this->repository->create([
            'id' => 's1', 'name' => 'Food', 'isActive' => true, 'serviceTypeFlag' => 'delivery-service',
        ]);
        $this->repository->create([
            'id' => 's2', 'name' => 'Fashion', 'isActive' => true, 'serviceTypeFlag' => 'ecommerce-service',
        ]);

        // Act
        $paginator = $this->repository->paginate([
            'serviceTypeFlag' => 'delivery-service',
            'search' => 'Food',
        ], 10);

        // Assert
        $this->assertCount(1, $paginator->items());
        $this->assertSame('s1', $paginator->items()[0]->id);
    }

    public function test_find_or_fail_throws_for_missing_record(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->repository->findOrFail('missing');
    }
}
