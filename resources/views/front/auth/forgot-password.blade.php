@extends('layouts.front')

@section('title', 'Mot de passe oublié | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-xl px-5 py-12 sm:px-8 lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Mot de passe oublié</p>
        <h1 class="mt-4 font-display text-4xl leading-tight text-slate-950">Recevez un lien de réinitialisation.</h1>
        <p class="mt-4 leading-7 text-slate-600">
            Indiquez l’adresse e-mail de votre compte : nous vous envoyons un lien sécurisé pour choisir un nouveau mot de passe.
            Le lien expire au bout de {{ config('auth.passwords.users.expire') }} minutes.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            @csrf
            @if(session('status'))
                <p class="border-l-2 border-emerald-600 bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('status') }}</p>
            @endif
            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-slate-800">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('email')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <button class="min-h-12 w-full bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-800">Envoyer le lien</button>
            <p class="text-center text-sm text-slate-600"><a class="font-semibold text-emerald-900 underline underline-offset-4" href="{{ route('login') }}">← Retour à la connexion</a></p>
        </form>
    </section>
@endsection
