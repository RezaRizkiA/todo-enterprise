<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\NewPasswordRequest;
use App\Http\Requests\Auth\PasswordResetLinkRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthAction
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $result = $this->authService->login(
            email: $data['email'],
            password: $data['password'],
            remember: (bool) ($data['remember'] ?? false),
            ip: $request->ip()
        );

        if (!$result['ok']) {
            $message = match ($result['reason'] ?? null) {
                'throttled' => trans('auth.throttle', [
                    'seconds' => $result['seconds'] ?? 60,
                    'minutes' => ceil(($result['seconds'] ?? 60) / 60),
                ]),
                'email_or_password'         => __('auth.failed'),
                'unverified'    => __('Your email is not verified'),
                default         => __('Unable to login.')
            };
            return back()->withErrors(['email' => $message])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();
        if ($user && $user->hasRole('admin')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
        return redirect()->intended(route('home', absolute: false));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->authService->register($data);
        $request->session()->regenerate();
        $user = $request->user();
        if ($user && $user->hasRole('admin')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
        return redirect()->intended(route('home', absolute: false));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function sendPasswordResetLink(PasswordResetLinkRequest $request): RedirectResponse
    {
        $email = (string) $request->validated('email');
        $result = $this->authService->sendResetLink($email);

        if ($result['ok']) {
            return back()->with('status', __($result['status']));
        }
        return back()->withErrors(['email' => __($result['status'])]);
    }

    public function resetPassword(NewPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $result = $this->authService->resetPassword(
            email: $data['email'],
            token: $data['token'],
            newPassword: $data['password'],
            passwordConfimation: (string) $request->input('password_confimation')
        );

        if ($result['ok']) {
            return to_route('login')->with('status', __($result['status']));
        }
        return back()->withErrors(['email' => __($result['status'])]);
    }
}
