@extends('layouts.front')

@section('title', 'Accès refusé | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-xl px-5 py-20 text-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-rose-700">Erreur 403</p>
        <h1 class="mt-4 font-display text-4xl text-slate-950">Accès refusé</h1>
        <p class="mt-4 leading-7 text-slate-600">
            {{ $exception->getMessage() && ! str_starts_with($exception->getMessage(), 'User does not have')
                ? $exception->getMessage()
                : 'Votre compte n’a pas la permission d’ouvrir cette page. Si vous pensez que c’est une erreur, contactez un administrateur.' }}
        </p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="{{ route('home') }}" class="bg-emerald-900 px-5 py-2.5 font-semibold text-white hover:bg-emerald-800">Retour à l’accueil</a>
            @auth
                <a href="{{ route('profile.edit') }}" class="border border-slate-300 px-5 py-2.5 font-semibold text-slate-700 hover:bg-white">Mon profil</a>
            @endauth
        </div>
    </section>
@endsection
