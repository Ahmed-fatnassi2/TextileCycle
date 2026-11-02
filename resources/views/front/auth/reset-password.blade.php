@extends('layouts.front')

@section('title', 'Nouveau mot de passe | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-xl px-5 py-12 sm:px-8 lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Réinitialisation</p>
        <h1 class="mt-4 font-display text-4xl leading-tight text-slate-950">Choisissez un nouveau mot de passe.</h1>

        <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            @csrf
            {{-- Jeton reçu par e-mail : vérifié et à usage unique, il expire après le délai configuré --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-slate-800">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('email')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold text-slate-800">Nouveau mot de passe</label>
                <input id="password" type="password" name="password" required autofocus autocomplete="new-password" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                <p class="mt-1 text-xs text-slate-500">8 caractères minimum, avec au moins une lettre et un chiffre.</p>
                @error('password')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-800">Confirmer le mot de passe</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
            </div>
            <button class="min-h-12 w-full bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-800">Enregistrer le nouveau mot de passe</button>
        </form>
    </section>
@endsection
