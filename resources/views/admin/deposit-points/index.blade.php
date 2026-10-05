@extends('layouts.admin')

@section('title', 'Points de collecte')
@section('page_title', 'Gestion des points de collecte')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher..."
                   class="border rounded-lg px-3 py-2 text-sm w-64">

            <select name="state" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Tous les états</option>
                @foreach(\App\Models\DepositPoint::states() as $state)
                    <option value="{{ $state }}" @selected(request('state') === $state)>{{ $state }}</option>
                @endforeach
            </select>

            <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                Filtrer
            </button>
        </form>

        <a href="{{ route('admin.deposit-points.create') }}"
           class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-emerald-700">
            + Nouveau point
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
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Ville</th>
                    <th class="px-4 py-3 text-left">Capacité</th>
                    <th class="px-4 py-3 text-left">État</th>
                    <th class="px-4 py-3 text-left">Dépôts</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($depositPoints as $point)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $point->id }}</td>
                        <td class="px-4 py-3 font-medium">{{ $point->name }}</td>
                        <td class="px-4 py-3">{{ $point->city }}</td>
                        <td class="px-4 py-3">{{ $point->capacity }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                @if($point->state === 'Ouvert') bg-green-100 text-green-700
                                @elseif($point->state === 'Plein') bg-yellow-100 text-yellow-700
                                @elseif($point->state === 'En maintenance') bg-orange-100 text-orange-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $point->state }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $point->deposits_count }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.deposit-points.show', $point) }}"
                               class="text-blue-600 hover:underline">Voir</a>
                            <a href="{{ route('admin.deposit-points.edit', $point) }}"
                               class="text-emerald-600 hover:underline">Modifier</a>
                            <form action="{{ route('admin.deposit-points.destroy', $point) }}"
                                  method="POST" class="inline"
                                  onsubmit="return confirm('Supprimer ce point ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-6 text-gray-400">Aucun point de collecte.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $depositPoints->links() }}</div>

@endsection