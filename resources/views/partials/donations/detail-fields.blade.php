{{-- Champs détaillés d'une demande de don, partagés par le formulaire association et le formulaire admin.
     Paramètres : $donation (en modification) et $defaults (valeurs proposées à la création). --}}
@php
    $value = fn (string $field) => old($field, $donation->{$field} ?? ($defaults[$field] ?? ''));
    $field = 'w-full border border-slate-300 bg-white px-3 py-2.5';
@endphp

<div class="grid gap-5 sm:grid-cols-[1fr_2fr]">
    <div>
        <label for="category" class="mb-1 block text-sm font-semibold">Catégorie (public visé) *</label>
        <select id="category" name="category" required class="{{ $field }}">
            <option value="">Choisir</option>
            @foreach(\App\Models\Donation::CATEGORIES as $category)
                <option value="{{ $category }}" @selected($value('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        @error('category')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="description" class="mb-1 block text-sm font-semibold">Description du besoin *</label>
        <textarea id="description" name="description" rows="3" required minlength="10" maxlength="1000"
                  placeholder="Pour qui, pourquoi, tailles souhaitées… Ex. : manteaux et pulls 4-10 ans pour 30 familles avant l’hiver."
                  class="{{ $field }}">{{ $value('description') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
    </div>
</div>

<fieldset class="grid gap-5 sm:grid-cols-[2fr_1fr]">
    <legend class="mb-2 text-sm font-semibold text-slate-800">Lieu de livraison</legend>
    <div>
        <label for="delivery_address" class="mb-1 block text-sm">Adresse *</label>
        <input id="delivery_address" name="delivery_address" value="{{ $value('delivery_address') }}" required maxlength="255" autocomplete="street-address" class="{{ $field }}">
        @error('delivery_address')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="delivery_city" class="mb-1 block text-sm">Ville *</label>
        <input id="delivery_city" name="delivery_city" value="{{ $value('delivery_city') }}" required maxlength="100" autocomplete="address-level2" class="{{ $field }}">
        @error('delivery_city')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
    </div>
</fieldset>
