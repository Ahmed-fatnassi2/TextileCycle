@extends('layouts.admin')

@section('title', 'Lots de matière')
@section('page_title', 'Gestion des lots de matière')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        {{-- Les filtres s'appliquent aussi à l'export Excel et au rapport PDF --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="material_type" class="rounded-lg border px-3 py-2 text-sm">
                <option value="">Toutes les matières</option>
                @foreach($materialTypes as $type)
                    <option value="{{ $type }}" @selected(($filters['material_type'] ?? '') === $type)>{{ $type }}</option>
                @endforeach
            </select>
            <select name="quality_grade" class="rounded-lg border px-3 py-2 text-sm">
                <option value="">Toutes qualités</option>
                @foreach($qualityGrades as $grade)
                    <option value="{{ $grade }}" @selected(($filters['quality_grade'] ?? '') === $grade)>Qualité {{ $grade }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm text-white hover:bg-gray-700">Filtrer</button>
            @if($filters)
                <a href="{{ route('admin.material-batches.index') }}" class="self-center text-sm text-gray-500 hover:underline">Effacer</a>
            @endif
        </form>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.upcycling.ideas') }}" class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-800">✨ Idées de produits</a>
            <a href="{{ route('admin.upcycling.report', $filters) }}" target="_blank" class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-gray-50">📄 Rapport PDF</a>
            <a href="{{ route('admin.upcycling.export', $filters) }}" class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-gray-50">⬇ Exporter Excel</a>
            <a href="{{ route('admin.upcycling.import', ['type' => 'lots']) }}" class="rounded-lg border bg-white px-4 py-2 text-sm hover:bg-gray-50">⬆ Importer</a>
            <a href="{{ route('admin.material-batches.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">+ Nouveau lot</a>
        </div>
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