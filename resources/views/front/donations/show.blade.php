@extends('layouts.front')

@section('title', 'Détail de la demande')

@section('content')
    <section class="mx-auto max-w-4xl px-5 py-12 sm:px-8 lg:px-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Espace association</p>
        <div class="mt-3 flex flex-wrap items-start justify-between gap-4"><div><h1 class="font-display text-4xl text-slate-950">Demande de don</h1><p class="mt-2 text-slate-600">Créée le {{ $donation->created_at->format('d/m/Y') }}</p></div><span class="border border-sky-200 bg-sky-50 px-3 py-1.5 text-sm font-semibold text-sky-900">{{ $donation->status }}</span></div>
<<<<<<< HEAD
        <dl class="mt-8 grid gap-6 border-y border-slate-200 bg-white p-6 sm:grid-cols-2"><div><dt class="text-sm text-slate-500">Nombre de pièces</dt><dd class="mt-1 text-2xl font-semibold text-slate-950">{{ $donation->quantity_items }}</dd></div><div><dt class="text-sm text-slate-500">Date souhaitée</dt><dd class="mt-1 text-lg font-semibold text-slate-950">{{ $donation->donation_date->format('d/m/Y') }}</dd></div></dl>
=======
        <dl class="mt-8 grid gap-6 border-y border-slate-200 bg-white p-6 sm:grid-cols-2"><div><dt class="text-sm text-slate-500">Référence</dt><dd class="mt-1 font-mono font-semibold text-slate-950">{{ $donation->trackingReference() }}</dd></div><div><dt class="text-sm text-slate-500">Catégorie</dt><dd class="mt-1 text-lg font-semibold text-slate-950">{{ $donation->category }}</dd></div><div><dt class="text-sm text-slate-500">Nombre de pièces</dt><dd class="mt-1 text-2xl font-semibold text-slate-950">{{ $donation->quantity_items }}</dd></div><div><dt class="text-sm text-slate-500">Date souhaitée</dt><dd class="mt-1 text-lg font-semibold text-slate-950">{{ $donation->donation_date->format('d/m/Y') }}</dd></div></dl>
        @if($donation->status !== \App\Models\Donation::STATUS_PENDING)
            <div class="mt-8 flex flex-wrap items-center gap-6 border border-slate-200 bg-white p-6">
                <img src="{{ route('association.donations.qr', $donation) }}" alt="QR Code du don {{ $donation->trackingReference() }}" width="180" height="180" class="border border-slate-200 p-2">
                <div><h2 class="font-semibold text-slate-950">Suivi sécurisé du don</h2><p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Scannez ce code avec une session autorisée pour ouvrir le suivi. Le scan ne modifie pas le statut du don.</p><a href="{{ route('association.donations.qr.download', $donation) }}" class="mt-4 inline-flex min-h-10 items-center bg-emerald-900 px-4 text-sm font-semibold text-white">Télécharger le QR Code</a></div>
            </div>
        @endif
        <section class="mt-8 border border-slate-200 bg-white p-6">
            <h2 class="text-xl font-semibold text-slate-950">Historique du don</h2>
            <ol class="mt-4 space-y-4">
                @foreach($donation->statusHistory->sortByDesc('created_at') as $event)
                    <li class="border-l-2 border-emerald-600 pl-4"><p class="font-semibold text-slate-900">{{ $event->to_status }}</p><p class="mt-1 text-sm text-slate-600">{{ $event->created_at->format('d/m/Y à H:i') }} · {{ $event->actor?->name ?? 'Historique initial' }}</p></li>
                @endforeach
            </ol>
        </section>
>>>>>>> origin/Donation/Association-"aziz"
        <div class="mt-6 flex flex-wrap gap-3"><a href="{{ route('association.donations.index') }}" class="inline-flex min-h-11 items-center border border-slate-300 px-5 font-semibold text-slate-700">Retour à mes demandes</a>
            @if($donation->status === \App\Models\Donation::STATUS_PENDING)
                <a href="{{ route('association.donations.edit', $donation) }}" class="inline-flex min-h-11 items-center bg-emerald-900 px-5 font-semibold text-white">Modifier</a>
                <form method="POST" action="{{ route('association.donations.destroy', $donation) }}" onsubmit="return confirm('Supprimer cette demande de don ?')">@csrf @method('DELETE')<button class="min-h-11 border border-rose-300 px-5 font-semibold text-rose-800">Supprimer</button></form>
            @endif
        </div>
    </section>
@endsection