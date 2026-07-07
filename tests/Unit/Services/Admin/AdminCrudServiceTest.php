<?php

namespace Tests\Unit\Services\Admin;

use App\Models\AppUser;
use App\Models\Section;
use App\Services\Admin\AdminCrudService;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

class AdminCrudServiceTest extends TestCase
{
    use UsesKweekTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        parent::tearDown();
    }

    public function test_store_find_update_destroy_lifecycle(): void
    {
        // Arrange
        $service = new AdminCrudService(new Section(), ['searchable' => ['name']]);

        // Act — create
        $created = $service->store([
            'name' => 'Groceries',
            'isActive' => true,
            'serviceTypeFlag' => 'delivery-service',
        ]);

        // Assert — read
        $this->assertNotEmpty($created->id);
        $found = $service->findOrFail($created->id);
        $this->assertSame('Groceries', $found->name);

        // Act — update
        $updated = $service->update($created->id, ['name' => 'Groceries Plus']);

        // Assert
        $this->assertSame('Groceries Plus', $updated->name);

        // Act — delete
        $service->destroy($created->id);

        // Assert
        $this->assertDatabaseMissing('sections', ['id' => $created->id]);
    }

    public function test_paginate_applies_filters_search_and_sorting(): void
    {
        // Arrange
        $service = new AdminCrudService(new Section(), ['searchable' => ['name']]);
        $service->store(['name' => 'Alpha', 'isActive' => true, 'serviceTypeFlag' => 'delivery-service']);
        $service->store(['name' => 'Beta', 'isActive' => false, 'serviceTypeFlag' => 'cab-service']);
        $service->store(['name' => 'Alpine', 'isActive' => true, 'serviceTypeFlag' => 'delivery-service']);

        // Act
        $paginator = $service->paginate([
            'isActive' => true,
            'search' => 'Al',
            'serviceTypeFlag' => 'delivery-service',
        ], 10, 'name', 'asc');

        // Assert
        $this->assertCount(2, $paginator->items());
        $this->assertSame('Alpha', $paginator->items()[0]->name);
        $this->assertSame('Alpine', $paginator->items()[1]->name);
    }

    public function test_datatable_returns_total_and_items(): void
    {
        // Arrange
        $service = new AdminCrudService(new Section(), ['searchable' => ['name']]);
        $service->store(['name' => 'One', 'isActive' => true, 'serviceTypeFlag' => 'x']);
        $service->store(['name' => 'Two', 'isActive' => true, 'serviceTypeFlag' => 'x']);

        // Act
        $result = $service->datatable(['search' => 'One'], 0, 10, 'name', 'asc');

        // Assert
        $this->assertSame(1, $result['total']);
        $this->assertCount(1, $result['items']);
        $this->assertSame('One', $result['items'][0]->name);
    }

    public function test_bulk_destroy_deletes_multiple_records(): void
    {
        // Arrange
        $service = new AdminCrudService(new Section());
        $a = $service->store(['name' => 'A', 'isActive' => true, 'serviceTypeFlag' => 'x']);
        $b = $service->store(['name' => 'B', 'isActive' => true, 'serviceTypeFlag' => 'x']);
        $service->store(['name' => 'C', 'isActive' => true, 'serviceTypeFlag' => 'x']);

        // Act
        $deleted = $service->bulkDestroy([$a->id, $b->id]);

        // Assert
        $this->assertSame(2, $deleted);
        $this->assertDatabaseCount('sections', 1);
    }

    public function test_customer_scope_limits_queries_to_customers(): void
    {
        // Arrange
        $service = new AdminCrudService(new AppUser(), ['scope' => 'customers']);
        AppUser::query()->create([
            'id' => 'cust-1', 'email' => 'c@example.com', 'role' => 'customer', 'active' => true, 'isActive' => true,
        ]);
        AppUser::query()->create([
            'id' => 'vend-1', 'email' => 'v@example.com', 'role' => 'vendor', 'active' => true, 'isActive' => true,
        ]);

        // Act
        $paginator = $service->paginate([], 10);

        // Assert
        $this->assertCount(1, $paginator->items());
        $this->assertSame('customer', $paginator->items()[0]->role);
    }

    public function test_store_hashes_password_for_app_users(): void
    {
        // Arrange
        $service = new AdminCrudService(new AppUser(), ['scope' => 'customers']);

        // Act
        $user = $service->store([
            'email' => 'new@example.com',
            'password' => 'plain-secret',
        ]);

        // Assert
        $this->assertTrue(Hash::check('plain-secret', $user->password));
        $this->assertSame('customer', $user->role);
    }

    public function test_find_or_fail_throws_for_missing_record(): void
    {
        $service = new AdminCrudService(new Section());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $service->findOrFail('missing-id');
    }
}
