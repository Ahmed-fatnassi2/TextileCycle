@extends('layouts.admin')

@section('title', 'Lots de matière')
@section('page_title', 'Gestion des lots de matière')

@section('content')
    <div class="mb-6 flex items-center justify-end">
        <a href="{{ route('admin.material-batches.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">+ Nouveau lot</a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Matière</th>
                    <th class="px-4 py-3 text-left">Poids</th>
                    <th class="px-4 py-3 text-left">Qualité</th>
                    <th class="px-4 py-3 text-left">Produits</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($materialBatches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $batch->id }}</td>
                        <td class="px-4 py-3 font-medium">{{ $batch->material_type }}</td>
                        <td class="px-4 py-3">{{ $batch->weight }} kg</td>
                        <td class="px-4 py-3">{{ $batch->quality_grade }}</td>
                        <td class="px-4 py-3">{{ $batch->upcycled_products_count }}</td>
                        <td class="space-x-2 px-4 py-3 text-right">
                            <a href="{{ route('admin.material-batches.show', $batch) }}" class="text-blue-600 hover:underline">Voir</a>
                            <a href="{{ route('admin.material-batches.edit', $batch) }}" class="text-emerald-600 hover:underline">Modifier</a>
                            <form action="{{ route('admin.material-batches.destroy', $batch) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce lot et ses produits associés ?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-gray-400">Aucun lot de matière.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $materialBatches->links() }}</div>
@endsection