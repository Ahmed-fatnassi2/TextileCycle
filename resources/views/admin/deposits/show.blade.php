@extends('layouts.admin')

@section('title', 'Dépôt #' . $deposit->id)
@section('page_title', 'Détail du dépôt')

@section('content')

    <div class="bg-white rounded-xl shadow p-6 max-w-2xl">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Citoyen</dt><dd>{{ $deposit->user->name ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Email</dt><dd>{{ $deposit->user->email ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Point</dt><dd>{{ $deposit->depositPoint->name ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Ville</dt><dd>{{ $deposit->depositPoint->city ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Poids</dt><dd>{{ $deposit->weight_kg }} kg</dd></div>
            <div><dt class="text-gray-500">État</dt><dd>{{ $deposit->state }}</dd></div>
            <div><dt class="text-gray-500">Statut</dt><dd>{{ $deposit->status }}</dd></div>
            <div><dt class="text-gray-500">Date</dt><dd>{{ $deposit->deposit_date->format('d/m/Y') }}</dd></div>
        </dl>

        <div class="mt-6 flex gap-3">
            <a href="{{ route('admin.deposits.edit', $deposit) }}"
               class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm">Modifier</a>
            <a href="{{ route('admin.deposits.index') }}"
               class="text-sm text-gray-600 hover:underline self-center">← Retour</a>
        </div>
    </div>

@endsection