@extends('layouts.front')

@section('title', 'Créer un compte | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-12 sm:px-8 lg:grid lg:grid-cols-[0.9fr_1.1fr] lg:gap-16 lg:px-12 lg:py-20">
        <div class="mb-10 max-w-md lg:mb-0 lg:pt-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Rejoindre le réseau</p>
            <h1 class="mt-4 font-display text-4xl leading-tight text-slate-950">Chaque compte peut faire circuler le textile.</h1>
            <p class="mt-4 leading-7 text-slate-600">Créez votre compte, puis envoyez les informations de votre association pour validation.</p>
        </div>
        <form method="POST" action="{{ route('register.store') }}" class="space-y-5 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-semibold text-slate-800">Nom complet</label>
                <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('name')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-slate-800">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('email')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold text-slate-800">Mot de passe</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('password')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-800">Confirmer le mot de passe</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
            </div>
            <button class="min-h-12 w-full bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-800">Créer mon compte</button>
            <p class="text-center text-sm text-slate-600">Déjà inscrit ? <a class="font-semibold text-emerald-900 underline underline-offset-4" href="{{ route('login') }}">Se connecter</a></p>
        </form>
    </section>
@endsection