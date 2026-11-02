<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Règle de mot de passe utilisée par l'inscription, le changement et la réinitialisation
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        $this->frenchAuthEmails();
        $this->logAuthenticationEvents();
    }

    private function frenchAuthEmails(): void
    {
        ResetPassword::toMailUsing(function (User $user, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]);
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Réinitialisation de votre mot de passe')
                ->greeting("Bonjour {$user->name},")
                ->line('Vous avez demandé à réinitialiser le mot de passe de votre compte TexTileCycle.')
                ->action('Choisir un nouveau mot de passe', $url)
                ->line("Ce lien expire dans {$minutes} minutes et ne peut servir qu’une fois.")
                ->line('Si vous n’êtes pas à l’origine de cette demande, ignorez cet e-mail : votre mot de passe reste inchangé.')
                ->salutation('L’équipe TexTileCycle');
        });

        VerifyEmail::toMailUsing(function (User $user, string $url) {
            $minutes = config('auth.verification.expire', 60);

            return (new MailMessage)
                ->subject('Confirmez votre adresse e-mail')
                ->greeting("Bienvenue {$user->name} !")
                ->line('Merci de rejoindre TexTileCycle. Confirmez votre adresse e-mail pour activer votre compte.')
                ->action('Confirmer mon adresse', $url)
                ->line("Ce lien est valable {$minutes} minutes.")
                ->salutation('L’équipe TexTileCycle');
        });
    }

    // Journal d'activité (visible dans le back office, fiche utilisateur)
    private function logAuthenticationEvents(): void
    {
        Event::listen(Registered::class, fn (Registered $event) => User::logActivity($event->user, 'Inscription', 'Compte créé par le citoyen.', $event->user));

        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
                User::logActivity($event->user, 'Connexion', 'Connexion réussie au compte.', $event->user);
            }
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user instanceof User) {
                User::logActivity($event->user, 'Déconnexion', 'Déconnexion du compte.', $event->user);
            }
        });

        Event::listen(Verified::class, fn (Verified $event) => User::logActivity($event->user, 'E-mail vérifié', 'Adresse e-mail confirmée.', $event->user));

        Event::listen(PasswordReset::class, fn (PasswordReset $event) => User::logActivity($event->user, 'Mot de passe réinitialisé', 'Mot de passe réinitialisé via le lien envoyé par e-mail.', $event->user));
    }
}
