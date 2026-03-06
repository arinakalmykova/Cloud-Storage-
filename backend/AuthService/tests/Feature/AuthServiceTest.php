<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User as UserModel;
use App\Application\Auth\AuthService;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\LoginUserDTO;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private string $jwtSecret;

    public function setUp(): void
    {
        parent::setUp();

        $this->jwtSecret = env('JWT_SECRET', 'testsecret');
        $userRepo = app(\App\Infrastructure\Auth\Repositories\EloquentUserRepository::class);

        $this->authService = new AuthService($userRepo, $this->jwtSecret);
    }

    public function test_register_login_and_get_user_from_jwt()
    {
        // Регистрация нового пользователя
        $registerDto = new RegisterUserDTO(
            name: 'Test User',
            email: 'testuser@example.com',
            password: 'password123'
        );

        $user = $this->authService->register($registerDto);

        $this->assertNotEmpty($user->getId());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('testuser@example.com', $user->getEmail());

        // Проверка, что пользователь сохранен в базе
        $this->assertDatabaseHas('users', [
            'id' => $user->getId(),
            'email' => $user->getEmail()
        ]);

        // Логин зарегистрированного пользователя
        $loginDto = new LoginUserDTO(
            email: 'testuser@example.com',
            password: 'password123'
        );

        $loginResult = $this->authService->login($loginDto);

        $this->assertEquals($user->getId(), $loginResult->userId);
        $this->assertNotEmpty($loginResult->token);

        // Получение пользователя из JWT
        $userFromToken = $this->authService->getUserFromToken($loginResult->token);

        $this->assertNotNull($userFromToken);
        $this->assertEquals($user->getId(), $userFromToken->getId());
        $this->assertEquals($user->getEmail(), $userFromToken->getEmail());

        // Проверка некорректного токена
        $invalidUser = $this->authService->getUserFromToken('invalid.token.here');
        $this->assertNull($invalidUser);
    }


    public function test_login_with_invalid_credentials_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $loginDto = new LoginUserDTO(
            email: 'nonexistent@example.com',
            password: 'wrongpassword'
        );

        $this->authService->login($loginDto);
    }
}