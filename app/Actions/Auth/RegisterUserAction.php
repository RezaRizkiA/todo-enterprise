<?php

namespace App\Actions\Auth;

use App\Services\Auth\AuthService;
use App\Models\User;

class RegisterUserAction
{
    private AuthService $authService;

    public function __construct(AuthService $authService) 
    {
        $this->authService = $authService;
    }

    public function register(array $validated): User
    {
        return $this->authService->register($validated);
    }
}
