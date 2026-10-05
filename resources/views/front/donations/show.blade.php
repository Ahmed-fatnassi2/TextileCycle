@extends('layouts.front')

@section('title', 'Détail de la demande')

@section('content')
    <section class="mx-auto max-w-4xl px-5 py-12 sm:px-8 lg:px-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Espace association</p>
        <div class="mt-3 flex flex-wrap items-start justify-between gap-4"><div><h1 class="font-display text-4xl text-slate-950">Demande de don</h1><p class="mt-2 text-slate-600">Créée le {{ $donation->created_at->format('d/m/Y') }}</p></div><span class="border border-sky-200 bg-sky-50 px-3 py-1.5 text-sm font-semibold text-sky-900">{{ $donation->status }}</span></div>
        <dl class="mt-8 grid gap-6 border-y border-slate-200 bg-white p-6 sm:grid-cols-2"><div><dt class="text-sm text-slate-500">Nombre de pièces</dt><dd class="mt-1 text-2xl font-semibold text-slate-950">{{ $donation->quantity_items }}</dd></div><div><dt class="text-sm text-slate-500">Date souhaitée</dt><dd class="mt-1 text-lg font-semibold text-slate-950">{{ $donation->donation_date->format('d/m/Y') }}</dd></div></dl>
        <div class="mt-6 flex flex-wrap gap-3"><a href="{{ route('association.donations.index') }}" class="inline-flex min-h-11 items-center border border-slate-300 px-5 font-semibold text-slate-700">Retour à mes demandes</a>
            @if($donation->status === \App\Models\Donation::STATUS_PENDING)
                <a href="{{ route('association.donations.edit', $donation) }}" class="inline-flex min-h-11 items-center bg-emerald-900 px-5 font-semibold text-white">Modifier</a>
                <form method="POST" action="{{ route('association.donations.destroy', $donation) }}" onsubmit="return confirm('Supprimer cette demande de don ?')">@csrf @method('DELETE')<button class="min-h-11 border border-rose-300 px-5 font-semibold text-rose-800">Supprimer</button></form>
            @endif
        </div>
    </section>
@endsection