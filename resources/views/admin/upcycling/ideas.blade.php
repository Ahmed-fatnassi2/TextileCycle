@extends('layouts.admin')

@section('title', 'Idées de produits (IA)')
@section('page_title', 'Idées de produits')

@section('content')
    <section class="max-w-6xl rounded-xl border border-violet-200 bg-violet-50/60 p-6"
             data-upcycling-ideas
             data-mode="stock"
             data-url="{{ route('admin.upcycling.ideas.suggest') }}">
        <h2 class="text-lg font-bold">✨ Que fabriquer avec notre matière ?</h2>
        <p class="mb-4 text-sm text-gray-600">
            Choisissez une matière en stock : l’IA propose des produits réalisables avec ses lots, avec prix conseillé et quantité possible.
            « Créer ce produit » ouvre le formulaire prérempli : rien n’est enregistré sans vous.
        </p>

        @if($materials->isEmpty())
            <p class="text-sm text-amber-800">Aucun lot de matière en stock. <a href="{{ route('admin.material-batches.create') }}" class="underline">Créez d’abord un lot.</a></p>
        @else
            <form data-ideas-form class="flex flex-wrap gap-3">
                <select name="material_type" required class="min-w-64 rounded-lg border bg-white px-3 py-2 text-sm">
                    @foreach($materials as $material)
                        <option value="{{ $material->material_type }}" @selected($selected === $material->material_type)>
                            {{ $material->material_type }} · {{ $material->batches }} lot(s) · {{ number_format((float) $material->total_kg, 1, ',', ' ') }} kg
                        </option>
                    @endforeach
                </select>
                <button data-ideas-run class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-800 disabled:opacity-60">Proposer des produits</button>
            </form>
        @endif

        <div data-ideas-result hidden class="mt-6"></div>
    </section>
@endsection

@push('scripts')
    @vite('resources/js/upcycling-ideas.js')
@endpush
