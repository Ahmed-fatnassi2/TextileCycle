@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Tableau de bord')

@section('content')

    @php($usagePercent = min(100, (int) round($aiUsageTotalToday / max(1, (int) config('ai.global_daily_limit')) * 100)))
    <section class="mb-8 rounded-xl border border-violet-200 bg-white p-5 shadow-sm" aria-labelledby="repair-ai-usage-title">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div><p class="text-xs font-bold uppercase tracking-wider text-violet-800">Suivi des appels réels · 7 derniers jours</p><h2 id="repair-ai-usage-title" class="mt-1 text-xl font-bold text-slate-900">Usage IA du module Réparation</h2><p class="mt-1 text-sm text-slate-600">Seuls les appels envoyés à OpenAI sont comptés : ni cache ni mode automatique.</p></div>
            <div class="rounded-lg bg-violet-50 px-4 py-3 text-right"><p class="text-sm text-violet-900">Aujourd’hui</p><p class="text-2xl font-black text-violet-950">{{ $aiUsageTotalToday }} <span class="text-sm font-semibold">/ {{ (int) config('ai.global_daily_limit') }} appels</span></p></div>
        </div>
        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Utilisation quotidienne du quota IA" aria-valuenow="{{ min($aiUsageTotalToday, (int) config('ai.global_daily_limit')) }}" aria-valuemin="0" aria-valuemax="{{ (int) config('ai.global_daily_limit') }}"><div class="h-full rounded-full bg-violet-600" style="width: {{ $usagePercent }}%"></div></div>
        <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_1.2fr]">
            <div><h3 class="text-sm font-bold text-slate-800">Activité quotidienne</h3><div class="mt-3 flex h-28 items-end gap-2" role="img" aria-label="Graphique des appels IA par jour sur sept jours">
                @foreach($aiUsageDays as $day)
                    @php($barHeight = $day['calls'] ? max(8, (int) round($day['calls'] / max(1, $aiUsageDays->max('calls')) * 100)) : 3)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-1"><span class="text-[10px] font-semibold text-slate-600">{{ $day['calls'] ?: '' }}</span><div class="w-full rounded-t bg-violet-500" style="height: {{ $barHeight }}%"></div><span class="text-[10px] text-slate-500">{{ $day['label'] }}</span></div>
                @endforeach
            </div></div>
            <div><h3 class="text-sm font-bold text-slate-800">Répartition et jetons · aujourd’hui</h3><ul class="mt-2 divide-y divide-slate-100">
                @forelse($aiUsageToday as $usage)
                    <li class="flex flex-wrap justify-between gap-2 py-2 text-sm"><span class="font-medium text-slate-700">{{ ['repair_assistant' => 'Assistant réparation', 'repair_message' => 'Messages atelier', 'repair_alternatives' => 'Conseils réparation / réemploi', 'repair_quality' => 'Analyse qualité'][$usage->feature] ?? $usage->feature }}</span><span class="text-slate-600">{{ (int) $usage->calls }} appel(s) · {{ (int) ($usage->input_tokens ?? 0) + (int) ($usage->output_tokens ?? 0) }} jeton(s)</span></li>
                @empty
                    <li class="py-2 text-sm text-slate-500">Aucun appel IA du module Réparation aujourd’hui.</li>
                @endforelse
            </ul></div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Total aujourd’hui : {{ $aiUsageInputTokensToday }} jetons en entrée et {{ $aiUsageOutputTokensToday }} en sortie. Le suivi conserve uniquement fonctionnalité, statut et volumes de jetons, jamais les prompts.</p>
    </section>

    @php($repairImpact = \App\Models\RepairRequest::whereNotNull('co2_saved_kg')->selectRaw('count(*) as repairs, sum(co2_saved_kg) as co2, sum(waste_saved_kg) as waste')->first())
    @php($impactByCategory = \App\Models\RepairRequest::whereNotNull('co2_saved_kg')->selectRaw('item_category, count(*) as repairs, sum(co2_saved_kg) as co2, sum(waste_saved_kg) as waste')->groupBy('item_category')->get())
    <section class="mb-8 rounded-xl border border-emerald-200 bg-emerald-50 p-5"><h2 class="font-semibold">Impact indicatif des réparations</h2><p class="mt-2">{{ (int) ($repairImpact->repairs ?? 0) }} réparations · {{ number_format((float) ($repairImpact->co2 ?? 0), 1) }} kg CO₂e · {{ number_format((float) ($repairImpact->waste ?? 0), 1) }} kg de textile évités.</p><ul class="mt-2 flex flex-wrap gap-x-5 text-sm">@foreach($impactByCategory as $row)<li>{{ $row->item_category }} : {{ $row->repairs }} · {{ number_format((float) $row->co2, 1) }} kg CO₂e</li>@endforeach</ul><p class="mt-1 text-xs">Estimations indicatives.</p></section>

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
                    <p class="mt-1 text-xs text-amber-700">{{ \App\Models\Deposit::where('status', \App\Models\Deposit::STATUS_EN_ATTENTE)->count() }} en attente de validation</p>
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

        <a href="{{ route('admin.workshops.index') }}" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between"><div><p class="text-gray-500 text-sm">Ateliers</p><p class="text-3xl font-bold text-emerald-600">{{ \App\Models\Workshop::count() }}</p></div><span class="text-4xl">🧵</span></div>
        </a>
        <a href="{{ route('admin.repair-requests.index') }}" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between"><div><p class="text-gray-500 text-sm">Réparations</p><p class="text-3xl font-bold text-emerald-600">{{ \App\Models\RepairRequest::count() }}</p></div><span class="text-4xl">🪡</span></div>
        </a>
        <a href="{{ route('admin.repair-requests.index', ['status' => \App\Models\RepairRequest::STATUS_PENDING]) }}" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between"><div><p class="text-gray-500 text-sm">Réparations en attente</p><p class="text-3xl font-bold text-emerald-600">{{ \App\Models\RepairRequest::where('status', \App\Models\RepairRequest::STATUS_PENDING)->count() }}</p></div><span class="text-4xl">⏳</span></div>
        </a>

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

        <a href="{{ route('admin.users.index') }}" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Citoyens</p>
                    <p class="text-3xl font-bold text-emerald-600">
                        {{ \App\Models\User::count() }}
                    </p>
                </div>
                <span class="text-4xl">👥</span>
            </div>
        </a>
    </div>

    <div class="mb-8 rounded-xl bg-white p-6 shadow">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold">Activité récente</h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-emerald-600 hover:underline">Voir tous les comptes →</a>
        </div>

        @forelse($recentActivities as $activity)
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 py-3 last:border-b-0">
                <div>
                    <p class="font-medium text-slate-800">{{ $activity->action }}</p>
                    <p class="text-sm text-slate-600">{{ $activity->description }}</p>
                </div>
                <div class="text-right text-xs text-slate-400">
                    <p>{{ $activity->user->name ?? 'Utilisateur' }}</p>
                    <p>{{ $activity->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune activité récente.</p>
        @endforelse
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
                            <span class="px-2 py-1 rounded text-xs {{ $d->statusBadgeClasses() }}">{{ $d->status }}</span>
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
