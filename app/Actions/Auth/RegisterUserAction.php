<?php

namespace App\Actions\Auth;

use App\Services\Auth\AuthService;
use App\Models\User;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    public function __construct(
        private AuthService $authService,
        private ProfileRepositoryInterface $profiles
    ) {}

    public function register(array $validated): User
    {
        return DB::transaction(function () use ($validated) {
            $user = $this->authService->register($validated);
            $this->profiles->createDefaultFor($user);
            $user->assignRole('member');
            DB::afterCommit(function() use ($user) {
                event(new Registered($user));
            });
            return $user;
        });
    }
}
