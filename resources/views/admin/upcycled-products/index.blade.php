@extends('layouts.admin')

@section('title', 'Produits upcyclés')
@section('page_title', 'Gestion des produits upcyclés')

@section('content')
    <div class="mb-6 flex items-center justify-end">
        <a href="{{ route('admin.upcycled-products.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">+ Nouveau produit</a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600"><tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Nom</th><th class="px-4 py-3 text-left">Lot matière</th><th class="px-4 py-3 text-left">Prix</th><th class="px-4 py-3 text-left">Stock</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y">
                @forelse($upcycledProducts as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $product->id }}</td>
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->materialBatch->material_type }} ({{ $product->materialBatch->weight }} kg)</td>
                        <td class="px-4 py-3">{{ number_format((float) $product->price, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3">{{ $product->stock }}</td>
                        <td class="space-x-2 px-4 py-3 text-right">
                            <a href="{{ route('admin.upcycled-products.show', $product) }}" class="text-blue-600 hover:underline">Voir</a>
                            <a href="{{ route('admin.upcycled-products.edit', $product) }}" class="text-emerald-600 hover:underline">Modifier</a>
                            <form action="{{ route('admin.upcycled-products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce produit ?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-gray-400">Aucun produit upcyclé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $upcycledProducts->links() }}</div>
@endsection