<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Application\Auth\AuthService;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\LoginUserDTO;
use App\Events\UserCreated;
use InvalidArgumentException;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private string $jwtSecret;

    public function setUp(): void
    {
        parent::setUp();

        // Отключаем события Kafka для тестов
        Event::fake();

        $this->jwtSecret = env('JWT_SECRET', 'testsecret');
        $userRepo = app(\App\Infrastructure\Auth\Repositories\EloquentUserRepository::class);

        $this->authService = new AuthService($userRepo, $this->jwtSecret);
    }

    /**
     * @test
     */
    public function register_login_and_get_user_from_jwt()
    {
        $registerDto = new RegisterUserDTO(
            name: 'Test User',
            email: 'testuser@example.com',
            password: 'StrongP@ssw0rd123!'
        );

        $user = $this->authService->register($registerDto);

        $this->assertNotEmpty($user->getId());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('testuser@example.com', $user->getEmail());

        Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
            return $event->userId === $user->getId();
        });

        $this->assertDatabaseHas('users', [
            'id' => $user->getId(),
            'email' => $user->getEmail()
        ]);

        $loginDto = new LoginUserDTO(
            email: 'testuser@example.com',
            password: 'StrongP@ssw0rd123!'
        );

        $loginResult = $this->authService->login($loginDto);

        $this->assertEquals($user->getId(), $loginResult->userId);
        $this->assertNotEmpty($loginResult->token);

        $userFromToken = $this->authService->getUserFromToken($loginResult->token);

        $this->assertNotNull($userFromToken);
        $this->assertEquals($user->getId(), $userFromToken->getId());
        $this->assertEquals($user->getEmail(), $userFromToken->getEmail());

        $invalidUser = $this->authService->getUserFromToken('invalid.token.here');
        $this->assertNull($invalidUser);
    }

    /**
     * @test
     */
    public function login_with_invalid_credentials_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email or password');

        $loginDto = new LoginUserDTO(
            email: 'nonexistent@example.com',
            password: 'wrongpassword'
        );

        $this->authService->login($loginDto);
    }

    /**
     * @test
     */
    public function login_with_weak_password_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain');

        $registerDto = new RegisterUserDTO(
            name: 'Test User',
            email: 'test@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $this->authService->register($registerDto);

        $loginDto = new LoginUserDTO(
            email: 'test@example.com',
            password: 'weak'
        );

        $this->authService->login($loginDto);
    }
}