@extends('layouts.front')

@section('title', 'Mon profil')

@php
    $messages = [
        'profile-information-updated' => 'Vos informations ont été mises à jour.',
        'password-updated' => 'Votre mot de passe a été modifié.',
        'photo-updated' => 'Votre photo de profil a été mise à jour.',
        'photo-deleted' => 'Votre photo de profil a été supprimée.',
        'verification-link-sent' => 'Un lien de confirmation vient d’être envoyé à votre adresse e-mail.',
        'two-factor-authentication-enabled' => 'Scannez le QR code puis saisissez le code affiché pour terminer l’activation.',
        'two-factor-authentication-confirmed' => 'Double authentification activée. Conservez vos codes de récupération.',
        'two-factor-authentication-disabled' => 'Double authentification désactivée.',
        'recovery-codes-generated' => 'De nouveaux codes de récupération ont été générés : les anciens ne fonctionnent plus.',
    ];
    $input = 'w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700/15';
@endphp

@section('content')
    <section class="mx-auto max-w-3xl space-y-6 px-4 py-12">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Mon compte</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">Mon profil</h1>
        </div>

        @if(isset($messages[session('status')]))
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">{{ $messages[session('status')] }}</div>
        @endif

        {{-- ===== En-tête + photo ===== --}}
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="flex flex-wrap items-center gap-5">
                @include('partials.avatar', ['user' => $user, 'class' => 'h-20 w-20 text-2xl'])
                <div class="flex-1">
                    <p class="text-lg font-semibold text-slate-900">{{ $user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-slate-700">{{ $user->roleLabel() }}</span>
                        @if($user->hasVerifiedEmail())
                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-emerald-800">✔ E-mail vérifié</span>
                        @else
                            <span class="rounded bg-amber-100 px-2 py-0.5 text-amber-800">E-mail à vérifier</span>
                        @endif
                        @if($user->hasTwoFactorEnabled())
                            <span class="rounded bg-violet-100 px-2 py-0.5 text-violet-800">🔒 Double authentification</span>
                        @endif
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
                @csrf
                <label for="photo" class="text-sm font-medium text-slate-700">Photo de profil</label>
                <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="text-sm">
                <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">Envoyer</button>
                <span class="text-xs text-slate-500">JPG, PNG ou WebP · 2 Mo max</span>
                @error('photo', 'updatePhoto')<p class="w-full text-sm text-red-600">{{ $message }}</p>@enderror
            </form>
            @if($user->profile_photo_path)
                <form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-2">
                    @csrf @method('DELETE')
                    <button class="text-sm text-rose-700 hover:underline">Supprimer la photo</button>
                </form>
            @endif
        </div>

        {{-- ===== Informations (Fortify) ===== --}}
        <div class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-bold">Informations personnelles</h2>
            <p class="mb-4 text-sm text-slate-500">Si vous changez d’adresse e-mail, il faudra la confirmer à nouveau.</p>
            <form method="POST" action="{{ route('user-profile-information.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium">Nom complet</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="{{ $input }}">
                    @error('name', 'updateProfileInformation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="{{ $input }}">
                    @error('email', 'updateProfileInformation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button class="rounded-lg bg-emerald-700 px-5 py-2 font-medium text-white hover:bg-emerald-800">Enregistrer</button>
            </form>
            @unless($user->hasVerifiedEmail())
                <form method="POST" action="{{ route('verification.send') }}" class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">
                    @csrf
                    Votre adresse n’est pas encore confirmée.
                    <button class="font-semibold underline">Renvoyer le lien de confirmation</button>
                </form>
            @endunless
        </div>

        {{-- ===== Mot de passe (Fortify) ===== --}}
        <div class="rounded-xl bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-bold">Mot de passe</h2>
            <form method="POST" action="{{ route('user-password.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label for="current_password" class="mb-1 block text-sm font-medium">Mot de passe actuel</label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password" class="{{ $input }}">
                    @error('current_password', 'updatePassword')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium">Nouveau mot de passe</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="{{ $input }}">
                        @error('password', 'updatePassword')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirmation</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="{{ $input }}">
                    </div>
                </div>
                <p class="text-xs text-slate-500">8 caractères minimum, avec au moins une lettre et un chiffre.</p>
                <button class="rounded-lg bg-emerald-700 px-5 py-2 font-medium text-white hover:bg-emerald-800">Changer le mot de passe</button>
            </form>
        </div>

        {{-- ===== Double authentification (Fortify, TOTP) ===== --}}
        <div id="securite" class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-bold">Double authentification (2FA)</h2>
            <p class="mb-4 text-sm text-slate-500">
                En plus du mot de passe, un code à 6 chiffres généré par une application (Google Authenticator, Microsoft Authenticator…) est demandé à la connexion.
            </p>

            @if(! $securityUnlocked)
                <a href="{{ route('profile.security') }}" class="inline-block rounded-lg bg-slate-800 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    {{ $user->hasTwoFactorEnabled() ? 'Gérer la double authentification' : 'Activer la double authentification' }}
                </a>
                <p class="mt-2 text-xs text-slate-500">Votre mot de passe vous sera demandé par sécurité.</p>

            @elseif(! $user->two_factor_secret)
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button class="rounded-lg bg-violet-700 px-5 py-2 text-sm font-medium text-white hover:bg-violet-800">Activer la double authentification</button>
                </form>

            @elseif(! $user->two_factor_confirmed_at)
                {{-- Étape 2 : scanner le QR code puis confirmer avec un premier code --}}
                <div class="grid gap-6 sm:grid-cols-[auto_1fr]">
                    <div class="w-48 rounded-lg border border-slate-200 bg-white p-3 [&>svg]:h-auto [&>svg]:w-full">{!! $qrCodeSvg !!}</div>
                    <div class="space-y-3 text-sm">
                        <p>1. Scannez ce QR code avec votre application d’authentification.</p>
                        <p>Ou saisissez la clé manuellement : <code class="break-all rounded bg-slate-100 px-1.5 py-0.5 font-mono">{{ $secretKey }}</code></p>
                        <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-2">
                            @csrf
                            <label for="code" class="block">2. Saisissez le code affiché par l’application :</label>
                            <div class="flex gap-2">
                                <input id="code" name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required class="w-36 rounded-lg border border-slate-300 px-3 py-2 text-center font-mono text-lg tracking-widest">
                                <button class="rounded-lg bg-violet-700 px-4 py-2 font-medium text-white hover:bg-violet-800">Confirmer</button>
                            </div>
                            @error('code', 'confirmTwoFactorAuthentication')<p class="text-red-600">{{ $message }}</p>@enderror
                        </form>
                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf @method('DELETE')
                            <button class="text-slate-500 hover:underline">Annuler</button>
                        </form>
                    </div>
                </div>

            @else
                <p class="mb-4 rounded-lg bg-violet-50 px-3 py-2 text-sm font-medium text-violet-900">🔒 Activée depuis le {{ $user->two_factor_confirmed_at->format('d/m/Y') }}.</p>

                <details class="mb-4 text-sm" @if(in_array(session('status'), ['two-factor-authentication-confirmed', 'recovery-codes-generated'])) open @endif>
                    <summary class="cursor-pointer font-semibold text-slate-800">Codes de récupération</summary>
                    <p class="mt-2 text-slate-500">Chaque code permet une connexion si vous perdez votre téléphone. Gardez-les en lieu sûr.</p>
                    <div class="mt-2 grid grid-cols-2 gap-1 rounded-lg bg-slate-50 p-3 font-mono text-xs sm:grid-cols-4">
                        @foreach($recoveryCodes as $code)<span>{{ $code }}</span>@endforeach
                    </div>
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="mt-2">
                        @csrf
                        <button class="text-sm font-semibold text-violet-800 hover:underline">Générer de nouveaux codes</button>
                    </form>
                </details>

                <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Désactiver la double authentification ?')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg border border-rose-300 px-5 py-2 text-sm font-medium text-rose-800 hover:bg-rose-50">Désactiver la double authentification</button>
                </form>
            @endif
        </div>

        {{-- ===== Suppression du compte ===== --}}
        <div class="rounded-xl border border-rose-200 bg-white p-6 shadow">
            <h2 class="text-lg font-bold text-rose-900">Supprimer mon compte</h2>
            <p class="mb-4 text-sm text-slate-600">Action définitive : votre compte, vos dépôts et vos demandes seront supprimés.</p>
            <form method="POST" action="{{ route('profile.destroy') }}" class="flex flex-wrap items-start gap-3" onsubmit="return confirm('Supprimer définitivement votre compte ?')">
                @csrf @method('DELETE')
                <div>
                    <label for="delete_password" class="sr-only">Mot de passe</label>
                    <input id="delete_password" type="password" name="password" required placeholder="Votre mot de passe" autocomplete="current-password" class="rounded-lg border border-slate-300 px-3 py-2">
                    @error('password', 'deleteUser')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button class="rounded-lg bg-rose-700 px-5 py-2 font-medium text-white hover:bg-rose-800">Supprimer définitivement</button>
            </form>
        </div>
    </section>
@endsection
