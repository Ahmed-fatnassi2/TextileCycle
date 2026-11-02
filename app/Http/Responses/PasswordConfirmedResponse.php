<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Après la confirmation du mot de passe : retour à la page demandée.
 * Si cette page est une action Fortify (/user/..., accessible seulement en POST/DELETE, ex. activer la 2FA),
 * on revient sur la section sécurité du profil au lieu d'y rediriger en GET (erreur 405).
 */
class PasswordConfirmedResponse implements PasswordConfirmedResponseContract
{
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $intended = $request->session()->pull('url.intended');
        $path = $intended ? (string) parse_url($intended, PHP_URL_PATH) : '';

        return $intended && ! str_starts_with($path, '/user/')
            ? redirect()->to($intended)
            : redirect()->to(route('profile.edit').'#securite');
    }
}
