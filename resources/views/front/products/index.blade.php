@extends('layouts.front')

@section('title', 'Produits upcyclés | TexTileCycle')

@section('content')
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 lg:px-12 lg:py-16">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Transformation & upcycling</p>
            <h1 class="mt-3 max-w-3xl font-display text-4xl leading-tight text-slate-950 sm:text-5xl">Le textile récupéré devient une nouvelle création.</h1>
            <p class="mt-4 max-w-2xl leading-7 text-slate-600">Parcourez les produits fabriqués à partir de matières textiles récupérées. Chaque fiche indique sa matière d’origine et son stock.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-12">
        <form method="GET" action="{{ route('products.index') }}" class="mb-8 grid gap-3 border-b border-slate-200 pb-6 sm:grid-cols-[1fr_1fr_1fr_auto]">
            <label class="sr-only" for="search">Rechercher un produit ou une matière</label>
            <input id="search" type="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Produit ou matière" class="min-h-11 border border-slate-300 bg-white px-3 py-2 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">

            <label class="sr-only" for="material_type">Filtrer par matière</label>
            <select id="material_type" name="material_type" class="min-h-11 border border-slate-300 bg-white px-3 py-2 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                <option value="">Toutes les matières</option>
                @foreach($materialTypes as $materialType)
                    <option value="{{ $materialType }}" @selected(request('material_type') === $materialType)>{{ $materialType }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="availability">Disponibilité</label>
            <select id="availability" name="availability" class="min-h-11 border border-slate-300 bg-white px-3 py-2 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                <option value="">Tous les stocks</option>
                <option value="available" @selected(request('availability') === 'available')>Disponibles</option>
                <option value="sold_out" @selected(request('availability') === 'sold_out')>Épuisés</option>
            </select>

            <button class="min-h-11 bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-800">Filtrer</button>
            @error('search') <p class="text-sm text-rose-700 sm:col-span-4">{{ $message }}</p> @enderror
            @error('material_type') <p class="text-sm text-rose-700 sm:col-span-4">{{ $message }}</p> @enderror
            @error('availability') <p class="text-sm text-rose-700 sm:col-span-4">{{ $message }}</p> @enderror
        </form>

        <div class="mb-4 flex items-center justify-between text-sm text-slate-600">
            <p>{{ $products->total() }} {{ $products->total() === 1 ? 'produit' : 'produits' }}</p>
            @if(request()->hasAny(['search', 'material_type', 'availability']))
                <a href="{{ route('products.index') }}" class="font-semibold text-emerald-900 underline underline-offset-4">Effacer les filtres</a>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($products as $product)
                <article class="flex min-h-56 flex-col border border-slate-200 bg-white p-5">
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $product->materialBatch->material_type }} · qualité {{ $product->materialBatch->quality_grade }}</p>
                        <h2 class="mt-3 font-display text-2xl text-slate-950">{{ $product->name }}</h2>
                        <p class="mt-2 text-sm text-slate-600">Issu d’un lot de {{ $product->materialBatch->weight }} kg de {{ strtolower($product->materialBatch->material_type) }}.</p>
                    </div>
                    <div class="mt-6 flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
                        <div>
                            <p class="text-lg font-semibold text-emerald-900">{{ number_format((float) $product->price, 2, ',', ' ') }} TND</p>
                            <p class="mt-1 text-sm {{ $product->stock > 0 ? 'text-emerald-800' : 'text-slate-500' }}">{{ $product->stock > 0 ? 'En stock · '.$product->stock : 'Épuisé' }}</p>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:border-emerald-800 hover:text-emerald-900">Détails</a>
                    </div>
                </article>
            @empty
                <p class="border-l-2 border-lime-400 py-2 pl-4 text-slate-600 sm:col-span-2 lg:col-span-3">Aucun produit ne correspond à ces critères.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    </section>
@endsection