@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Tableau de bord')

@section('content')

    {{-- Cartes statistiques --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

        <a href="{{ route('admin.deposit-points.index') }}"
           class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Points de collecte</p>
                    <p class="text-3xl font-bold text-emerald-600">
                        {{ \App\Models\DepositPoint::count() }}
                    </p>
                </div>
                <span class="text-4xl">📍</span>
            </div>
        </a>

        <a href="{{ route('admin.deposits.index') }}"
           class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Dépôts</p>
                    <p class="text-3xl font-bold text-emerald-600">
                        {{ \App\Models\Deposit::count() }}
                    </p>
                </div>
                <span class="text-4xl">📦</span>
            </div>
        </a>

        <div class="bg-white p-6 rounded-xl shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Poids total</p>
                    <p class="text-3xl font-bold text-emerald-600">
                        {{ number_format(\App\Models\Deposit::sum('weight_kg'), 1) }} kg
                    </p>
                </div>
                <span class="text-4xl">⚖️</span>
            </div>
        </div>

        <a href="{{ route('admin.associations.index') }}" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div><p class="text-gray-500 text-sm">Associations</p><p class="text-3xl font-bold text-emerald-600">{{ \App\Models\Association::count() }}</p><p class="mt-1 text-xs text-amber-700">{{ \App\Models\Association::where('status', \App\Models\Association::STATUS_PENDING)->count() }} en attente</p></div>
                <span class="text-4xl">🤝</span>
            </div>
        </a>

        <a href="{{ route('admin.donations.index') }}" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div><p class="text-gray-500 text-sm">Demandes de dons</p><p class="text-3xl font-bold text-emerald-600">{{ \App\Models\Donation::count() }}</p></div>
                <span class="text-4xl">🧺</span>
            </div>
        </a>

        <div class="bg-white p-6 rounded-xl shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Citoyens</p>
                    <p class="text-3xl font-bold text-emerald-600">
                        {{ \App\Models\User::count() }}
                    </p>
                </div>
                <span class="text-4xl">👥</span>
            </div>
        </div>
    </div>

    {{-- Raccourcis --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <a href="{{ route('admin.deposit-points.create') }}"
           class="bg-emerald-600 text-white p-6 rounded-xl shadow hover:bg-emerald-700 transition flex items-center justify-between">
            <span class="text-lg font-semibold">+ Nouveau point de collecte</span>
            <span class="text-3xl">📍</span>
        </a>

        <a href="{{ route('admin.deposits.create') }}"
           class="bg-gray-800 text-white p-6 rounded-xl shadow hover:bg-gray-900 transition flex items-center justify-between">
            <span class="text-lg font-semibold">+ Enregistrer un dépôt</span>
            <span class="text-3xl">📦</span>
        </a>
        <a href="{{ route('admin.associations.index') }}" class="bg-[#e9f1ed] text-slate-950 p-6 rounded-xl shadow hover:bg-emerald-100 transition flex items-center justify-between">
            <span class="text-lg font-semibold">Examiner les associations</span>
            <span class="text-3xl">🤝</span>
        </a>
        <a href="{{ route('admin.donations.index') }}" class="bg-slate-900 text-white p-6 rounded-xl shadow hover:bg-slate-800 transition flex items-center justify-between">
            <span class="text-lg font-semibold">Suivre les demandes de dons</span>
            <span class="text-3xl">🧺</span>
        </a>
    </div>

    {{-- Derniers dépôts --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold">Derniers dépôts</h2>
            <a href="{{ route('admin.deposits.index') }}"
               class="text-sm text-emerald-600 hover:underline">Voir tout →</a>
        </div>

        @php
            $recentDeposits = \App\Models\Deposit::with(['user', 'depositPoint'])
                ->latest('deposit_date')
                ->take(5)
                ->get();
        @endphp

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left">Citoyen</th>
                    <th class="px-3 py-2 text-left">Point</th>
                    <th class="px-3 py-2 text-left">Poids</th>
                    <th class="px-3 py-2 text-left">État</th>
                    <th class="px-3 py-2 text-left">Statut</th>
                    <th class="px-3 py-2 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($recentDeposits as $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">{{ $d->user->name ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $d->depositPoint->name ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $d->weight_kg }} kg</td>
                        <td class="px-3 py-2">{{ $d->state }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 rounded text-xs
                                @if($d->status === 'Trié') bg-green-100 text-green-700
                                @elseif($d->status === 'Rejeté') bg-red-100 text-red-700
                                @else bg-blue-100 text-blue-700 @endif">
                                {{ $d->status }}
                            </span>
                        </td>
                        <td class="px-3 py-2">{{ $d->deposit_date->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-gray-400">Aucun dépôt.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection