<?php

namespace Tests\Unit\Services\Admin;

use App\Services\Admin\AdminModuleRegistry;
use InvalidArgumentException;
use Tests\TestCase;

class AdminModuleRegistryTest extends TestCase
{
    private AdminModuleRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new AdminModuleRegistry();
    }

    public function test_get_returns_module_config_with_defaults(): void
    {
        // Act
        $config = $this->registry->get('sections');

        // Assert
        $this->assertSame('sections', $config['slug']);
        $this->assertSame('sections', $config['route']);
        $this->assertSame('sections', $config['view']);
        $this->assertSame(\App\Models\Section::class, $config['model']);
    }

    public function test_get_normalizes_underscore_slug_to_dash(): void
    {
        // Act
        $config = $this->registry->get('gift_cards');

        // Assert
        $this->assertSame('gift-cards', $config['slug']);
    }

    public function test_get_throws_for_unknown_module(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown admin module [not-a-real-module].');

        $this->registry->get('not-a-real-module');
    }

    public function test_slugs_returns_all_registered_modules(): void
    {
        // Act
        $slugs = $this->registry->slugs();

        // Assert
        $this->assertContains('sections', $slugs);
        $this->assertContains('taxes', $slugs);
        $this->assertNotEmpty($slugs);
    }
}
