<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class UserRepository
{
    public function findByEmail(string $email): ?Authenticatable
    {
        return User::where('email', $email)->first();
    }
}