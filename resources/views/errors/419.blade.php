@extends('layouts.front')

@section('title', 'Session expirée | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-xl px-5 py-20 text-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-amber-700">Erreur 419</p>
        <h1 class="mt-4 font-display text-4xl text-slate-950">Votre session a expiré</h1>
        <p class="mt-4 leading-7 text-slate-600">Par sécurité, le formulaire n’a pas été envoyé. Rechargez la page et recommencez.</p>
        <a href="{{ url()->previous() }}" class="mt-8 inline-block bg-emerald-900 px-5 py-2.5 font-semibold text-white hover:bg-emerald-800">Revenir à la page précédente</a>
    </section>
@endsection
