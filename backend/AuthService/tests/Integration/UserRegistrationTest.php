<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Application\Auth\AuthService;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\LoginUserDTO;
use App\Events\UserCreated;
use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

/**
 * Интеграционные тесты для AuthService
 * Используют реальную базу данных (PostgreSQL)
 */
class AuthServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private string $jwtSecret;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ В интеграционных тестах НЕ отключаем события
        // Event::fake();  // ← НЕ вызываем! События должны работать

        $this->jwtSecret = env('JWT_SECRET', 'testsecret');
        $userRepo = app(\App\Infrastructure\Auth\Repositories\EloquentUserRepository::class);

        $this->authService = new AuthService($userRepo, $this->jwtSecret);
    }

    /**
     * @test
     * Проверяет, что пользователь действительно сохраняется в БД
     */
    public function user_is_persisted_in_database_after_registration()
    {
        $dto = new RegisterUserDTO(
            name: 'Integration Test User',
            email: 'integration@example.com',
            password: 'StrongP@ssw0rd123!'
        );

        $user = $this->authService->register($dto);

        // ✅ Проверяем, что пользователь есть в реальной БД
        $this->assertDatabaseHas('users', [
            'id' => $user->getId(),
            'email' => 'integration@example.com',
            'name' => 'Integration Test User'
        ]);

        // ✅ Проверяем, что пароль захеширован правильно
        $dbUser = User::find($user->getId());
        $this->assertNotEquals('StrongP@ssw0rd123!', $dbUser->password);
        $this->assertTrue(password_verify('StrongP@ssw0rd123!', $dbUser->password));
    }

    /**
     * @test
     * Проверяет, что событие UserCreated действительно отправляется
     */
    public function user_created_event_is_dispatched()
    {
        // Включаем прослушивание событий
        Event::fake();

        $dto = new RegisterUserDTO(
            name: 'Event Test User',
            email: 'event@example.com',
            password: 'StrongP@ssw0rd123!'
        );

        $user = $this->authService->register($dto);

        // ✅ Проверяем, что событие было отправлено
        Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
            return $event->userId === $user->getId();
        });
    }

    /**
     * @test
     * Проверяет, что JWT токен валиден и можно получить пользователя
     */
    public function jwt_token_is_valid_and_can_retrieve_user()
    {
        // Регистрация
        $registerDto = new RegisterUserDTO(
            name: 'JWT Test User',
            email: 'jwt@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $user = $this->authService->register($registerDto);

        // Логин
        $loginDto = new LoginUserDTO(
            email: 'jwt@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $result = $this->authService->login($loginDto);

        // ✅ Проверяем, что токен не пустой
        $this->assertNotEmpty($result->token);

        // ✅ Проверяем, что из токена можно получить пользователя
        $userFromToken = $this->authService->getUserFromToken($result->token);
        $this->assertNotNull($userFromToken);
        $this->assertEquals($user->getId(), $userFromToken->getId());
        $this->assertEquals($user->getEmail(), $userFromToken->getEmail());
    }

    /**
     * @test
     * Проверяет, что нельзя зарегистрировать двух пользователей с одинаковым email
     */
    public function cannot_register_duplicate_email()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User with this email already exists');

        $dto = new RegisterUserDTO(
            name: 'Test User',
            email: 'duplicate@example.com',
            password: 'StrongP@ssw0rd123!'
        );

        $this->authService->register($dto);
        $this->authService->register($dto); // Попытка повторной регистрации
    }

    /**
     * @test
     * Проверяет, что нельзя войти с неверным паролем
     */
    public function cannot_login_with_wrong_password()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email or password');

        // Регистрация
        $registerDto = new RegisterUserDTO(
            name: 'Wrong Password Test',
            email: 'wrongpass@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $this->authService->register($registerDto);

        // Попытка входа с неверным паролем
        $loginDto = new LoginUserDTO(
            email: 'wrongpass@example.com',
            password: 'WrongPassword123!'
        );
        $this->authService->login($loginDto);
    }

    /**
     * @test
     * Проверяет, что пользователь может удалить свой аккаунт
     */
    public function user_can_delete_own_account()
    {
        // Регистрация
        $registerDto = new RegisterUserDTO(
            name: 'Delete Test',
            email: 'delete@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $user = $this->authService->register($registerDto);

        // Логин для получения токена
        $loginDto = new LoginUserDTO(
            email: 'delete@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $result = $this->authService->login($loginDto);

        // Удаление пользователя
        $deleted = $this->authService->deleteUser($result->token);
        $this->assertTrue($deleted);

        // Проверяем, что пользователь удален из БД
        $this->assertDatabaseMissing('users', [
            'id' => $user->getId()
        ]);

        // Проверяем, что с токеном больше нельзя получить пользователя
        $userFromToken = $this->authService->getUserFromToken($result->token);
        $this->assertNull($userFromToken);
    }

    /**
     * @test
     * Проверяет, что пользователь может обновить свои данные
     */
    public function user_can_update_own_profile()
    {
        // Регистрация
        $registerDto = new RegisterUserDTO(
            name: 'Update Test',
            email: 'update@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $user = $this->authService->register($registerDto);

        // Логин для получения токена
        $loginDto = new LoginUserDTO(
            email: 'update@example.com',
            password: 'StrongP@ssw0rd123!'
        );
        $result = $this->authService->login($loginDto);

        // Обновление данных
        $updated = $this->authService->updateUser($result->token, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
        $this->assertTrue($updated);

        // Проверяем, что данные обновились в БД
        $this->assertDatabaseHas('users', [
            'id' => $user->getId(),
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        // Проверяем, что из токена можно получить обновленного пользователя
        $updatedUser = $this->authService->getUserFromToken($result->token);
        $this->assertEquals('Updated Name', $updatedUser->getName());
        $this->assertEquals('updated@example.com', $updatedUser->getEmail());
    }
}