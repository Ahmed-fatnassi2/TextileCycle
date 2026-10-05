@php($editing = isset($donation))
<form method="POST" action="{{ $editing ? route('admin.donations.update', $donation) : route('admin.donations.store') }}" class="max-w-3xl space-y-5 border border-slate-200 bg-white p-6 sm:p-8">
    @csrf
    @if($editing) @method('PUT') @endif
    <div>
        <label for="association_id" class="mb-1 block text-sm font-semibold">Association *</label>
        <select id="association_id" name="association_id" required class="w-full border border-slate-300 bg-white px-3 py-2.5"><option value="">Choisir une association</option>@foreach($associations as $association)<option value="{{ $association->id }}" @selected(old('association_id', $donation->association_id ?? '') == $association->id)>{{ $association->name }} · {{ $association->status }}</option>@endforeach</select>
        @error('association_id')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
    </div>
    <div class="grid gap-5 sm:grid-cols-2">
        <div><label for="quantity_items" class="mb-1 block text-sm font-semibold">Nombre de pièces *</label><input id="quantity_items" name="quantity_items" type="number" min="1" max="10000" value="{{ old('quantity_items', $donation->quantity_items ?? '') }}" required class="w-full border border-slate-300 px-3 py-2.5">@error('quantity_items')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
        <div><label for="donation_date" class="mb-1 block text-sm font-semibold">Date du don *</label><input id="donation_date" name="donation_date" type="date" value="{{ old('donation_date', isset($donation) ? $donation->donation_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="w-full border border-slate-300 px-3 py-2.5">@error('donation_date')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
    </div>
    <div><label for="status" class="mb-1 block text-sm font-semibold">Statut *</label><select id="status" name="status" required class="w-full border border-slate-300 bg-white px-3 py-2.5">@foreach(\App\Models\Donation::statuses() as $status)<option value="{{ $status }}" @selected(old('status', $donation->status ?? \App\Models\Donation::STATUS_PENDING) === $status)>{{ $status }}</option>@endforeach</select>@error('status')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
    <div class="flex flex-wrap gap-3"><button class="min-h-11 bg-emerald-900 px-5 font-semibold text-white">{{ $editing ? 'Enregistrer les modifications' : 'Créer le don' }}</button><a href="{{ $editing ? route('admin.donations.show', $donation) : route('admin.donations.index') }}" class="inline-flex min-h-11 items-center border border-slate-300 px-5 font-semibold text-slate-700">Annuler</a></div>
</form>