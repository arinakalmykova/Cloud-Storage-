<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Application\Auth\AuthService;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use Mockery;

class PasswordPolicyTest extends TestCase
{
    private $authService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($repository, 'test-secret');
    }
    
    /** @test */
    public function password_shorter_than_8_returns_false()
    {
        $weakPassword = "short";

        $result = $this->authService->isPasswordStrong($weakPassword);

        $this->assertFalse($result);
    }

    /** @test */
    public function password_with_8_chars_but_no_digit_returns_false()
    {
        $password = "password";

        $result = $this->authService->isPasswordStrong($password);

        $this->assertFalse($result);
    }

    /** @test */
    public function password_with_digit_returns_true()
    {
        $password = "password1";

        $result = $this->authService->isPasswordStrong($password);

        $this->assertTrue($result);
    }

     /** @test */
    public function strong_password_returns_true()
    {
        $password = "StrongPass1";

        $result = $this->authService->isPasswordStrong($password);

        $this->assertTrue($result);
    }
}