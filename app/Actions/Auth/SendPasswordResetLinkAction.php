<?php

namespace App\Actions\Auth;

use App\Services\Auth\AuthService;

class SendPasswordResetLinkAction
{
    public function __construct(private AuthService $auth) {}
    public function execute(string $email): string
    {
        return $this->auth->sendResetLink($email);
    }
}
