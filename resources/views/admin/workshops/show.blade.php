@extends('layouts.admin')
@section('title', $workshop->name)
@section('page_title', 'Détail de l’atelier')
@section('content')
<div class="mb-6 rounded-xl bg-white p-6 shadow"><h2 class="mb-4 text-xl font-bold">{{ $workshop->name }}</h2><dl class="grid gap-4 text-sm sm:grid-cols-3"><div><dt class="text-gray-500">Spécialité</dt><dd>{{ $workshop->specialty }}</dd></div><div><dt class="text-gray-500">Adresse</dt><dd>{{ $workshop->address }}</dd></div><div><dt class="text-gray-500">Téléphone</dt><dd>{{ $workshop->phone }}</dd></div></dl></div>
<div class="rounded-xl bg-white p-6 shadow"><h3 class="mb-4 font-bold">Dernières demandes</h3><table class="w-full text-sm"><thead class="bg-gray-50"><tr><th class="p-3 text-left">Vêtement</th><th class="p-3 text-left">Problème</th><th class="p-3 text-left">Statut</th></tr></thead><tbody class="divide-y">@forelse($workshop->repairRequests as $repairRequest)<tr><td class="p-3">{{ $repairRequest->item_description }}</td><td class="p-3">{{ $repairRequest->problem_type }}</td><td class="p-3">{{ $repairRequest->status }}</td></tr>@empty<tr><td colspan="3" class="p-4 text-center text-gray-400">Aucune demande.</td></tr>@endforelse</tbody></table><a href="{{ route('admin.workshops.index') }}" class="mt-4 inline-block text-sm text-emerald-600">← Retour à la liste</a></div>
@endsection
