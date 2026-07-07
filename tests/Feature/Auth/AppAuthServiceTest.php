<?php

namespace Tests\Feature\Auth;

use App\Models\AppUser;
use App\Services\Auth\AppAuthService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\UsesKweekTestSchema;
use Tests\TestCase;

/**
 * Authentication is backed by MySQL + Sanctum (replaces legacy Firebase Auth).
 */
class AppAuthServiceTest extends TestCase
{
    use UsesKweekTestSchema;

    private AppAuthService $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpKweekSchema();
        $this->auth = new AppAuthService();
    }

    protected function tearDown(): void
    {
        $this->tearDownKweekSchema();
        parent::tearDown();
    }

    public function test_register_creates_user_in_mysql_and_returns_token(): void
    {
        // Act
        $result = $this->auth->register([
            'email' => 'user@example.com',
            'password' => 'secret123',
            'firstName' => 'Test',
            'lastName' => 'User',
        ]);

        // Assert
        $this->assertDatabaseHas('app_users', ['email' => 'user@example.com']);
        $this->assertNotEmpty($result['token']);
        $this->assertTrue(Hash::check('secret123', $result['user']->password));
    }

    public function test_login_with_valid_credentials(): void
    {
        // Arrange
        AppUser::query()->create([
            'id' => 'user-1',
            'email' => 'login@example.com',
            'password' => Hash::make('password'),
            'active' => true,
            'isActive' => true,
            'role' => 'customer',
        ]);

        // Act
        $result = $this->auth->login('login@example.com', 'password');

        // Assert
        $this->assertSame('login@example.com', $result['user']->email);
        $this->assertNotEmpty($result['token']);
    }

    public function test_login_throws_validation_exception_for_invalid_credentials(): void
    {
        $this->expectException(ValidationException::class);
        $this->auth->login('missing@example.com', 'wrong');
    }

    public function test_login_rejects_inactive_users(): void
    {
        // Arrange
        AppUser::query()->create([
            'id' => 'inactive',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'active' => false,
            'isActive' => false,
        ]);

        // Assert
        $this->expectException(ValidationException::class);
        $this->auth->login('inactive@example.com', 'password');
    }

    public function test_logout_revokes_current_token(): void
    {
        // Arrange
        $registered = $this->auth->register([
            'email' => 'logout@example.com',
            'password' => 'password',
        ]);
        $user = $registered['user'];
        $user->withAccessToken($user->tokens()->first());

        // Act
        $this->auth->logout($user);

        // Assert
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_refresh_rotates_token(): void
    {
        // Arrange
        $registered = $this->auth->register([
            'email' => 'refresh@example.com',
            'password' => 'password',
        ]);
        $user = $registered['user'];
        $oldToken = $registered['token'];
        $user->withAccessToken($user->tokens()->first());

        // Act
        $newToken = $this->auth->refresh($user);

        // Assert
        $this->assertNotSame($oldToken, $newToken);
        $this->assertSame(1, $user->tokens()->count());
    }
}
