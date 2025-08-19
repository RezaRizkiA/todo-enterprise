<?php

namespace App\Actions\Auth;

use App\Services\Auth\AuthService;

class AuthenticateUserAction
{
    public function __construct(private AuthService $auth) {}
    public function execute(string $email, string $password, bool $remember = false): bool
    {
        return $this->auth->login($email, $password, $remember);
    }
}
