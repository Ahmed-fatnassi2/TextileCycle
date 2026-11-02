@extends('layouts.front')

@section('title', 'Double authentification | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-xl px-5 py-12 sm:px-8 lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Double authentification</p>
        <h1 class="mt-4 font-display text-4xl leading-tight text-slate-950">Encore une étape.</h1>
        <p class="mt-4 leading-7 text-slate-600">Ouvrez votre application d’authentification (Google Authenticator, Microsoft Authenticator…) et saisissez le code à 6 chiffres.</p>

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-8 space-y-5 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            @csrf

            <div data-2fa-code>
                <label for="code" class="mb-1 block text-sm font-semibold text-slate-800">Code de l’application</label>
                <input id="code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" autofocus
                       class="w-full border border-slate-300 px-3 py-2.5 text-center font-mono text-2xl tracking-[0.5em] focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('code')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>

            {{-- Téléphone perdu : un code de récupération (usage unique) remplace le code de l'application --}}
            <details class="text-sm" @if($errors->has('recovery_code')) open @endif>
                <summary class="cursor-pointer font-semibold text-emerald-900">Utiliser un code de récupération</summary>
                <div class="mt-3">
                    <label for="recovery_code" class="mb-1 block text-sm font-semibold text-slate-800">Code de récupération</label>
                    <input id="recovery_code" name="recovery_code" autocomplete="one-time-code"
                           class="w-full border border-slate-300 px-3 py-2.5 font-mono focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                    @error('recovery_code')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
            </details>

            <button class="min-h-12 w-full bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-800">Valider</button>
        </form>
    </section>
@endsection
