<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use UsesKweekTestSchema;

    private SettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();
        config(['kweek.cache.enabled' => true, 'kweek.cache.ttl' => 60]);
        Cache::flush();
        $this->service = new SettingsService();
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        parent::tearDown();
    }

    public function test_put_creates_setting_in_mysql(): void
    {
        // Act
        $setting = $this->service->put('globalSettings', [
            'applicationName' => 'KWEEK',
            'admin_panel_color' => '#000000',
        ]);

        // Assert
        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertDatabaseHas('settings', ['id' => 'globalSettings']);
        $this->assertSame('KWEEK', Setting::find('globalSettings')->value['applicationName']);
    }

    public function test_get_reads_from_mysql_and_uses_cache(): void
    {
        // Arrange
        $this->service->put('globalSettings', ['applicationName' => 'KWEEK']);

        // Act
        $first = $this->service->get('globalSettings');
        Setting::query()->where('id', 'globalSettings')->update(['value' => json_encode(['applicationName' => 'Changed'])]);
        $cached = $this->service->get('globalSettings');

        // Assert
        $this->assertSame('KWEEK', $first['applicationName']);
        $this->assertSame('KWEEK', $cached['applicationName']);
    }

    public function test_get_returns_default_when_missing(): void
    {
        $this->assertSame('default', $this->service->get('missing-key', 'default'));
    }

    public function test_forget_deletes_setting_from_mysql(): void
    {
        // Arrange
        $this->service->put('tempSetting', ['foo' => 'bar']);

        // Act
        $this->service->forget('tempSetting');

        // Assert
        $this->assertDatabaseMissing('settings', ['id' => 'tempSetting']);
        $this->assertNull($this->service->get('tempSetting'));
    }

    public function test_put_invalidates_cache(): void
    {
        // Arrange
        $this->service->put('globalSettings', ['applicationName' => 'Old']);
        $this->service->get('globalSettings');

        // Act
        $this->service->put('globalSettings', ['applicationName' => 'New']);

        // Assert
        $this->assertSame('New', $this->service->get('globalSettings')['applicationName']);
    }
}
