<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\LoginRequest;
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
        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $this->authService->register($data);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
