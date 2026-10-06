<div class="mb-4">
    <label for="material_type" class="mb-1 block text-sm font-medium">Type de matière *</label>
    <input id="material_type" type="text" name="material_type" value="{{ old('material_type', $materialBatch->material_type ?? '') }}" required maxlength="255" class="w-full rounded-lg border px-3 py-2 @error('material_type') border-red-500 @enderror">
    @error('material_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label for="weight" class="mb-1 block text-sm font-medium">Poids (kg) *</label>
    <input id="weight" type="number" name="weight" value="{{ old('weight', $materialBatch->weight ?? '') }}" required min="0.01" step="0.01" class="w-full rounded-lg border px-3 py-2 @error('weight') border-red-500 @enderror">
    @error('weight') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="mb-6">
    <label for="quality_grade" class="mb-1 block text-sm font-medium">Qualité (ex. A, B, C) *</label>
    <input id="quality_grade" type="text" name="quality_grade" value="{{ old('quality_grade', $materialBatch->quality_grade ?? '') }}" required maxlength="255" class="w-full rounded-lg border px-3 py-2 @error('quality_grade') border-red-500 @enderror">
    @error('quality_grade') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="flex gap-3">
    <button class="rounded-lg bg-emerald-600 px-5 py-2 text-white hover:bg-emerald-700">{{ $submitLabel }}</button>
    <a href="{{ route('admin.material-batches.index') }}" class="rounded-lg border px-5 py-2 hover:bg-gray-50">Annuler</a>
</div>