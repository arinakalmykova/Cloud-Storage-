<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Application\Auth\AuthService;
use Illuminate\Routing\Controller;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\DTOs\LoginUserDTO;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
         $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        $dto = new RegisterUserDTO($request->name, $request->email, $request->password);
        $user = $this->authService->register($dto);

        return response()->json([
            'userId' => $user->getId(),
            'name' => $user->getName(), 
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt(),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $dto = new LoginUserDTO($request->email, $request->password);
        $loginResult = $this->authService->login($dto);

        return response()->json([
            'userId' => $loginResult->userId,
            'token' => $loginResult->token,
        ]);
    }

    public function me(Request $request)
    {
        $token = $request->bearerToken();
        $user = $this->authService->getUserFromToken($token);

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt(),
        ]);
    }


    public function deleteUser(Request $request)
{
    $token = $request->bearerToken();
    $success = $this->authService->deleteUser($token);
    
    return response()->json([
        'success' => $success,
        'message' => $success ? 'Пользователь удален' : 'Ошибка удаления'
    ]);
}

    public function updateUser(Request $request)
    {
        $token = $request->bearerToken();
        $success = $this->authService->updateUser($token, $request->all());
        
        if ($success) {
            $user = $this->authService->getUserFromToken($token);
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'createdAt' => $user->getCreatedAt()
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Ошибка обновления'
        ], 400);
    }

}
