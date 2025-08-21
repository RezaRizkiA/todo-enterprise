<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthAction;
use App\Actions\Auth\LoginUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, AuthAction $authAction): RedirectResponse
    {
        return $authAction->login($request);
    }

    public function destroy(Request $request, AuthAction $authAction): RedirectResponse
    {
        return $authAction->logout($request);
    }
}
