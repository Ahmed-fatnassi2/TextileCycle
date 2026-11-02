@extends('layouts.admin')

@section('title', 'Dons | TexTileCycle')
@section('page_title', 'Demandes de dons')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm text-slate-500">Module 4 · Associations et dons</p><h2 class="mt-1 text-2xl font-semibold text-slate-950">Suivi des dons</h2></div>
        <div class="flex gap-3">
            <a href="{{ route('admin.donations.calendar') }}" class="inline-flex min-h-10 items-center border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">📅 Calendrier</a>
            <a href="{{ route('admin.donations.create') }}" class="inline-flex min-h-10 items-center bg-emerald-900 px-4 text-sm font-semibold text-white hover:bg-emerald-800">Enregistrer un don</a>
        </div>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Référence, association ou ville…" class="w-72 border border-slate-300 bg-white px-3 py-2 text-sm">
        <select name="category" class="border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Toutes catégories</option>
            @foreach(\App\Models\Donation::CATEGORIES as $category)
                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Tous statuts</option>
            @foreach(\App\Models\Donation::statuses() as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button class="bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filtrer</button>
        @if(array_filter($filters))
            <a href="{{ route('admin.donations.index') }}" class="self-center text-sm text-slate-500 hover:underline">Effacer</a>
        @endif
    </form>

    <div class="overflow-hidden border border-slate-200 bg-white">
        <div class="overflow-x-auto"><table class="w-full min-w-[980px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Référence</th>
                    <th class="px-4 py-3">Association</th>
                    <th class="px-4 py-3">Catégorie</th>
                    <th class="px-4 py-3">Quantité</th>
                    <th class="px-4 py-3">Soumise le</th>
                    <th class="px-4 py-3">Livraison</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($donations as $donation)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-4 font-mono text-xs font-semibold">{{ $donation->reference }}</td>
                        <td class="px-4 py-4 font-semibold text-slate-900">{{ $donation->association->name }}</td>
                        <td class="px-4 py-4">{{ $donation->category }}</td>
                        <td class="px-4 py-4">{{ $donation->quantity_items }} pièces</td>
                        <td class="px-4 py-4">{{ $donation->submittedAt()?->format('d/m/Y') }}</td>
                        <td class="px-4 py-4">{{ $donation->donation_date->format('d/m/Y') }}<span class="block text-xs text-slate-500">{{ $donation->delivery_city ?? '—' }}</span></td>
                        <td class="px-4 py-4"><span class="border px-2.5 py-1 text-xs font-semibold {{ $donation->statusBadgeClasses() }}">{{ $donation->status }}</span></td>
                        <td class="px-4 py-4 text-right"><a href="{{ route('admin.donations.show', $donation) }}" class="font-semibold text-emerald-900 underline underline-offset-4">Ouvrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">Aucune demande de don ne correspond.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="mt-4">{{ $donations->links() }}</div>
@endsection
