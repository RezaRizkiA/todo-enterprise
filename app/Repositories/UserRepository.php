<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $attributes): Authenticatable
    {
        return User::create($attributes);
    }
}