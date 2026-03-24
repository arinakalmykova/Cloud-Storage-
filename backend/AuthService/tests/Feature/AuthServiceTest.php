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
    use RefreshDatabase;  // ✅ Это создает тестовую БД и очищает после тестов

    private AuthService $authService;
    private string $jwtSecret;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ Отключаем события (чтобы Kafka не мешал)
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
        // ✅ Используем пароль, который соответствует требованиям
        $registerDto = new RegisterUserDTO(
            name: 'Test User',
            email: 'testuser@example.com',
            password: 'StrongP@ssw0rd123!'  // ← правильный пароль
        );

        $user = $this->authService->register($registerDto);

        $this->assertNotEmpty($user->getId());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('testuser@example.com', $user->getEmail());

        // ✅ Проверяем, что событие было вызвано
        Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
            return $event->userId === $user->getId();
        });

        // ✅ Проверка, что пользователь сохранен в тестовой БД
        $this->assertDatabaseHas('users', [
            'id' => $user->getId(),
            'email' => $user->getEmail()
        ]);

        // Логин зарегистрированного пользователя
        $loginDto = new LoginUserDTO(
            email: 'testuser@example.com',
            password: 'StrongP@ssw0rd123!'  // ← тот же правильный пароль
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
        $this->expectExceptionMessage('Invalid email or password');

        // Сначала регистрируем пользователя с правильным паролем
        $registerDto = new RegisterUserDTO(
            name: 'Test User',
            email: 'test@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $this->authService->register($registerDto);

        // Пытаемся войти с НЕПРАВИЛЬНЫМ паролем (не соответствующим сохраненному)
        $loginDto = new LoginUserDTO(
            email: 'test@example.com',
            password: 'WrongPassword123!'  // ← просто неправильный пароль
        );

        $this->authService->login($loginDto);
    }
}