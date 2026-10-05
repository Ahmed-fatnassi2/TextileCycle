@extends('layouts.admin')

@section('title', $depositPoint->name)
@section('page_title', 'Détail du point')

@section('content')

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">{{ $depositPoint->name }}</h2>

        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Adresse</dt><dd>{{ $depositPoint->address }}</dd></div>
            <div><dt class="text-gray-500">Ville</dt><dd>{{ $depositPoint->city }}</dd></div>
            <div><dt class="text-gray-500">Capacité</dt><dd>{{ $depositPoint->capacity }}</dd></div>
            <div><dt class="text-gray-500">État</dt><dd>{{ $depositPoint->state }}</dd></div>
        </dl>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-bold mb-4">Derniers dépôts ({{ $depositPoint->deposits->count() }})</h3>

        <table class="w-full text-sm">
            <thead class="text-gray-600 bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">Citoyen</th>
                    <th class="px-3 py-2 text-left">Poids</th>
                    <th class="px-3 py-2 text-left">État</th>
                    <th class="px-3 py-2 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($depositPoint->deposits as $deposit)
                    <tr>
                        <td class="px-3 py-2">{{ $deposit->user->name ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $deposit->weight_kg }} kg</td>
                        <td class="px-3 py-2">{{ $deposit->state }}</td>
                        <td class="px-3 py-2">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-400">Aucun dépôt.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            <a href="{{ route('admin.deposit-points.index') }}"
               class="text-sm text-emerald-600 hover:underline">← Retour à la liste</a>
        </div>
    </div>

@endsection