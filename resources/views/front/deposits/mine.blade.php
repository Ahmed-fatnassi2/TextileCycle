@extends('layouts.front')

@section('title', 'Mes dépôts')

@section('content')
    <section class="max-w-5xl mx-auto px-4 py-12">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold mb-2">Mes dépôts</h1>
                <p class="text-gray-600">Présentez le QR code d’un dépôt « En attente » à l’agent du point de collecte.</p>
            </div>
            <a href="{{ route('deposits.create') }}" class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">
                + Nouveau dépôt
            </a>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Dépôts</p>
                <p class="text-3xl font-bold text-emerald-700">{{ $totals['count'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">En attente de dépôt</p>
                <p class="text-3xl font-bold text-amber-600">{{ $totals['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Textile collecté</p>
                <p class="text-3xl font-bold text-emerald-700">{{ number_format($totals['weight'], 1, ',', ' ') }} kg</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Point</th>
                        <th class="px-4 py-3 text-left">Poids</th>
                        <th class="px-4 py-3 text-left">Date prévue</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($deposits as $deposit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono">{{ $deposit->code }}</td>
                            <td class="px-4 py-3">{{ $deposit->depositPoint->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $deposit->weight_kg }} kg</td>
                            <td class="px-4 py-3">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $deposit->statusBadgeClasses() }}">{{ $deposit->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('deposits.mine.show', $deposit) }}" class="font-semibold text-emerald-700 hover:underline">
                                    {{ $deposit->isPending() ? 'Afficher le QR' : 'Détail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">Vous n’avez encore fait aucun dépôt.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $deposits->links() }}</div>
    </section>
@endsection
