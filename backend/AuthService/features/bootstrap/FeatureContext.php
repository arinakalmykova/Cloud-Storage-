<?php

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use App\Application\Auth\AuthService;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\LoginUserDTO;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Foundation\Application;

class FeatureContext implements Context
{
    private ?Application $app = null;
    private ?AuthService $authService = null;
    private ?UserRepositoryInterface $userRepository = null;

    private $lastResult;
    private $lastException;
    private $lastToken;
    private $lastUser;

    private array $testUsers = [];

    /** @BeforeScenario */
    public function setUp()
    {
        $this->app = require __DIR__ . '/../../bootstrap/app.php';
        $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
        $this->authService = $this->app->make(AuthService::class);

        $this->cleanUp();
    }

    /** @AfterScenario */
    public function tearDown()
    {
        $this->cleanUp();
    }

    private function cleanUp()
    {
        foreach ($this->testUsers as $email) {
            try {
                $user = $this->userRepository->findByEmail($email);
                if ($user) {
                    $this->userRepository->delete($user);
                }
            } catch (\Exception $e) {
                // silently ignore
            }
        }

        $this->testUsers = [];
        $this->lastResult = null;
        $this->lastException = null;
        $this->lastToken = null;
        $this->lastUser = null;
    }

    // ────────────────────────────────────────────────
    // Password strength
    // ────────────────────────────────────────────────

    /**
     * @Given я проверяю пароль :password
     */
    public function iCheckPassword(string $password)
    {
        $this->lastResult = $this->authService->isPasswordStrong($password);
    }

    /**
     * @Then результат должен быть :expected
     */
    public function resultShouldBe(string $expected)
    {
        $expectedBool = filter_var($expected, FILTER_VALIDATE_BOOLEAN);
        Assert::assertSame($expectedBool, $this->lastResult);
    }

    // ────────────────────────────────────────────────
    // Registration
    // ────────────────────────────────────────────────

    /**
     * @When я пытаюсь зарегистрировать пользователя с именем :name, email :email и паролем :password
     */
    public function iTryToRegisterUser(string $name, string $email, string $password)
    {
        echo "REGISTER DEBUG: name='$name', email='$email', password='$password' (empty? " . (empty($password) ? 'YES' : 'no') . ")\n";

        try {
            if (empty($name) || empty($email) || empty($password)) {
                throw new \Exception('Invalid credentials');
            }

            if (!$this->authService->isPasswordStrong($password)) {
                throw new \Exception('Invalid credentials');
            }

            $dto = new RegisterUserDTO($name, $email, $password);
            $user = $this->authService->register($dto);

            $this->lastResult = 'success';
            $this->lastUser = $user;
            $this->testUsers[] = $email;
            $this->lastException = null;
        } catch (\Exception $e) {
            $this->lastResult = 'error';
            $this->lastException = $e;
        }
    }

    /**
     * @Then регистрация должна быть успешной
     */
    public function registrationShouldBeSuccessful()
    {
        Assert::assertSame('success', $this->lastResult);
        Assert::assertNotNull($this->lastUser);
    }

    /**
     * @Then регистрация должна завершиться с ошибкой :errorMessage
     */
    public function registrationShouldFailWithError(string $errorMessage)
    {
        Assert::assertSame('error', $this->lastResult);
        Assert::assertNotNull($this->lastException);
        Assert::assertStringContainsString($errorMessage, $this->lastException->getMessage());
    }

    // ────────────────────────────────────────────────
    // Login
    // ────────────────────────────────────────────────

    /**
     * @When я пытаюсь войти с email :email и паролем :password
     */
    public function iTryToLogin(string $email, string $password)
    {
        echo "LOGIN DEBUG: email='$email', password='$password' (empty? " . (empty($password) ? 'YES' : 'no') . ")\n";

        try {
            if (empty($email) || empty($password)) {
                echo "→ throwing Invalid credentials (empty input detected)\n";
                throw new \Exception('Invalid credentials');
            }

            $dto = new LoginUserDTO($email, $password);
            $result = $this->authService->login($dto);

            $this->lastToken = $result->token;
            $this->lastResult = 'success';
            $this->lastException = null;
        } catch (\Exception $e) {
            $this->lastToken = null;
            $this->lastResult = 'error';
            $this->lastException = $e;
        }
    }

    /**
     * @Then вход должен быть успешным
     */
    public function loginShouldBeSuccessful()
    {
        Assert::assertSame('success', $this->lastResult);
        Assert::assertNotNull($this->lastToken);
        Assert::assertNotEmpty($this->lastToken);
    }

    /**
     * @Then вход должен завершиться с ошибкой :errorMessage
     */
    public function loginShouldFailWithError(string $errorMessage)
    {
        Assert::assertSame('error', $this->lastResult);
        Assert::assertNotNull($this->lastException);
        Assert::assertStringContainsString($errorMessage, $this->lastException->getMessage());
    }

    /**
     * @Then я должен получить действительный токен
     */
    public function iShouldGetValidToken()
    {
        Assert::assertNotNull($this->lastToken);

        $user = $this->authService->getUserFromToken($this->lastToken);
        Assert::assertNotNull($user);
    }

    // ────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────

    /**
     * @Given в системе существует пользователь с email :email и паролем :password
     */
    public function userExistsInSystem(string $email, string $password)
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            $dto = new RegisterUserDTO('Test User', $email, $password);
            $user = $this->authService->register($dto);
            $this->testUsers[] = $email;
        }
        Assert::assertNotNull($user);
    }

    /**
     * @Given я очищаю тестовые данные
     */
    public function iCleanTestData()
    {
        $this->cleanUp();
    }
}