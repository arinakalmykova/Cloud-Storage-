<?php

namespace App\Application\Auth;

class LoginResult
{
    public string $userId;
    public string $token;

    public function __construct(string $userId, string $token)
    {
        $this->userId = $userId;
        $this->token = $token;
    }
}
