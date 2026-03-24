<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Application\Auth\AuthService;
use RuntimeException;

class JwtMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $user = $this->authService->getUserFromToken($token);

            if (!$user) {
                return response()->json(['error' => 'Invalid or expired token'], 401);
            }

            // Привязываем пользователя к запросу
            $request->merge(['user' => $user]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        } catch (RuntimeException $e) {
            return response()->json(['error' => 'Token validation failed: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}
