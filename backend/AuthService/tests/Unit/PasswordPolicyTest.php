<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Application\Auth\AuthService;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use InvalidArgumentException;
use Mockery;

class PasswordPolicyTest extends TestCase
{
    private AuthService $authService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $repository = Mockery::mock(UserRepositoryInterface::class);
        // Настраиваем мок: findByEmail всегда возвращает null (пользователь не существует)
        $repository->shouldReceive('findByEmail')->andReturn(null);
        $repository->shouldReceive('save')->andReturn(null);
        
        $this->authService = new AuthService($repository, 'test-secret');
    }
    
    /**
     * @test
     */
    public function password_shorter_than_6_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain: at least 6 characters');
        
        $weakPassword = "12345";  // 5 символов (меньше 6)
        
        // Используем рефлексию для вызова приватного метода
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('validatePasswordStrength');
        $method->invokeArgs($this->authService, [$weakPassword]);
    }
    
    /**
     * @test
     */
    public function password_with_6_chars_but_no_uppercase_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain: at least one uppercase letter');
        
        $password = "abcdef";  // 6 символов, только строчные
        
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('validatePasswordStrength');
        $method->invokeArgs($this->authService, [$password]);
    }
    
    /**
     * @test
     */
    public function password_with_6_chars_but_no_lowercase_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain: at least one lowercase letter');
        
        $password = "ABCDEF";  // 6 символов, только заглавные
        
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('validatePasswordStrength');
        $method->invokeArgs($this->authService, [$password]);
    }
    
    /**
     * @test
     */
    public function password_with_6_chars_but_no_digit_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain: at least one number');
        
        $password = "Abcdef";  // 6 символов, нет цифр
        
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('validatePasswordStrength');
        $method->invokeArgs($this->authService, [$password]);
    }
    
    /**
     * @test
     */
    public function password_with_6_chars_but_no_special_character_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain: at least one special character');
        
        $password = "Abc123";  // 6 символов, есть цифры, но нет спецсимволов
        
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('validatePasswordStrength');
        $method->invokeArgs($this->authService, [$password]);
    }
    
    /**
     * @test
     */
    public function strong_password_passes_validation()
    {
        $password = "StrongP@ssw0rd123!";  // Содержит: заглавные, строчные, цифры, спецсимволы
        
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('validatePasswordStrength');
        
        // Должно пройти без исключения
        $method->invokeArgs($this->authService, [$password]);
        
        $this->assertTrue(true); // Если дошли сюда, тест пройден
    }
    
    /**
     * @test
     */
    public function password_with_all_requirements_passes()
    {
        $password = "Valid@Pass123";  // 12 символов, все требования выполнены
        
        $reflection = new \ReflectionClass($this->authService);
        $method = $reflection->getMethod('validatePasswordStrength');
        
        $method->invokeArgs($this->authService, [$password]);
        
        $this->assertTrue(true);
    }
}