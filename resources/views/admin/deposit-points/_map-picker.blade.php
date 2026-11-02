{{-- Choix de l'emplacement : clic sur la carte ou saisie des coordonnées. $depositPoint est optionnel (création). --}}
<div class="mb-4">
    <label class="block text-sm font-medium mb-1">Emplacement sur la carte</label>
    <p class="text-xs text-gray-500 mb-2">Cliquez sur la carte pour placer le point, puis faites glisser le marqueur pour l’ajuster.</p>

    <div class="relative isolate overflow-hidden rounded-lg border">
        <div class="h-72 w-full bg-slate-100"
             data-map="picker"
             data-lat-input="#latitude"
             data-lng-input="#longitude"
             data-clear-button="#clear-location"></div>
    </div>

    <div class="mt-2 grid grid-cols-[1fr_1fr_auto] gap-3">
        <input type="text" inputmode="decimal" id="latitude" name="latitude" placeholder="Latitude"
               value="{{ old('latitude', $depositPoint->latitude ?? '') }}"
               class="w-full border rounded-lg px-3 py-2 text-sm @error('latitude') border-red-500 @enderror">
        <input type="text" inputmode="decimal" id="longitude" name="longitude" placeholder="Longitude"
               value="{{ old('longitude', $depositPoint->longitude ?? '') }}"
               class="w-full border rounded-lg px-3 py-2 text-sm @error('longitude') border-red-500 @enderror">
        <button type="button" id="clear-location" class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">Effacer</button>
    </div>
    @error('latitude') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    @error('longitude') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

@push('scripts')
    @vite('resources/js/deposit-map.js')
@endpush
