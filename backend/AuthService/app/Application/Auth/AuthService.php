<?php

namespace App\Application\Auth;

use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Entities\User;
use App\Application\Auth\LoginResult;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\LoginUserDTO;
use App\Events\UserCreated;

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
        $user = new User(
            id: Str::uuid()->toString(),
            name: $dto->name,
            email: $dto->email,
            passwordHash: password_hash($dto->password, PASSWORD_BCRYPT)
        );
        $this->userRepository->save($user);

      UserCreated::dispatch($user->getId());

        return $user;
    }

    public function login(LoginUserDTO $dto): LoginResult
    {
        $user = $this->userRepository->findByEmail($dto->email);
        if (!$user || !$user->verifyPassword($dto->password)) {
            throw new \Exception('Invalid credentials');
        }

        $token = $this->generateJWT($user);

        return new LoginResult($user->getId(), $token);
    }

    private function generateJWT(User $user): string
    {
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function getUserFromToken(string $token)
    {
        try {
            $payload = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userId = $payload->sub;

            return $this->userRepository->findById($userId); // убедись, что findById умеет работать с UUID
        } catch (\Exception $e) {
            return null;
        }
    }


}
