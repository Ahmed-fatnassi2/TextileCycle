@extends('layouts.admin')

@section('title', 'Dons | TexTileCycle')
@section('page_title', 'Demandes de dons')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm text-slate-500">Module 4 · Associations et dons</p><h2 class="mt-1 text-2xl font-semibold text-slate-950">Suivi des dons</h2></div>
        <a href="{{ route('admin.donations.create') }}" class="inline-flex min-h-10 items-center bg-emerald-900 px-4 text-sm font-semibold text-white hover:bg-emerald-800">Enregistrer un don</a>
    </div>
    <div class="overflow-hidden border border-slate-200 bg-white">
        <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Association</th><th class="px-4 py-3">Quantité</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($donations as $donation)
                    <tr class="hover:bg-slate-50/70"><td class="px-4 py-4 font-semibold text-slate-900">{{ $donation->association->name }}</td><td class="px-4 py-4">{{ $donation->quantity_items }} pièces</td><td class="px-4 py-4">{{ $donation->donation_date->format('d/m/Y') }}</td><td class="px-4 py-4"><span class="border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-900">{{ $donation->status }}</span></td><td class="px-4 py-4 text-right"><a href="{{ route('admin.donations.show', $donation) }}" class="font-semibold text-emerald-900 underline underline-offset-4">Ouvrir</a></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">Aucune demande de don pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="mt-4">{{ $donations->links() }}</div>
@endsection