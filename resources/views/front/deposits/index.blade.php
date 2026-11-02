@extends('layouts.front')

@section('title', 'Points de collecte')

@section('content')
    <section class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-2">Points de collecte</h1>
        <p class="text-gray-600 mb-8">Trouvez le point le plus proche pour déposer vos vêtements.</p>

        <div class="mb-4 flex flex-wrap items-center gap-3">
            <button type="button" data-locate data-message="#locate-message"
                    class="bg-emerald-900 text-white px-5 py-2 rounded-lg hover:bg-emerald-800 disabled:opacity-60">
                📍 Trouver le point le plus proche
            </button>
            <a href="{{ route('deposits.create') }}"
               class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">
                + Déposer un vêtement
            </a>
            @auth
                <a href="{{ route('deposits.mine') }}" class="px-5 py-2 rounded-lg border border-slate-300 hover:bg-white">
                    Mes dépôts et QR codes
                </a>
            @endauth
            @if($userPosition)
                <a href="{{ route('deposits.index') }}" class="text-sm text-slate-500 hover:underline">Réinitialiser le tri</a>
            @endif
        </div>

        <p id="locate-message" class="hidden mb-4 rounded bg-amber-50 border border-amber-200 px-4 py-2 text-sm text-amber-800"></p>

        @if($nearestOpen)
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                Le point ouvert le plus proche est <strong>{{ $nearestOpen->name }}</strong>,
                à <strong>{{ number_format($nearestOpen->distance_km, 1, ',', ' ') }} km</strong>.
                <a href="{{ route('deposits.create', ['point' => $nearestOpen->id]) }}" class="ml-2 font-semibold underline">Déposer ici →</a>
            </div>
        @endif

        {{-- Carte : "isolate" garde les calques Leaflet sous le menu de navigation --}}
        <div class="relative isolate mb-3 overflow-hidden rounded-xl shadow">
            <div class="h-[420px] w-full bg-slate-100"
                 data-map="points"
                 data-points='@json($mapPoints)'
                 @if($userPosition) data-user='@json($userPosition)' @endif
                 @if($nearestOpen) data-nearest="{{ $nearestOpen->id }}" @endif></div>
        </div>

        <div class="mb-8 flex flex-wrap gap-4 text-xs text-slate-600">
            <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded-full bg-emerald-600"></span> Ouvert</span>
            <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded-full bg-amber-600"></span> Plein</span>
            <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded-full bg-orange-600"></span> En maintenance</span>
            @if($userPosition)
                <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded-full bg-blue-600"></span> Votre position</span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($points as $point)
                <div class="bg-white rounded-xl shadow p-5 {{ $nearestOpen?->id === $point->id ? 'ring-2 ring-emerald-600' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-lg">{{ $point->name }}</h3>
                        @if($point->distance_km !== null)
                            <span class="shrink-0 rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-800">
                                {{ number_format($point->distance_km, 1, ',', ' ') }} km
                            </span>
                        @endif
                    </div>
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

                    <div class="mt-4 flex gap-3 text-sm">
                        @if($point->hasCoordinates())
                            <button type="button" data-focus-point="{{ $point->id }}" class="text-emerald-700 hover:underline">Voir sur la carte</button>
                        @endif
                        @if($point->acceptsDeposits())
                            <a href="{{ route('deposits.create', ['point' => $point->id]) }}" class="font-semibold text-emerald-700 hover:underline">Déposer ici →</a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-400">Aucun point disponible.</p>
            @endforelse
        </div>
    </section>
@endsection

@push('scripts')
    @vite('resources/js/deposit-map.js')
@endpush
