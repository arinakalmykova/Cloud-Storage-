<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User as UserModel;
use App\Application\Auth\AuthService;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\LoginUserDTO;
use InvalidArgumentException;
use RuntimeException;

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

    /**
     * @test
     */
    public function register_login_and_get_user_from_jwt()
    {
        // Регистрация нового пользователя
        // ✅ Используем пароль, соответствующий требованиям:
        // - минимум 6 символов
        // - заглавные и строчные буквы
        // - цифры
        // - специальные символы
        $registerDto = new RegisterUserDTO(
            name: 'Test User',
            email: 'testuser@example.com',
            password: 'StrongP@ssw0rd123!'  // ✅ Правильный пароль
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
            password: 'StrongP@ssw0rd123!'  // ✅ Тот же пароль
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
        // ✅ Ожидаем InvalidArgumentException (не общее Exception)
        $this->expectException(InvalidArgumentException::class);
        // ✅ Обновленное сообщение об ошибке
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

        // Сначала регистрируем пользователя с правильным паролем
        $registerDto = new RegisterUserDTO(
            name: 'Test User',
            email: 'test@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $this->authService->register($registerDto);

        // Пытаемся войти с неправильным паролем (не соответствует требованиям)
        $loginDto = new LoginUserDTO(
            email: 'test@example.com',
            password: 'weak'
        );

        $this->authService->login($loginDto);
    }
}