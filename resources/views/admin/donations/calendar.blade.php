@extends('layouts.admin')

@section('title', 'Calendrier des dons | TexTileCycle')
@section('page_title', 'Calendrier des livraisons')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">Module 4 · Associations et dons</p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-950">Planning des livraisons</h2>
            <p class="mt-1 text-sm text-slate-600">
                Glissez une livraison vers un autre jour pour la replanifier : l’association est prévenue par email.
                Capacité : <strong>{{ $dailyDeliveries }} livraisons</strong> et <strong>{{ $dailyItems }} pièces</strong> par jour.
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.donations.index') }}" class="inline-flex min-h-10 items-center border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Vue liste</a>
            <a href="{{ route('admin.donations.create') }}" class="inline-flex min-h-10 items-center bg-emerald-900 px-4 text-sm font-semibold text-white hover:bg-emerald-800">Enregistrer un don</a>
        </div>
    </div>

    {{-- Assistant de planning IA --}}
    <section class="mb-6 border border-violet-200 bg-violet-50/60 p-5"
             data-planning-assistant
             data-optimize-url="{{ route('admin.donations.calendar.optimize') }}">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-slate-950">✨ Assistant de planning</h3>
                <p class="text-sm text-slate-600">
                    L’IA analyse les livraisons en retard et des {{ config('donations.planning_horizon_days') }} prochains jours,
                    les besoins des associations et la capacité, puis propose des déplacements. Rien n’est modifié sans votre accord.
                </p>
            </div>
            <button type="button" data-planning-run
                    class="inline-flex min-h-10 items-center bg-violet-700 px-4 text-sm font-semibold text-white hover:bg-violet-800 disabled:opacity-60">
                Optimiser le planning
            </button>
        </div>
        <div data-planning-result hidden class="mt-4"></div>
    </section>

    {{-- Message après un déplacement (succès ou refus) --}}
    <div data-calendar-flash hidden class="mb-4 border px-4 py-3 text-sm" role="status"></div>

    <div class="mb-4 flex flex-wrap gap-4 text-xs text-slate-600">
        @foreach($colors as $status => $color)
            <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-sm" style="background: {{ $color }}"></span>{{ $status }}</span>
        @endforeach
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-sm border border-rose-300 bg-rose-50"></span>Jour complet</span>
        <span class="text-slate-400">Les dons livrés ne peuvent plus être déplacés.</span>
    </div>

    <div class="border border-slate-200 bg-white p-4"
         data-donation-calendar
         data-events-url="{{ route('admin.donations.calendar.events') }}"
         data-daily-deliveries="{{ $dailyDeliveries }}"
         data-daily-items="{{ $dailyItems }}"></div>
@endsection

@push('scripts')
    @vite('resources/js/donation-calendar.js')
@endpush
