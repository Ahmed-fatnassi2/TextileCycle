@extends('layouts.front')

@section('title', 'Mes dons | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-12">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Espace association</p><h1 class="mt-2 font-display text-4xl text-slate-950">Mes demandes de dons</h1><p class="mt-2 text-slate-600">Suivez les demandes liées à votre association.</p></div>
            <a href="{{ route('association.donations.create') }}" class="inline-flex min-h-12 items-center bg-emerald-900 px-5 font-semibold text-white hover:bg-emerald-800">Nouvelle demande</a>
        </div>
        <div class="overflow-hidden border border-slate-200 bg-white">
            <div class="overflow-x-auto"><table class="w-full min-w-[620px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Date souhaitée</th><th class="px-4 py-3">Nombre de pièces</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Détail</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($donations as $donation)
                        <tr class="hover:bg-slate-50/70"><td class="px-4 py-4">{{ $donation->donation_date->format('d/m/Y') }}</td><td class="px-4 py-4 font-semibold">{{ $donation->quantity_items }} pièces</td><td class="px-4 py-4"><span class="border px-2.5 py-1 text-xs font-semibold {{ $donation->status === \App\Models\Donation::STATUS_DELIVERED ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : ($donation->status === \App\Models\Donation::STATUS_VALIDATED ? 'border-sky-200 bg-sky-50 text-sky-900' : 'border-amber-200 bg-amber-50 text-amber-900') }}">{{ $donation->status }}</span></td><td class="px-4 py-4 text-right"><a href="{{ route('association.donations.show', $donation) }}" class="font-semibold text-emerald-900 underline underline-offset-4">Ouvrir</a></td></tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-12 text-center text-slate-500">Vous n’avez pas encore créé de demande de don.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
        <div class="mt-4">{{ $donations->links() }}</div>
    </section>
@endsection