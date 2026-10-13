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

        <section class="border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold">Demandes récentes</h2>
                <a href="{{ route('workshop-owner.repairs.index') }}" class="text-sm font-medium text-emerald-800 underline">Tout afficher</a>
            </div>
            @forelse($recentRepairs as $repairRequest)
                <a href="{{ route('workshop-owner.repairs.show', $repairRequest) }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 last:border-0 hover:bg-slate-50">
                    <span><span class="block font-medium">{{ $repairRequest->item_description }}</span><span class="mt-1 block text-sm text-slate-500">{{ $repairRequest->workshop->name }} · {{ $repairRequest->user?->name }} · {{ $repairRequest->problem_type }}</span></span>
                    <span class="text-sm font-medium text-emerald-800">{{ $repairRequest->status }}</span>
                </a>
            @empty
                <p class="px-5 py-8 text-sm text-slate-500">Aucune demande pour cet atelier.</p>
            @endforelse
        </section>
    @endif
@endsection