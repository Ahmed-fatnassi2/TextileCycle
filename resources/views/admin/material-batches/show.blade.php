@extends('layouts.admin')

@section('title', 'Lot '.$materialBatch->id)
@section('page_title', 'Détail du lot de matière')

@section('content')
    <div class="mb-6 rounded-xl bg-white p-6 shadow">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <h2 class="text-xl font-bold">{{ $materialBatch->material_type }}</h2>
            <a href="{{ route('admin.material-batches.pdf', $materialBatch) }}" target="_blank" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50">📄 Fiche PDF</a>
        </div>
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
            <div><dt class="text-gray-500">Poids</dt><dd>{{ $materialBatch->weight }} kg</dd></div>
            <div><dt class="text-gray-500">Qualité</dt><dd>{{ $materialBatch->quality_grade }}</dd></div>
            <div><dt class="text-gray-500">Créé le</dt><dd>{{ $materialBatch->created_at->format('d/m/Y') }}</dd></div>
        </dl>
    </div>

    <a href="{{ route('admin.upcycling.ideas', ['material_type' => $materialBatch->material_type]) }}"
       class="mb-6 flex items-center justify-between gap-4 rounded-xl border border-violet-200 bg-violet-50/60 p-5 hover:bg-violet-50">
        <span>
            <span class="block font-bold">✨ Idées de produits avec ce {{ strtolower($materialBatch->material_type) }}</span>
            <span class="text-sm text-gray-600">L’IA propose des produits à fabriquer à partir de cette matière en stock.</span>
        </span>
        <span class="text-violet-700">→</span>
    </a>

    <div class="rounded-xl bg-white p-6 shadow">
        <h3 class="mb-4 font-bold">Produits issus du lot ({{ $materialBatch->upcycledProducts->count() }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600"><tr><th class="px-3 py-2 text-left">Produit</th><th class="px-3 py-2 text-left">Prix</th><th class="px-3 py-2 text-left">Stock</th><th class="px-3 py-2 text-right">Action</th></tr></thead>
                <tbody class="divide-y">
                    @forelse($materialBatch->upcycledProducts as $product)
                        <tr><td class="px-3 py-2">{{ $product->name }}</td><td class="px-3 py-2">{{ number_format((float) $product->price, 2, ',', ' ') }}</td><td class="px-3 py-2">{{ $product->stock }}</td><td class="px-3 py-2 text-right"><a href="{{ route('admin.upcycled-products.show', $product) }}" class="text-emerald-700 hover:underline">Voir</a></td></tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-gray-400">Aucun produit associé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <a href="{{ route('admin.material-batches.index') }}" class="mt-4 inline-block text-sm text-emerald-700 hover:underline">Retour aux lots</a>
    </div>
@endsection
