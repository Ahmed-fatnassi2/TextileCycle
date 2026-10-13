@extends('layouts.admin')

@section('title', $association->name)
@section('page_title', 'Dossier association')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div><p class="text-sm text-slate-500">{{ $association->user->email }}</p><h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ $association->name }}</h2><p class="mt-1 text-sm text-slate-600">Contact : {{ $association->contact_person }} · {{ $association->phone }}</p></div>
        <div class="flex gap-2">
            <a href="{{ route('admin.associations.edit', $association) }}" class="inline-flex min-h-10 items-center bg-emerald-900 px-4 text-sm font-semibold text-white">Modifier / statuer</a>
            <button type="button" id="open-association-delete-dialog" class="min-h-10 border border-rose-300 px-4 text-sm font-semibold text-rose-800 hover:bg-rose-50">Supprimer</button>
        </div>
    </div>
    <dialog id="association-delete-dialog" aria-labelledby="association-delete-title" aria-describedby="association-delete-description" class="association-delete-dialog fixed inset-0 m-0 h-dvh w-screen max-h-none max-w-none place-items-center border-0 bg-transparent p-4 text-slate-900 shadow-none backdrop:bg-slate-950/60">
        <div class="w-full max-w-lg overflow-hidden rounded-lg bg-white shadow-2xl">
            <div class="border-b border-rose-100 bg-rose-50 px-6 py-5">
                <div class="flex items-start gap-4">
                    <span aria-hidden="true" class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-rose-100 text-lg font-bold text-rose-700">!</span>
                    <div>
                        <h2 id="association-delete-title" class="text-lg font-semibold text-slate-950">Supprimer cette association ?</h2>
                        <p id="association-delete-description" class="mt-1 text-sm leading-6 text-slate-600">Cette action supprimera également toutes les demandes de dons liées à cette association. Elle est irréversible.</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap justify-end gap-3 px-6 py-4">
                <button type="button" data-close-association-delete class="min-h-10 border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Annuler</button>
                <form method="POST" action="{{ route('admin.associations.destroy', $association) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="min-h-10 bg-rose-700 px-4 text-sm font-semibold text-white hover:bg-rose-800">Supprimer l’association</button>
                </form>
            </div>
        </div>
    </dialog>
    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <section class="border border-slate-200 bg-white p-6"><h3 class="font-semibold text-slate-950">Besoins exprimés</h3><p class="mt-3 whitespace-pre-line leading-7 text-slate-700">{{ $association->needs_description }}</p><p class="mt-6 border-t border-slate-100 pt-4 text-sm text-slate-500">Demande reçue le {{ $association->created_at->format('d/m/Y') }}</p></section>
        <section class="border border-slate-200 bg-white"><div class="border-b border-slate-200 px-5 py-4"><h3 class="font-semibold text-slate-950">Demandes de dons</h3></div><div class="overflow-x-auto"><table class="w-full min-w-[520px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Articles</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Action</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($association->donations as $donation)<tr><td class="px-4 py-3">{{ $donation->donation_date->format('d/m/Y') }}</td><td class="px-4 py-3">{{ $donation->quantity_items }}</td><td class="px-4 py-3">{{ $donation->status }}</td><td class="px-4 py-3"><a class="font-semibold text-emerald-900 underline" href="{{ route('admin.donations.show', $donation) }}">Ouvrir</a></td></tr>@empty<tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucun don enregistré.</td></tr>@endforelse</tbody></table></div></section>
    </div>
@endsection

@push('scripts')
    <script>
        const associationDeleteDialog = document.getElementById('association-delete-dialog');
        const openAssociationDeleteButton = document.getElementById('open-association-delete-dialog');

        window.addEventListener('pageshow', () => {
            if (associationDeleteDialog.open) {
                associationDeleteDialog.close();
            }
        });

        openAssociationDeleteButton.addEventListener('click', () => {
            associationDeleteDialog.showModal();
        });

        document.querySelector('[data-close-association-delete]').addEventListener('click', () => {
            associationDeleteDialog.close();
        });

        associationDeleteDialog.addEventListener('click', (event) => {
            if (event.target === associationDeleteDialog) {
                associationDeleteDialog.close();
            }
        });
    </script>
@endpush