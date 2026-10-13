@extends('layouts.admin')

@section('title', 'Associations | TexTileCycle')
@section('page_title', 'Associations')

@section('content')
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div><p class="text-sm text-slate-500">Module 4 · Associations et dons</p><h2 class="mt-1 text-2xl font-semibold text-slate-950">Demandes et partenaires</h2></div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                <input name="search" value="{{ request('search') }}" placeholder="Nom ou contact" class="min-h-10 border border-slate-300 bg-white px-3 text-sm">
                <select name="status" class="min-h-10 border border-slate-300 bg-white px-3 text-sm">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Association::statuses() as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach
                </select>
                <button class="min-h-10 bg-slate-900 px-4 text-sm font-semibold text-white">Filtrer</button>
            </form>
            <a href="{{ route('admin.associations.create') }}" class="inline-flex min-h-10 items-center justify-center bg-emerald-900 px-4 text-sm font-semibold text-white hover:bg-emerald-800">Ajouter</a>
        </div>
    </div>

    <div class="overflow-hidden border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Association</th><th class="px-4 py-3">Contact</th><th class="px-4 py-3">Compte</th><th class="px-4 py-3">Dons</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($associations as $association)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-4 font-semibold text-slate-900">{{ $association->name }}</td>
                            <td class="px-4 py-4"><span class="block text-slate-800">{{ $association->contact_person }}</span><span class="text-xs text-slate-500">{{ $association->phone }}</span></td>
                            <td class="px-4 py-4 text-slate-600">{{ $association->user->email }}</td>
                            <td class="px-4 py-4 tabular-nums">{{ $association->donations_count }}</td>
                            <td class="px-4 py-4"><span class="inline-flex border px-2.5 py-1 text-xs font-semibold {{ $association->status === \App\Models\Association::STATUS_APPROVED ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : ($association->status === \App\Models\Association::STATUS_REJECTED ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-amber-200 bg-amber-50 text-amber-900') }}">{{ $association->status }}</span></td>
                            <td class="px-4 py-4 text-right"><a href="{{ route('admin.associations.show', $association) }}" class="font-semibold text-emerald-900 underline underline-offset-4">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Aucune association ne correspond à ces critères.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $associations->links() }}</div>
@endsection