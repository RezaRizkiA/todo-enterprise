<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\{Auth, Hash, Password};
use Illuminate\Auth\Events\{Registered, PasswordReset};
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function register(array $data): User
    {
        $user = $this->users->create([
            'name'     => $data['name'],
            'email'    => strtolower($data['email']),
            'password' => Hash::make($data['password']),
        ]);
        // event(new Registered($user));
        return $user;
    }

    public function login(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt(['email' => $email, 'password' => $password], $remember);
    }

    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset($data, function (User $user, string $password) {
            $this->users->updatePassword($user, Hash::make($password));
            $user->setRememberToken(Str::random(60));
            event(new PasswordReset($user));
        });
    }
}
