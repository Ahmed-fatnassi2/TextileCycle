@extends('layouts.admin')

@section('title', 'Détail du don')
@section('page_title', 'Détail de la demande')

@section('content')
    <div class="max-w-4xl border border-slate-200 bg-white">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 p-6">
            <div><p class="text-sm text-slate-500">{{ $donation->donation_date->format('d/m/Y') }}</p><h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ $donation->association->name }}</h2></div>
            <span class="border border-sky-200 bg-sky-50 px-3 py-1.5 text-sm font-semibold text-sky-900">{{ $donation->status }}</span>
        </div>
<<<<<<< HEAD
        <dl class="grid gap-6 p-6 sm:grid-cols-2"><div><dt class="text-sm text-slate-500">Quantité</dt><dd class="mt-1 text-xl font-semibold">{{ $donation->quantity_items }} pièces</dd></div><div><dt class="text-sm text-slate-500">Contact association</dt><dd class="mt-1 font-semibold">{{ $donation->association->contact_person }}</dd><dd class="text-sm text-slate-600">{{ $donation->association->phone }}</dd></div></dl>
=======
        <dl class="grid gap-6 p-6 sm:grid-cols-2"><div><dt class="text-sm text-slate-500">Référence</dt><dd class="mt-1 font-mono font-semibold">{{ $donation->trackingReference() }}</dd></div><div><dt class="text-sm text-slate-500">Catégorie</dt><dd class="mt-1 font-semibold">{{ $donation->category }}</dd></div><div><dt class="text-sm text-slate-500">Quantité</dt><dd class="mt-1 text-xl font-semibold">{{ $donation->quantity_items }} pièces</dd></div><div><dt class="text-sm text-slate-500">Contact association</dt><dd class="mt-1 font-semibold">{{ $donation->association->contact_person }}</dd><dd class="text-sm text-slate-600">{{ $donation->association->phone }}</dd></div></dl>
        @if($donation->status !== \App\Models\Donation::STATUS_PENDING)
            <div class="flex flex-wrap items-center gap-5 border-t border-slate-200 p-6"><img src="{{ route('association.donations.qr', $donation) }}" alt="QR Code du don {{ $donation->trackingReference() }}" width="150" height="150"><a href="{{ route('association.donations.qr.download', $donation) }}" class="font-semibold text-emerald-900 underline">Télécharger le QR Code</a><a href="{{ route('donations.track', $donation->tracking_token) }}" class="font-semibold text-emerald-900 underline">Ouvrir le suivi</a></div>
        @endif
>>>>>>> origin/Donation/Association-"aziz"
        <div class="flex flex-wrap gap-3 border-t border-slate-200 p-6"><a href="{{ route('admin.donations.edit', $donation) }}" class="inline-flex min-h-10 items-center bg-emerald-900 px-4 text-sm font-semibold text-white">Modifier / changer le statut</a><form method="POST" action="{{ route('admin.donations.destroy', $donation) }}" onsubmit="return confirm('Supprimer cette demande de don ?')">@csrf @method('DELETE')<button class="min-h-10 border border-rose-300 px-4 text-sm font-semibold text-rose-800">Supprimer</button></form></div>
    </div>
@endsection