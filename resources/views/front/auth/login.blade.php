@extends('layouts.front')

@section('title', 'Connexion | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12 sm:px-8 lg:grid lg:grid-cols-2 lg:gap-16 lg:px-12 lg:py-20">
        <div class="mb-10 max-w-md lg:mb-0 lg:pt-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Votre espace</p>
            <h1 class="mt-4 font-display text-4xl leading-tight text-slate-950">Reprenons le fil.</h1>
            <p class="mt-4 leading-7 text-slate-600">Connectez-vous pour suivre votre demande ou gérer vos dons.</p>
        </div>
        <form method="POST" action="{{ route('login.store') }}" class="space-y-5 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-slate-800">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('email')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold text-slate-800">Mot de passe</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('password')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="remember" value="1" class="border-slate-300 text-emerald-800 focus:ring-emerald-700">Rester connecté</label>
            <button class="min-h-12 w-full bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-800">Se connecter</button>
            <p class="text-center text-sm text-slate-600">Pas encore de compte ? <a class="font-semibold text-emerald-900 underline underline-offset-4" href="{{ route('register') }}">Créer un compte</a></p>
        </form>
    </section>
@endsection