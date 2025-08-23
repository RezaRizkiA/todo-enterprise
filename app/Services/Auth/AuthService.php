<?php

namespace App\Services\Auth;

use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\{DB, Log, RateLimiter};
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
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
            $user->assignRole('member');
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

    public function resetPassword(
        string $email, string $token, string $newPassword, string $passwordConfimation
    ): array
    {
        $status = $this->passwords->reset(
            [
                'email' => $email,
                'token' => $token,
                'password' => $newPassword,
                'confirmation_password' => $passwordConfimation
            ],
            function (CanResetPassword $user) use($newPassword){
                $user->forceFill([
                    'password' => $this->hasher->make($newPassword),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return['ok' => $status === PasswordFacade::PASSWORD_RESET, 'status' => $status];
    }
}
