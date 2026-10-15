<div class="mb-4">
    <label for="name" class="mb-1 block text-sm font-medium">Nom du produit *</label>
    <input id="name" type="text" name="name" value="{{ old('name', $upcycledProduct->name ?? '') }}" required maxlength="255" class="w-full rounded-lg border px-3 py-2 @error('name') border-red-500 @enderror">
    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label for="price" class="mb-1 block text-sm font-medium">Prix *</label>
    <input id="price" type="number" name="price" value="{{ old('price', $upcycledProduct->price ?? '') }}" required min="0" step="0.01" class="w-full rounded-lg border px-3 py-2 @error('price') border-red-500 @enderror">
    @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label for="stock" class="mb-1 block text-sm font-medium">Stock *</label>
    <input id="stock" type="number" name="stock" value="{{ old('stock', $upcycledProduct->stock ?? 0) }}" required min="0" step="1" class="w-full rounded-lg border px-3 py-2 @error('stock') border-red-500 @enderror">
    @error('stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="mb-6">
    <label for="material_batch_id" class="mb-1 block text-sm font-medium">Lot de matière *</label>
    <select id="material_batch_id" name="material_batch_id" required class="w-full rounded-lg border px-3 py-2 @error('material_batch_id') border-red-500 @enderror">
        <option value="">Choisir un lot</option>
        @foreach($materialBatches as $batch)
            <option value="{{ $batch->id }}" @selected((string) old('material_batch_id', $upcycledProduct->material_batch_id ?? '') === (string) $batch->id)>
                {{ $batch->material_type }} - {{ $batch->weight }} kg - qualité {{ $batch->quality_grade }}
            </option>
        @endforeach
    </select>
    @error('material_batch_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    @if($materialBatches->isEmpty()) <p class="mt-1 text-sm text-amber-700">Créez d’abord un lot de matière.</p> @endif
</div>

<div class="flex gap-3">
    <button class="rounded-lg bg-emerald-600 px-5 py-2 text-white hover:bg-emerald-700">{{ $submitLabel }}</button>
    <a href="{{ route('admin.upcycled-products.index') }}" class="rounded-lg border px-5 py-2 hover:bg-gray-50">Annuler</a>
</div>