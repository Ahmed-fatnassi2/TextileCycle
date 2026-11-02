@extends('layouts.front')

@section('title', 'Vérifiez votre adresse e-mail | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-xl px-5 py-12 sm:px-8 lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Dernière étape</p>
        <h1 class="mt-4 font-display text-4xl leading-tight text-slate-950">Confirmez votre adresse e-mail.</h1>
        <p class="mt-4 leading-7 text-slate-600">
            Nous avons envoyé un lien de confirmation à <strong>{{ auth()->user()->email }}</strong>.
            Cliquez dessus pour activer votre compte : dépôts, réparations et dons sont accessibles une fois l’adresse vérifiée.
        </p>

        <div class="mt-8 space-y-4 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            @if(session('status') === 'verification-link-sent')
                <p class="border-l-2 border-emerald-600 bg-emerald-50 p-3 text-sm text-emerald-900">Un nouveau lien vient de vous être envoyé.</p>
            @endif

            <p class="text-sm text-slate-600">Rien reçu ? Vérifiez vos courriers indésirables, ou demandez un nouveau lien.</p>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="min-h-12 w-full bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-800">Renvoyer le lien</button>
            </form>

            <div class="flex flex-wrap justify-between gap-3 border-t border-slate-100 pt-4 text-sm">
                <a href="{{ route('profile.edit') }}" class="font-semibold text-emerald-900 underline underline-offset-4">Corriger mon adresse</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="font-semibold text-slate-600 underline underline-offset-4">Se déconnecter</button></form>
            </div>
        </div>
    </section>
@endsection
