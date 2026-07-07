<?php

namespace Tests\Feature;

use App\Models\Section;
use App\Models\User;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use UsesKweekTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();
        $this->withoutMiddleware(\App\Http\Middleware\CheckUserRoleMiddleware::class);
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        parent::tearDown();
    }

    public function test_get_sections_returns_active_sections_from_mysql(): void
    {
        // Arrange
        Section::query()->create([
            'id' => 's1', 'name' => 'Alpha', 'isActive' => true, 'serviceTypeFlag' => 'delivery-service',
        ]);
        Section::query()->create([
            'id' => 's2', 'name' => 'Beta', 'isActive' => false, 'serviceTypeFlag' => 'cab-service',
        ]);

        User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Act
        $response = $this->actingAs(User::first())->getJson(route('api.sections'));

        // Assert
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Alpha', $response->json('data.0.name'));
    }
}
