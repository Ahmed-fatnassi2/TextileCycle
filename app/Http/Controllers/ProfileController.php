<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Page "Mon profil". Les informations, le mot de passe et la double authentification sont traités
 * par les routes de Fortify ; ce contrôleur gère la page, la photo et la suppression du compte.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $securityUnlocked = $this->passwordRecentlyConfirmed($request);

        return view('front.profile.edit', [
            'user' => $user,
            'securityUnlocked' => $securityUnlocked,
            'qrCodeSvg' => $securityUnlocked && $user->two_factor_secret ? $user->twoFactorQrCodeSvg() : null,
            'secretKey' => $securityUnlocked && $user->two_factor_secret ? decrypt($user->two_factor_secret) : null,
            'recoveryCodes' => $securityUnlocked && $user->two_factor_recovery_codes ? $user->recoveryCodes() : [],
        ]);
    }

    // Demande le mot de passe (sauf s'il vient d'être confirmé), puis revient sur la section sécurité du profil
    public function confirmSecurity(Request $request): RedirectResponse
    {
        $securityUrl = route('profile.edit').'#securite';

        if ($this->passwordRecentlyConfirmed($request)) {
            return redirect()->to($securityUrl);
        }

        $request->session()->put('url.intended', $securityUrl);

        return redirect()->route('password.confirm');
    }

    // Même délai que le middleware "password.confirm" de Laravel / Fortify
    private function passwordRecentlyConfirmed(Request $request): bool
    {
        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);

        return time() - $confirmedAt < (int) config('auth.password_timeout', 10800);
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validateWithBag('updatePhoto', [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $old = $user->profile_photo_path;

        $user->forceFill(['profile_photo_path' => $request->file('photo')->store('profile-photos', 'local')])->save();

        if ($old) {
            Storage::disk('local')->delete($old);
        }

        User::logActivity($user, 'Photo de profil', 'Photo de profil mise à jour.', $user);

        return redirect()->route('profile.edit')->with('status', 'photo-updated');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('local')->delete($user->profile_photo_path);
            $user->forceFill(['profile_photo_path' => null])->save();
        }

        return redirect()->route('profile.edit')->with('status', 'photo-deleted');
    }

    // Photos stockées hors du dossier public : servies uniquement aux utilisateurs connectés
    public function photo(User $user): StreamedResponse
    {
        abort_unless($user->profile_photo_path && Storage::disk('local')->exists($user->profile_photo_path), 404);

        return Storage::disk('local')->response($user->profile_photo_path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('deleteUser', [
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Saisissez votre mot de passe pour confirmer la suppression.',
            'password.current_password' => 'Le mot de passe est incorrect.',
        ]);

        $user = $request->user();

        // Il doit toujours rester au moins un administrateur
        if ($user->isAdmin() && User::role(User::ROLE_ADMIN)->count() === 1) {
            return back()->withErrors(['password' => 'Vous êtes le dernier administrateur : nommez-en un autre avant de supprimer votre compte.'], 'deleteUser');
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Votre compte a été supprimé. Merci d’avoir participé à TexTileCycle.');
    }
}
