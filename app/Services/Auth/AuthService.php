<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\{Auth, Hash, Log, Password, RateLimiter};
use Illuminate\Auth\Events\{Registered, PasswordReset};
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private StatefulGuard $guard,
        private UserRepository $userRepository,
        private Hasher $hasher
    ) {}

    // public function register(array $data): User
    // {
    //     $user = $this->users->create([
    //         'name'     => $data['name'],
    //         'email'    => strtolower($data['email']),
    //         'password' => Hash::make($data['password']),
    //     ]);
    //     // event(new Registered($user));
    //     return $user;
    // }

    public function login(string $email, string $password, bool $remember, string $ip): array
    {
        $key = $this->throttleKey($email, $ip);

        if(RateLimiter::tooManyAttempts($key, 5)){
            return ['ok' => false, 'reason' => 'throttled', 'seconds' => RateLimiter::availableIn($key)];
        }
        
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            RateLimiter::hit($key);
            return ['ok' => false, 'reason' => 'email_or_password'];
        }

        if (!$this->hasher->check($password, $user->getAuthPassword())) {
            RateLimiter::hit($key);
            return ['ok' => false, 'reason' => 'email_or_password'];
        }

        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            return ['ok' => false, 'reason' => 'unverified'];
        }

        $this->guard->login($user, $remember);
        RateLimiter::clear($key);
        Log::info('user.login', [
            'user_id' => $user->getAuthIdentifier(),
            'email'   => $email,
            'remember' => $remember,
            'at'      => now()->toISOString(),
        ]);
        return ['ok' => true];
    }

    public function logout(): void
    {
        $this->guard->logout();
    }

    private function throttleKey(string $email, string $ip): string
    {
        return Str::transliterate(Str::lower($email). '|' .$ip);
    }

    // public function sendResetLink(string $email): string
    // {
    //     return Password::sendResetLink(['email' => $email]);
    // }

    // public function resetPassword(array $data): string
    // {
    //     return Password::reset($data, function (User $user, string $password) {
    //         $this->users->updatePassword($user, Hash::make($password));
    //         $user->setRememberToken(Str::random(60));
    //         event(new PasswordReset($user));
    //     });
    // }
}
