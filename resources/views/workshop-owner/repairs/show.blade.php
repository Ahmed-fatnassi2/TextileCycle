@extends('layouts.workshop-owner')

@section('title', 'Dossier de réparation')
@section('content')
    <a href="{{ route('workshop-owner.repairs.index') }}" class="text-sm font-medium text-emerald-800 underline">← Toutes les demandes</a>
    <section class="mt-4 border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500">{{ $repairRequest->user?->name }} · {{ $repairRequest->user?->email }}</p>
                <h1 class="mt-1 text-2xl font-bold">{{ $repairRequest->item_description }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $repairRequest->problem_type }}</p>
            </div>
            <span class="border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-900">{{ $repairRequest->status }}</span>
        </div>

        <dl class="mt-6 grid gap-4 border-t border-slate-100 pt-5 text-sm sm:grid-cols-3">
            <div><dt class="text-slate-500">Devis proposé</dt><dd class="mt-1 font-medium">{{ $repairRequest->estimated_cost ? $repairRequest->estimated_cost.' TND' : 'En attente' }}</dd></div>
            <div><dt class="text-slate-500">Coût final</dt><dd class="mt-1 font-medium">{{ $repairRequest->cost ? $repairRequest->cost.' TND' : '—' }}</dd></div>
            <div><dt class="text-slate-500">Fin estimée</dt><dd class="mt-1 font-medium">{{ $repairRequest->estimated_completion_date?->format('d/m/Y') ?? 'À confirmer' }}</dd></div>
            <div><dt class="text-slate-500">Statut</dt><dd class="mt-1 font-medium">{{ $repairRequest->status }}</dd></div>
        </dl>
    </section>

    @if(in_array($repairRequest->status, [\App\Models\RepairRequest::STATUS_PENDING, \App\Models\RepairRequest::STATUS_QUOTE_REFUSED], true))
        <section class="mt-5 border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Proposer un devis</h2>
            <form method="POST" action="{{ route('workshop-owner.repairs.quote', $repairRequest) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')
                <label class="text-sm font-medium">Montant (TND)
                    <input type="number" name="estimated_cost" min="0" max="100000" step="0.01" value="{{ old('estimated_cost', $repairRequest->estimated_cost) }}" required class="mt-1 block min-h-10 w-full border border-slate-300 px-3">
                    @error('estimated_cost')<span class="mt-1 block text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium">Date estimée
                    <input type="date" name="estimated_completion_date" min="{{ now()->toDateString() }}" value="{{ old('estimated_completion_date', $repairRequest->estimated_completion_date?->toDateString()) }}" required class="mt-1 block min-h-10 w-full border border-slate-300 px-3">
                    @error('estimated_completion_date')<span class="mt-1 block text-rose-700">{{ $message }}</span>@enderror
                </label>
                <div class="sm:col-span-2"><button class="min-h-10 bg-emerald-800 px-4 text-sm font-semibold text-white hover:bg-emerald-900">Envoyer le devis au citoyen</button></div>
            </form>
        </section>
    @elseif($repairRequest->status === \App\Models\RepairRequest::STATUS_QUOTE_ACCEPTED)
        <form method="POST" action="{{ route('workshop-owner.repairs.status', $repairRequest) }}" class="mt-5 border border-emerald-200 bg-emerald-50 p-5">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ \App\Models\RepairRequest::STATUS_IN_PROGRESS }}">
            <p class="mb-3 text-sm text-emerald-950">Le citoyen a accepté le devis. Vous pouvez commencer le travail.</p>
            <button class="min-h-10 bg-emerald-800 px-4 text-sm font-semibold text-white">Démarrer la réparation</button>
        </form>
    @elseif($repairRequest->status === \App\Models\RepairRequest::STATUS_IN_PROGRESS)
        <form method="POST" action="{{ route('workshop-owner.repairs.status', $repairRequest) }}" enctype="multipart/form-data" class="mt-5 border border-slate-200 bg-white p-6">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ \App\Models\RepairRequest::STATUS_READY_FOR_PICKUP }}">
            <h2 class="font-semibold">Terminer la réparation</h2>
            <label class="mt-3 block text-sm font-medium">Coût final (TND)
                <input type="number" name="cost" min="0" max="100000" step="0.01" value="{{ old('cost', $repairRequest->estimated_cost) }}" required class="mt-1 block min-h-10 w-full max-w-xs border border-slate-300 px-3">
                @error('cost')<span class="mt-1 block text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="mt-3 block text-sm font-medium">Photos après réparation (facultatif, 3 maximum)
                <input type="file" name="after_photos[]" accept="image/jpeg,image/png,image/webp" multiple class="mt-1 block w-full text-sm">
            </label>
            @error('after_photos')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            <button class="mt-4 min-h-10 bg-emerald-800 px-4 text-sm font-semibold text-white">Marquer prête au retrait</button>
        </form>
    @endif

    @if($repairRequest->photos->isNotEmpty())
        <section class="mt-5 border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Photos de la demande</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($repairRequest->photos as $photo)
                    <a href="{{ asset('storage/'.$photo->path) }}" target="_blank" rel="noreferrer">
                        <img src="{{ asset('storage/'.$photo->path) }}" alt="Photo {{ strtolower($photo->type) }}" class="aspect-square w-full object-cover">
                        <span class="mt-1 block text-xs text-slate-500">{{ $photo->type }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection