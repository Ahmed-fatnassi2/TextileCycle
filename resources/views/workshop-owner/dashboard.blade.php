@extends('layouts.workshop-owner')

@section('title', 'Tableau de bord atelier')
@section('content')
    @if($workshops->isEmpty())
        <section class="border border-amber-200 bg-amber-50 p-6">
            <h1 class="text-xl font-semibold">Aucun atelier affecté</h1>
            <p class="mt-2 text-sm text-slate-700">Un administrateur doit associer votre compte à un atelier avant que vous puissiez gérer ses demandes.</p>
        </section>
    @else
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-emerald-800">Espace propriétaire</p>
                <h1 class="mt-1 text-2xl font-bold">Mes ateliers</h1>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                    @foreach($workshops as $workshop)<span>{{ $workshop->name }} · {{ $workshop->address }}</span>@endforeach
                </div>
            </div>
            <a href="{{ route('workshop-owner.repairs.index') }}" class="inline-flex min-h-10 items-center bg-emerald-800 px-4 text-sm font-semibold text-white hover:bg-emerald-900">Gérer les demandes</a>
        </div>

        <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <a href="#delai-risk" class="rounded-xl border-2 border-rose-300 bg-gradient-to-br from-rose-50 to-orange-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><p class="text-sm font-semibold text-rose-800">⚠ Réparations à surveiller</p><p class="mt-1 text-3xl font-black text-rose-900">{{ $atRiskCount }}</p><p class="mt-1 text-xs text-rose-800">Voir le détail du risque ↓</p></a>
            @foreach([
                \App\Models\RepairRequest::STATUS_PENDING => 'À traiter',
                \App\Models\RepairRequest::STATUS_QUOTE_ACCEPTED => 'Devis acceptés',
                \App\Models\RepairRequest::STATUS_IN_PROGRESS => 'En cours',
                \App\Models\RepairRequest::STATUS_READY_FOR_PICKUP => 'Prêts au retrait',
            ] as $status => $label)
                <a href="{{ route('workshop-owner.repairs.index', ['status' => $status]) }}" class="border border-slate-200 bg-white p-4 hover:border-emerald-700">
                    <p class="text-sm text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-800">{{ $statusCounts[$status] }}</p>
                </a>
            @endforeach
        </div>
        <p class="mb-5 text-sm text-slate-600">Impact estimé indicatif des réparations : {{ number_format((float) ($impact->co2 ?? 0), 1) }} kg CO₂e et {{ number_format((float) ($impact->waste ?? 0), 1) }} kg de textile évités ({{ (int) ($impact->repairs ?? 0) }} réparations).</p>
        <section class="mb-7 rounded-xl border border-violet-200 bg-violet-50 p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-violet-800">Transparence IA · aujourd’hui</p>
            <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
                <div><h2 class="text-lg font-bold text-slate-900">Usage IA de votre compte</h2><p class="mt-1 text-sm text-slate-700">{{ (int) ($aiUsage->calls ?? 0) }} appel(s) API · {{ (int) ($aiUsage->input_tokens ?? 0) }} jeton(s) entrée · {{ (int) ($aiUsage->output_tokens ?? 0) }} jeton(s) sortie</p></div>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-violet-900">Sans contenu ni donnée client enregistrés</span>
            </div>
        </section>
        <section class="mb-7 rounded-xl border border-sky-200 bg-white p-5 shadow-sm" aria-labelledby="repair-performance-title">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-wider text-sky-800">Indicateurs atelier · 30 derniers jours</p><h2 id="repair-performance-title" class="mt-1 text-lg font-bold text-slate-900">Performance de réparation</h2><p class="mt-1 text-sm text-slate-600">Indicateurs calculés sur vos ateliers, à partir des dossiers clôturés.</p></div><span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-900">Données actualisées à l’ouverture</span></div>
            <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-lg bg-slate-50 p-3"><p class="text-xs text-slate-500">Dossiers terminés</p><p class="mt-1 text-2xl font-black text-slate-900">{{ $repairPerformance['completed'] }}</p></div>
                <div class="rounded-lg bg-emerald-50 p-3"><p class="text-xs text-emerald-800">Respect des délais</p><p class="mt-1 text-2xl font-black text-emerald-900">{{ $repairPerformance['on_time_percentage'] === null ? '—' : $repairPerformance['on_time_percentage'].'%' }}</p></div>
                <div class="rounded-lg bg-sky-50 p-3"><p class="text-xs text-sky-800">Durée moyenne</p><p class="mt-1 text-2xl font-black text-sky-950">{{ $repairPerformance['average_days'] === null ? '—' : $repairPerformance['average_days'].' j' }}</p></div>
                <div class="rounded-lg bg-violet-50 p-3"><p class="text-xs text-violet-800">Avis qualité automatique</p><p class="mt-1 text-2xl font-black text-violet-950">{{ $repairPerformance['average_quality'] === null ? '—' : $repairPerformance['average_quality'].'/5' }}</p></div>
            </div>
            <p class="mt-3 text-xs text-slate-500">Le respect des délais et la durée sont calculés uniquement lorsque les dates nécessaires sont renseignées. La note qualité est un avis automatique, pas une garantie.</p>
        </section>

        <section id="delai-risk" class="mb-7 scroll-mt-6 overflow-hidden rounded-2xl border-2 border-rose-200 bg-white shadow-md">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-rose-50 via-orange-50 to-white px-5 py-4">
                <div><p class="text-xs font-bold uppercase tracking-wider text-rose-800">Prévision automatique · sans appel IA</p><h2 class="mt-1 text-lg font-bold text-slate-900">Réparations à surveiller</h2></div>
                <span class="rounded-full bg-rose-100 px-3 py-1 text-sm font-bold text-rose-900">{{ $atRiskCount }} risque(s) moyen ou élevé</span>
            </div>
            <p class="border-b border-slate-100 px-5 py-3 text-sm text-slate-600">Le calcul tient compte des jours restants, des demandes ouvertes et de la durée moyenne historique. Ouvrez un dossier pour modifier sa date et voir le risque recalculé.</p>
            @forelse($riskRepairs as $riskRepair)
                @php($riskInfo = $delayRisks[$riskRepair->id])
                <a href="{{ route('workshop-owner.repairs.show', $riskRepair) }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 transition hover:bg-rose-50 last:border-0">
                    <span><span class="block font-semibold text-slate-900">{{ $riskRepair->item_description }}</span><span class="mt-1 block text-sm text-slate-500">{{ $riskRepair->workshop->name }} · {{ $riskRepair->user?->name }} · Échéance {{ $riskRepair->estimated_completion_date?->format('d/m/Y') ?? 'à définir' }}</span></span>
                    <span class="rounded-lg px-3 py-2 text-sm font-bold {{ $riskInfo['risk'] === 'high' ? 'bg-rose-100 text-rose-900' : 'bg-orange-100 text-orange-900' }}">{{ $riskInfo['risk'] === 'high' ? '🔴 Risque élevé' : '🟠 Risque modéré' }}<span class="mt-1 block text-xs font-medium">{{ $riskInfo['reason'] }}</span></span>
                </a>
            @empty
                <p class="px-5 py-5 text-sm text-slate-600">Aucune réparation active à risque. Le seeder de démonstration fournit un dossier en retard pour illustrer ce bloc.</p>
            @endforelse
        </section>

        <section class="border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold">Demandes récentes</h2>
                <a href="{{ route('workshop-owner.repairs.index') }}" class="text-sm font-medium text-emerald-800 underline">Tout afficher</a>
            </div>
            @forelse($recentRepairs as $repairRequest)
                <a href="{{ route('workshop-owner.repairs.show', $repairRequest) }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 last:border-0 hover:bg-slate-50">
                    <span><span class="block font-medium">{{ $repairRequest->item_description }}</span><span class="mt-1 block text-sm text-slate-500">{{ $repairRequest->workshop->name }} · {{ $repairRequest->user?->name }} · {{ $repairRequest->problem_type }}</span></span>
                    <span class="text-sm font-medium text-emerald-800">{{ $repairRequest->status }} @if(isset($delayRisks[$repairRequest->id])) · {{ $delayRisks[$repairRequest->id]['risk'] === 'high' ? '🔴' : ($delayRisks[$repairRequest->id]['risk'] === 'medium' ? '🟠' : '🟢') }} {{ $delayRisks[$repairRequest->id]['reason'] }} @endif</span>
                </a>
            @empty
                <p class="px-5 py-8 text-sm text-slate-500">Aucune demande pour cet atelier.</p>
            @endforelse
        </section>
    @endif
@endsection
