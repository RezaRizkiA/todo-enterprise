<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class UserRepository
{
    /**
     * @return User|null
     */
    public function findByEmail(string $email): ?Authenticatable
    {
        return User::where('email', $email)->first();
    }

    /** @return User */
    public function create(array $attributes): Authenticatable
    {
        return User::create($attributes);
    }
}