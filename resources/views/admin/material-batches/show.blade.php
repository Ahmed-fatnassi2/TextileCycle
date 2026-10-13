@extends('layouts.admin')

@section('title', 'Lot '.$materialBatch->id)
@section('page_title', 'Détail du lot de matière')

@section('content')
    <div class="mb-6 rounded-xl bg-white p-6 shadow">
        <h2 class="mb-4 text-xl font-bold">{{ $materialBatch->material_type }}</h2>
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
            <div><dt class="text-gray-500">Poids</dt><dd>{{ $materialBatch->weight }} kg</dd></div>
            <div><dt class="text-gray-500">Qualité</dt><dd>{{ $materialBatch->quality_grade }}</dd></div>
            <div><dt class="text-gray-500">Créé le</dt><dd>{{ $materialBatch->created_at->format('d/m/Y') }}</dd></div>
        </dl>
    </div>

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