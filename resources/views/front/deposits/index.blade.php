@extends('layouts.front')

@section('title', 'Points de collecte')

@section('content')
    <section class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-2">Points de collecte</h1>
        <p class="text-gray-600 mb-8">Trouvez le point le plus proche pour déposer vos vêtements.</p>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-6">{{ session('success') }}</div>
        @endif

        <div class="mb-6">
            <a href="{{ route('deposits.create') }}"
               class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">
                + Déposer un vêtement
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($points as $point)
                <div class="bg-white rounded-xl shadow p-5">
                    <h3 class="font-bold text-lg">{{ $point->name }}</h3>
                    <p class="text-gray-600 text-sm mt-1">{{ $point->address }}</p>
                    <p class="text-gray-600 text-sm">{{ $point->city }}</p>

                    <div class="mt-3 flex justify-between items-center text-sm">
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            @if($point->state === 'Ouvert') bg-green-100 text-green-700
                            @elseif($point->state === 'Plein') bg-yellow-100 text-yellow-700
                            @elseif($point->state === 'En maintenance') bg-orange-100 text-orange-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ $point->state }}
                        </span>
                        <span class="text-gray-500">{{ $point->deposits_count }} dépôts</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-400">Aucun point disponible.</p>
            @endforelse
        </div>
    </section>
@endsection