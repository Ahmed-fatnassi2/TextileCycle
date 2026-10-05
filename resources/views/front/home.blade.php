@extends('layouts.front')

@section('title', 'TexTileCycle | Le textile continue')

@section('content')
    <section class="relative isolate flex min-h-[66svh] items-end overflow-hidden bg-slate-950 text-white">
        <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=2200&q=85"
             alt="Vêtements soigneusement suspendus, prêts à être réemployés"
             class="absolute inset-0 -z-20 h-full w-full object-cover object-center">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/90 via-slate-950/55 to-slate-950/10"></div>

        <div class="mx-auto w-full max-w-7xl px-5 pb-14 pt-24 sm:px-8 sm:pb-20 lg:px-12">
            <p class="mb-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.16em] text-lime-300">
                <span class="h-px w-10 bg-lime-300"></span>La seconde vie commence ici
            </p>
            <h1 class="max-w-3xl font-display text-5xl leading-[1.02] sm:text-6xl lg:text-7xl">Le textile<br class="hidden sm:block"> continue.</h1>
            <p class="mt-6 max-w-xl text-base leading-7 text-white/85 sm:text-lg">Déposez les vêtements que vous ne portez plus. Nous les orientons vers les associations qui en ont besoin.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('deposits.index') }}" class="inline-flex min-h-12 items-center justify-center bg-lime-300 px-6 font-semibold text-slate-950 transition hover:bg-lime-200">Trouver un point de collecte</a>
                @auth
                    @if(auth()->user()->association?->status === \App\Models\Association::STATUS_APPROVED)
                        <a href="{{ route('association.donations.create') }}" class="inline-flex min-h-12 items-center justify-center border border-white/70 px-6 font-semibold text-white transition hover:bg-white hover:text-slate-950">Demander un don</a>
                    @elseif(auth()->user()->association)
                        <a href="{{ route('association.status') }}" class="inline-flex min-h-12 items-center justify-center border border-white/70 px-6 font-semibold text-white transition hover:bg-white hover:text-slate-950">Suivre ma demande</a>
                    @else
                        <a href="{{ route('associations.create') }}" class="inline-flex min-h-12 items-center justify-center border border-white/70 px-6 font-semibold text-white transition hover:bg-white hover:text-slate-950">Rejoindre comme association</a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center border border-white/70 px-6 font-semibold text-white transition hover:bg-white hover:text-slate-950">Rejoindre comme association</a>
                @endauth
            </div>
            <div class="mt-12 flex items-center gap-3 text-sm text-white/75"><span class="h-2 w-2 rounded-full bg-lime-300"></span>Une collecte locale, un impact qui circule.</div>
        </div>
    </section>

    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:grid-cols-3 sm:px-8 lg:px-12">
            <article class="border-l-2 border-lime-400 pl-5"><p class="text-sm font-semibold uppercase tracking-wide text-slate-500">01 / Déposer</p><h2 class="mt-2 text-xl font-semibold text-slate-900">Un point près de vous</h2><p class="mt-2 text-sm leading-6 text-slate-600">Repérez un lieu de collecte ouvert et donnez une suite à vos vêtements.</p></article>
            <article class="border-l-2 border-coral-500 pl-5"><p class="text-sm font-semibold uppercase tracking-wide text-slate-500">02 / Relier</p><h2 class="mt-2 text-xl font-semibold text-slate-900">Des besoins concrets</h2><p class="mt-2 text-sm leading-6 text-slate-600">Les associations partagent leurs besoins et demandent des dons adaptés.</p></article>
            <article class="border-l-2 border-sky-500 pl-5"><p class="text-sm font-semibold uppercase tracking-wide text-slate-500">03 / Réemployer</p><h2 class="mt-2 text-xl font-semibold text-slate-900">Moins de textile jeté</h2><p class="mt-2 text-sm leading-6 text-slate-600">Chaque pièce réutilisée prolonge son histoire et soutient le tissu local.</p></article>
        </div>
    </section>

    <section class="bg-[#e9f1ed]">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-5 py-14 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-12">
            <div class="max-w-2xl"><p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Associations partenaires</p><h2 class="mt-3 font-display text-3xl leading-tight text-slate-950 sm:text-4xl">Vos besoins peuvent trouver leur réponse.</h2><p class="mt-3 max-w-xl leading-7 text-slate-700">Créez votre compte, présentez votre association et, après validation, demandez les dons dont votre communauté a besoin.</p></div>
            @guest
                <a href="{{ route('register') }}" class="inline-flex min-h-12 shrink-0 items-center justify-center bg-slate-950 px-6 font-semibold text-white transition hover:bg-emerald-900">Créer un compte</a>
            @else
                <a href="{{ route('associations.create') }}" class="inline-flex min-h-12 shrink-0 items-center justify-center bg-slate-950 px-6 font-semibold text-white transition hover:bg-emerald-900">{{ auth()->user()->association ? 'Voir ma demande' : 'Présenter mon association' }}</a>
            @endguest
        </div>
    </section>
@endsection