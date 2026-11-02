<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\PasswordConfirmedResponse;
use App\Http\Responses\RegisterResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;

/**
 * Laravel Fortify : authentification côté serveur (connexion, inscription, mot de passe oublié,
 * vérification d'email, double authentification). Fortify n'a pas d'interface : les pages sont
 * les vues Blade du site, déclarées ci-dessous.
 */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Redirection selon le rôle après la connexion (avec ou sans 2FA) et après l'inscription
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(PasswordConfirmedResponseContract::class, PasswordConfirmedResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Pages (vues Blade du site)
        Fortify::loginView(fn () => view('front.auth.login'));
        Fortify::registerView(fn () => view('front.auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('front.auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('front.auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('front.auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('front.auth.confirm-password'));
        Fortify::twoFactorChallengeView(fn () => view('front.auth.two-factor-challenge'));

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
