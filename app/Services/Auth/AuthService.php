<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\{Auth, DB, Hash, Log, Password, RateLimiter};
use Illuminate\Auth\Events\{Registered, PasswordReset};
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password as PasswordFacade;

class AuthService
{
    public function __construct(
        private StatefulGuard $guard,
        private UserRepository $userRepository,
        private Hasher $hasher,
        private PasswordBroker $passwords,
    ) {}

    public function register(array $data, bool $remember = false): Authenticatable
    {
        $user = DB::transaction(function () use ($data) {
            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $this->hasher->make($data['password']),
            ]);
            event(new Registered($user));
            return $user;
        });

        $this->guard->login($user, $remember);
        return $user;
    }

    public function login(string $email, string $password, bool $remember, string $ip): array
    {
        $key = $this->throttleKey($email, $ip);

        if (RateLimiter::tooManyAttempts($key, 5)) {
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

    private function throttleKey(string $email, string $ip): string
    {
        return Str::transliterate(Str::lower($email) . '|' . $ip);
    }

    public function logout(): void
    {
        $this->guard->logout();
    }


    public function sendResetLink(string $email): array
    {
        $status = $this->passwords->sendResetLink(['email' => $email]);
        return ['ok' => $status === PasswordFacade::RESET_LINK_SENT, 'status' => $status];
    }

    // public function resetPassword(array $data): string
    // {
    //     return Password::reset($data, function (User $user, string $password) {
    //         $this->users->updatePassword($user, Hash::make($password));
    //         $user->setRememberToken(Str::random(60));
    //         event(new PasswordReset($user));
    //     });
    // }
}
