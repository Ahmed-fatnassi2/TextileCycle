@extends('layouts.front')

@section('title', $upcycledProduct->name.' | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-12 lg:py-16">
        <a href="{{ route('products.index') }}" class="text-sm font-semibold text-emerald-900 underline decoration-emerald-300 underline-offset-4">← Tous les produits</a>

        <div class="mt-7 grid gap-10 border-y border-slate-200 py-8 md:grid-cols-[1.1fr_0.9fr] md:gap-16 md:py-12">
            <div class="flex min-h-72 flex-col justify-between bg-[#e9f1ed] p-7 sm:min-h-96 sm:p-10">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Création upcyclée</p>
                <div>
                    <p class="text-sm text-slate-600">Matière réemployée</p>
                    <h1 class="mt-2 font-display text-4xl leading-tight text-slate-950 sm:text-5xl">{{ $upcycledProduct->materialBatch->material_type }}</h1>
                    <p class="mt-3 text-slate-700">Lot de {{ $upcycledProduct->materialBatch->weight }} kg · Qualité {{ $upcycledProduct->materialBatch->quality_grade }}</p>
                </div>
            </div>

            <div class="flex flex-col justify-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Produit upcyclé</p>
                <h2 class="mt-3 font-display text-4xl leading-tight text-slate-950">{{ $upcycledProduct->name }}</h2>
                <p class="mt-5 text-3xl font-semibold text-emerald-900">{{ number_format((float) $upcycledProduct->price, 2, ',', ' ') }} TND</p>
                <p class="mt-3 text-sm {{ $upcycledProduct->stock > 0 ? 'text-emerald-800' : 'text-slate-500' }}">
                    {{ $upcycledProduct->stock > 0 ? 'Disponible · '.$upcycledProduct->stock.' en stock' : 'Actuellement épuisé' }}
                </p>
                <div class="mt-8 border-t border-slate-200 pt-5">
                    <h3 class="font-semibold text-slate-900">Matière & origine</h3>
                    <p class="mt-2 leading-7 text-slate-600">Ce produit est associé à un lot de {{ $upcycledProduct->materialBatch->weight }} kg de {{ strtolower($upcycledProduct->materialBatch->material_type) }}, classé qualité {{ $upcycledProduct->materialBatch->quality_grade }}.</p>
                </div>
                <p class="mt-6 border-l-2 border-lime-400 pl-4 text-sm leading-6 text-slate-600">Chaque pièce upcyclée contribue à prolonger la vie des matières textiles.</p>
            </div>
        </div>
    </section>
@endsection