@extends('layouts.admin')

@section('title', $upcycledProduct->name)
@section('page_title', 'Détail du produit upcyclé')

@section('content')
    <div class="max-w-3xl rounded-xl bg-white p-6 shadow">
        <h2 class="mb-5 text-xl font-bold">{{ $upcycledProduct->name }}</h2>
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
            <div><dt class="text-gray-500">Prix</dt><dd>{{ number_format((float) $upcycledProduct->price, 2, ',', ' ') }}</dd></div>
            <div><dt class="text-gray-500">Stock</dt><dd>{{ $upcycledProduct->stock }}</dd></div>
            <div><dt class="text-gray-500">Lot de matière</dt><dd><a class="text-emerald-700 hover:underline" href="{{ route('admin.material-batches.show', $upcycledProduct->materialBatch) }}">{{ $upcycledProduct->materialBatch->material_type }} - {{ $upcycledProduct->materialBatch->weight }} kg</a></dd></div>
            <div><dt class="text-gray-500">Qualité du lot</dt><dd>{{ $upcycledProduct->materialBatch->quality_grade }}</dd></div>
        </dl>
        <a href="{{ route('admin.upcycled-products.index') }}" class="mt-6 inline-block text-sm text-emerald-700 hover:underline">Retour aux produits</a>
    </div>
@endsection