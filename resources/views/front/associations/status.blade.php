@extends('layouts.front')

@section('title', 'Mon association | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-4xl px-5 py-12 sm:px-8 lg:px-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Espace association</p>
        <h1 class="mt-3 font-display text-4xl text-slate-950">{{ $association->name }}</h1>
        <div class="mt-8 border-l-4 {{ $association->status === \App\Models\Association::STATUS_APPROVED ? 'border-emerald-600 bg-emerald-50' : ($association->status === \App\Models\Association::STATUS_REJECTED ? 'border-rose-500 bg-rose-50' : 'border-amber-500 bg-amber-50') }} p-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-slate-600">Statut de la demande</p>
            <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $association->status }}</p>
            <p class="mt-2 leading-7 text-slate-700">
                @if($association->status === \App\Models\Association::STATUS_APPROVED)
                    Votre profil est approuvé. Vous pouvez maintenant créer et suivre vos demandes de dons.
                @elseif($association->status === \App\Models\Association::STATUS_REJECTED)
                    Votre demande n’a pas été approuvée. Contactez notre équipe pour obtenir plus d’informations.
                @else
                    Votre dossier est en cours d’examen. Nous vous informerons dès qu’une décision sera prise.
                @endif
            </p>
        </div>
        @if($association->status === \App\Models\Association::STATUS_APPROVED)
            <a href="{{ route('association.donations.index') }}" class="mt-6 inline-flex min-h-12 items-center bg-emerald-900 px-6 font-semibold text-white hover:bg-emerald-800">Accéder à mes dons</a>
        @endif
    </section>
@endsection