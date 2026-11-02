@extends('layouts.front')

@section('title', 'Dépôt '.$deposit->code)

@section('content')
    <section class="max-w-3xl mx-auto px-4 py-12">
        <a href="{{ route('deposits.mine') }}" class="text-sm text-emerald-700 hover:underline print:hidden">← Mes dépôts</a>

        <div class="mt-4 bg-white rounded-xl shadow p-6 sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Dépôt</p>
                    <h1 class="font-mono text-2xl font-bold">{{ $deposit->code }}</h1>
                </div>
                <span class="px-3 py-1 rounded text-sm font-semibold {{ $deposit->statusBadgeClasses() }}">{{ $deposit->status }}</span>
            </div>

            <div class="mt-6 grid gap-8 sm:grid-cols-2">
                <div class="flex flex-col items-center">
                    @if($deposit->isPending())
                        {{-- SVG généré côté serveur à partir de notre propre URL : pas de contenu utilisateur --}}
                        <div class="w-60 max-w-full rounded-lg border border-slate-200 bg-white p-3 [&>svg]:h-auto [&>svg]:w-full">
                            {!! $qrSvg !!}
                        </div>
                        <p class="mt-3 text-center text-sm text-gray-600">Présentez ce QR code à l’agent du point de collecte.</p>
                        <div class="mt-4 flex gap-3 print:hidden">
                            <a href="{{ route('deposits.mine.qr', $deposit) }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50">Télécharger</a>
                            <button type="button" onclick="window.print()" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50">Imprimer</button>
                        </div>
                    @else
                        <div class="w-full rounded-lg bg-slate-50 p-6 text-center">
                            <p class="text-4xl">{{ $deposit->status === \App\Models\Deposit::STATUS_REJETE ? '✖' : '✔' }}</p>
                            <p class="mt-2 font-semibold">
                                {{ $deposit->status === \App\Models\Deposit::STATUS_REJETE ? 'Dépôt refusé au point de collecte' : 'Dépôt reçu, merci !' }}
                            </p>
                            @if($deposit->checked_in_at)
                                <p class="mt-1 text-sm text-gray-500">le {{ $deposit->checked_in_at->format('d/m/Y à H:i') }}</p>
                            @endif
                            <p class="mt-2 text-xs text-gray-400">Ce QR code a déjà été utilisé.</p>
                        </div>
                    @endif
                </div>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Point de collecte</dt>
                        <dd class="font-medium">{{ $deposit->depositPoint->name ?? '—' }}</dd>
                        <dd class="text-gray-600">{{ $deposit->depositPoint->address ?? '' }}, {{ $deposit->depositPoint->city ?? '' }}</dd>
                    </div>
                    <div><dt class="text-gray-500">Date prévue</dt><dd>{{ $deposit->deposit_date->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-500">Poids {{ $deposit->isPending() ? 'estimé' : 'pesé' }}</dt><dd>{{ $deposit->weight_kg }} kg</dd></div>
                    <div><dt class="text-gray-500">État des vêtements</dt><dd>{{ $deposit->state }}</dd></div>
                    @if($deposit->depositPoint?->hasCoordinates())
                        <div class="print:hidden">
                            <a target="_blank" rel="noopener"
                               href="https://www.openstreetmap.org/directions?to={{ $deposit->depositPoint->latitude }}%2C{{ $deposit->depositPoint->longitude }}"
                               class="font-semibold text-emerald-700 hover:underline">Itinéraire vers le point →</a>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </section>
@endsection
