@extends('layouts.admin')

@section('title', 'Importer depuis Excel')
@section('page_title', 'Importer des lots ou des produits')

@section('content')
    <div class="grid max-w-5xl gap-6 lg:grid-cols-[1.2fr_1fr]">
        <form method="POST" action="{{ route('admin.upcycling.import.preview') }}" enctype="multipart/form-data" class="rounded-xl bg-white p-6 shadow">
            @csrf
            <h2 class="mb-4 text-lg font-bold">1. Choisir le fichier</h2>

            <fieldset class="mb-4">
                <legend class="mb-2 block text-sm font-medium">Que voulez-vous importer ? *</legend>
                <div class="flex flex-wrap gap-3">
                    @foreach($types as $value => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2 text-sm has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50">
                            <input type="radio" name="type" value="{{ $value }}" @checked(old('type', $selectedType) === $value)>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </fieldset>

            <div class="mb-6">
                <label for="file" class="mb-1 block text-sm font-medium">Fichier Excel ou CSV (5 Mo max) *</label>
                <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="w-full rounded-lg border px-3 py-2 text-sm @error('file') border-red-500 @enderror">
                @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button class="rounded-lg bg-emerald-600 px-5 py-2 text-white hover:bg-emerald-700">Vérifier le fichier</button>
            <p class="mt-2 text-xs text-gray-500">Rien n’est enregistré à cette étape : vous verrez d’abord un aperçu ligne par ligne.</p>
        </form>

        <div class="space-y-4 rounded-xl bg-white p-6 text-sm shadow">
            <h2 class="text-lg font-bold">Format attendu</h2>
            <p class="text-gray-600">Téléchargez un modèle pour avoir les bonnes colonnes :</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.upcycling.template', 'lots') }}" class="rounded-lg border px-3 py-2 hover:bg-gray-50">⬇ Modèle lots</a>
                <a href="{{ route('admin.upcycling.template', 'produits') }}" class="rounded-lg border px-3 py-2 hover:bg-gray-50">⬇ Modèle produits</a>
            </div>
            <ul class="list-disc space-y-1 pl-5 text-gray-600">
                <li><strong>Lots</strong> : Type matière, Poids (kg), Qualité (lettres majuscules, ex. A).</li>
                <li><strong>Produits</strong> : Nom, Description (facultatif), Prix (TND), Stock, Lot ID.</li>
                <li><strong>Colonne ID</strong> : vide = création ; un ID existant = mise à jour de cet élément.</li>
                <li>Un fichier obtenu avec « Exporter Excel » peut être modifié puis réimporté tel quel.</li>
                <li>Les virgules décimales (24,5) et les CSV avec point-virgule sont acceptés.</li>
            </ul>
        </div>
    </div>
@endsection
