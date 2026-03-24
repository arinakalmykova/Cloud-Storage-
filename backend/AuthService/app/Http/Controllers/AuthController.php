<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Application\Auth\AuthService;
use Illuminate\Routing\Controller;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\LoginUserDTO;
use InvalidArgumentException;
use RuntimeException;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            $dto = new RegisterUserDTO(
                $validated['name'],
                $validated['email'],
                $validated['password']
            );

            $user = $this->authService->register($dto);

            return response()->json([
                'userId' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'createdAt' => $user->getCreatedAt(),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user and return JWT token
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $dto = new LoginUserDTO(
                $validated['email'],
                $validated['password']
            );

            $loginResult = $this->authService->login($dto);

            return response()->json([
                'userId' => $loginResult->userId,
                'token' => $loginResult->token,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 401);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            $user = $this->authService->getUserFromToken($token);

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json([
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'createdAt' => $user->getCreatedAt(),
            ], 200);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete current user
     */
    public function deleteUser(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            $success = $this->authService->deleteUser($token);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update current user
     */
    public function updateUser(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email',
            ]);

            $success = $this->authService->updateUser($token, $validated);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user'
                ], 400);
            }

            $user = $this->authService->getUserFromToken($token);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'createdAt' => $user->getCreatedAt()
                ]
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
