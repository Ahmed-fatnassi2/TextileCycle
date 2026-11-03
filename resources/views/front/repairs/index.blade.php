@extends('layouts.front')
@section('title', 'Mes réparations')
@section('content')
<section class="mx-auto max-w-5xl px-4 py-12">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-3xl font-bold">Mes réparations</h1><p class="mt-1 text-gray-600">Suivez vos devis, vos dates estimées et l’avancement de chaque vêtement.</p></div>
        <a href="{{ route('repairs.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white">+ Nouvelle demande</a>
    </div>

    @if($highQuoteRepair)
        <a href="{{ route('repairs.show', $highQuoteRepair) }}" class="group mb-6 block overflow-hidden rounded-2xl border-2 border-amber-300 bg-gradient-to-r from-amber-50 via-orange-50 to-white p-5 shadow-md transition hover:-translate-y-0.5 hover:shadow-xl sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex gap-4"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-200 text-2xl">♻</span><div>
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Devis élevé · alternative disponible</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Votre devis de {{ number_format((float) $highQuoteRepair->estimated_cost, 2, ',', ' ') }} TND est prêt</h2>
                    <p class="mt-1 text-sm text-slate-700">{{ $highQuoteRepair->item_description }} · {{ $highQuoteRepair->workshop->name }}</p>
                </div></div>
                <span class="rounded-full bg-amber-200 px-3 py-1 text-xs font-bold text-amber-950">Seuil : {{ number_format((float) config('ai.upcycle_cost_threshold'), 0) }} TND</span>
            </div>
            <p class="mt-4 inline-flex items-center gap-2 rounded-lg bg-amber-800 px-4 py-2.5 text-sm font-bold text-white transition group-hover:bg-amber-900">Ouvrir le devis et les alternatives <span aria-hidden="true">→</span></p>
        </a>
    @endif

    <div class="space-y-4">
        @forelse($repairRequests as $repairRequest)
            <a href="{{ route('repairs.show', $repairRequest) }}" class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-400 hover:shadow-lg">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-bold">{{ $repairRequest->workshop->name }}</h2><p class="mt-1 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($repairRequest->item_description, 90) }}</p></div><span class="rounded bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">{{ $repairRequest->status }}</span></div>
                <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-500"><span>Devis : {{ $repairRequest->estimated_cost ? $repairRequest->estimated_cost.' TND' : 'en attente' }}</span><span>Fin estimée : {{ $repairRequest->estimated_completion_date?->format('d/m/Y') ?? 'à confirmer' }}</span></div>
                @if($repairRequest->status === \App\Models\RepairRequest::STATUS_QUOTE_PROPOSED && $repairRequest->estimated_cost >= config('ai.upcycle_cost_threshold'))
                    <p class="mt-3 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">♻ Voir les alternatives réparation / upcycling / don</p>
                @endif
            </a>
        @empty
            <div class="rounded-xl bg-white p-8 text-center text-gray-500 shadow">Vous n’avez encore aucune demande de réparation.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $repairRequests->links() }}</div>
</section>
@endsection
