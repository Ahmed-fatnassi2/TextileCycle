@extends('layouts.admin')

@section('title', 'Dépôts')
@section('page_title', 'Gestion des dépôts')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nom du citoyen..."
                   class="border rounded-lg px-3 py-2 text-sm w-64">

            <select name="status" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Tous statuts</option>
                @foreach(\App\Models\Deposit::statuses() as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>

            <select name="deposit_point_id" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Tous les points</option>
                @foreach($points as $p)
                    <option value="{{ $p->id }}" @selected(request('deposit_point_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>

            <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">Filtrer</button>
        </form>

        <a href="{{ route('admin.deposits.create') }}"
           class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-emerald-700">
            + Nouveau dépôt
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Citoyen</th>
                    <th class="px-4 py-3 text-left">Point</th>
                    <th class="px-4 py-3 text-left">Poids</th>
                    <th class="px-4 py-3 text-left">État</th>
                    <th class="px-4 py-3 text-left">Statut</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($deposits as $deposit)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $deposit->id }}</td>
                        <td class="px-4 py-3">{{ $deposit->user->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $deposit->depositPoint->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $deposit->weight_kg }} kg</td>
                        <td class="px-4 py-3">{{ $deposit->state }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs
                                @if($deposit->status === 'Trié') bg-green-100 text-green-700
                                @elseif($deposit->status === 'Rejeté') bg-red-100 text-red-700
                                @else bg-blue-100 text-blue-700 @endif">
                                {{ $deposit->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.deposits.show', $deposit) }}" class="text-blue-600 hover:underline">Voir</a>
                            <a href="{{ route('admin.deposits.edit', $deposit) }}" class="text-emerald-600 hover:underline">Modifier</a>
                            <form action="{{ route('admin.deposits.destroy', $deposit) }}" method="POST"
                                  class="inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-center text-gray-400">Aucun dépôt.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $deposits->links() }}</div>

@endsection