@extends('layouts.workshop-owner')

@section('title', 'Demandes de réparation')
@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Demandes de réparation</h1>
        @if($workshops->isNotEmpty())<p class="mt-1 text-sm text-slate-500">{{ $workshops->pluck('name')->join(' · ') }}</p>@endif
    </div>

    @if($workshops->isEmpty())
        <div class="border border-amber-200 bg-amber-50 p-5 text-sm text-slate-700">Aucun atelier n’est affecté à votre compte.</div>
    @else
        <form method="GET" class="mb-5 flex flex-wrap gap-2">
            <input name="search" value="{{ request('search') }}" placeholder="Rechercher un vêtement..." class="min-h-10 border border-slate-300 bg-white px-3 text-sm">
            <select name="status" class="min-h-10 border border-slate-300 bg-white px-3 text-sm">
                <option value="">Tous les statuts</option>
                @foreach(\App\Models\RepairRequest::statuses() as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach
            </select>
            <button class="min-h-10 bg-slate-900 px-4 text-sm font-semibold text-white">Filtrer</button>
        </form>

        <div class="border border-slate-200 bg-white">
            @forelse($repairRequests as $repairRequest)
                <a href="{{ route('workshop-owner.repairs.show', $repairRequest) }}" class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 last:border-0 hover:bg-slate-50">
                    <span><span class="block font-medium">{{ $repairRequest->item_description }}</span><span class="mt-1 block text-sm text-slate-500">{{ $repairRequest->user?->name }} · {{ $repairRequest->problem_type }} · {{ $repairRequest->created_at->format('d/m/Y') }}</span></span>
                    <span class="text-sm font-semibold text-emerald-800">{{ $repairRequest->status }}</span>
                </a>
            @empty
                <p class="px-5 py-8 text-sm text-slate-500">Aucune demande ne correspond à ces critères.</p>
            @endforelse
        </div>
        @if(method_exists($repairRequests, 'links'))<div class="mt-4">{{ $repairRequests->links() }}</div>@endif
    @endif
@endsection