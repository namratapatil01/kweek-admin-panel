<?php

namespace Tests\Unit\Services;

use App\Services\EntityRegistry;
use InvalidArgumentException;
use Tests\TestCase;

class EntityRegistryTest extends TestCase
{
    public function test_get_returns_registered_entity_service(): void
    {
        $registry = new EntityRegistry();

        $this->assertNotEmpty($registry->slugs());
        $service = $registry->get('sections');
        $this->assertNotNull($service);
    }

    public function test_get_throws_for_unknown_entity(): void
    {
        $registry = new EntityRegistry();

        $this->expectException(InvalidArgumentException::class);
        $registry->get('not-a-real-entity-slug');
    }

    public function test_slug_normalizes_underscores_to_dashes(): void
    {
        $registry = new EntityRegistry();
        $slugs = $registry->slugs();

        $this->assertContains('vendor-orders', $slugs);
    }
}
