<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\DTOs\LoginUserDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Events\UserCreated;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class AuthService
{
    private UserRepositoryInterface $userRepository;
    private string $jwtSecret;

    public function __construct(UserRepositoryInterface $userRepository, string $jwtSecret)
    {
        $this->userRepository = $userRepository;
        $this->jwtSecret = $jwtSecret;
    }

    public function register(RegisterUserDTO $dto): User
    {
        $this->validatePasswordStrength($dto->password);
        $this->validateEmailUniqueness($dto->email);

        $user = new User(
            id: Str::uuid()->toString(),
            name: $dto->name,
            email: $dto->email,
            passwordHash: $this->hashPassword($dto->password),
            createdAt: now()->toDateTimeString()
        );

        $this->userRepository->save($user);

        UserCreated::dispatch($user->getId());

        return $user;
    }

    public function login(LoginUserDTO $dto): LoginResult
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !$user->verifyPassword($dto->password)) {
            throw new InvalidArgumentException('Invalid email or password');
        }

        $token = $this->generateJwtToken($user);

        return new LoginResult($user->getId(), $token);
    }

    private function generateJwtToken(User $user): string
    {
        if (empty($this->jwtSecret)) {
            throw new RuntimeException('JWT secret is not configured');
        }

        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function getUserFromToken(string $token): ?User
    {
        try {
            $payload = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userId = $payload->sub;

            return $this->userRepository->findById($userId);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function deleteUser(string $token): bool
    {
        $user = $this->getUserFromToken($token);

        if (!$user) {
            return false;
        }

        $this->userRepository->delete($user);

        return true;
    }

    public function updateUser(string $token, array $data): bool
    {
        $user = $this->getUserFromToken($token);

        if (!$user) {
            throw new RuntimeException('User not found or invalid token');
        }

        if (!isset($data['name']) || !isset($data['email'])) {
            throw new InvalidArgumentException('Name and email are required');
        }

        $user->setName($data['name']);
        $user->setEmail($data['email']);

        $this->userRepository->save($user);

        return true;
    }

    private function validatePasswordStrength(string $password): void
    {
        $errors = [];

        if (strlen($password) < 6) {
            $errors[] = 'at least 6 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'at least one lowercase letter';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'at least one number';
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'at least one special character';
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(
                'Password must contain: ' . implode(', ', $errors)
            );
        }
    }

    private function validateEmailUniqueness(string $email): void
    {
        $existingUser = $this->userRepository->findByEmail($email);

        if ($existingUser) {
            throw new InvalidArgumentException('User with this email already exists');
        }
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
