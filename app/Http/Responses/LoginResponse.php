<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

// Après la connexion (avec ou sans code 2FA) : chaque rôle arrive dans son espace.
class LoginResponse implements LoginResponseContract, TwoFactorLoginResponseContract
{
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false]);
        }

        $user = $request->user();

        if ($user->can('admin.access')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole(User::ROLE_WORKSHOP_OWNER)) {
            return redirect()->route('workshop-owner.dashboard');
        }

        return redirect()->intended(route('home'));
    }
}
