<?php

namespace App\Actions\Auth;

use App\Services\Auth\AuthService;
use App\Models\User;

class RegisterUserAction
{
    public function __construct(private AuthService $auth) {}
    public function execute(array $validated): User
    {
        return $this->auth->register($validated);
    }
}
