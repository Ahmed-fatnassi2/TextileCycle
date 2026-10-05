@php($editing = isset($donation))
<form method="POST" action="{{ $editing ? route('association.donations.update', $donation) : route('association.donations.store') }}" class="max-w-3xl space-y-5 border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="grid gap-5 sm:grid-cols-2">
        <div><label for="quantity_items" class="mb-1 block text-sm font-semibold">Nombre de pièces souhaité *</label><input id="quantity_items" name="quantity_items" type="number" min="1" max="10000" value="{{ old('quantity_items', $donation->quantity_items ?? '') }}" required class="w-full border border-slate-300 px-3 py-2.5">@error('quantity_items')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
        <div><label for="donation_date" class="mb-1 block text-sm font-semibold">Date souhaitée *</label><input id="donation_date" name="donation_date" type="date" min="{{ now()->format('Y-m-d') }}" value="{{ old('donation_date', isset($donation) ? $donation->donation_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="w-full border border-slate-300 px-3 py-2.5">@error('donation_date')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
    </div>
    <p class="border-l-2 border-lime-500 bg-lime-50 p-3 text-sm leading-6 text-slate-700">Votre demande sera transmise à l’équipe. L’association liée à votre compte est ajoutée automatiquement.</p>
    <div class="flex flex-wrap gap-3"><button class="min-h-11 bg-emerald-900 px-5 font-semibold text-white">{{ $editing ? 'Enregistrer' : 'Envoyer la demande' }}</button><a href="{{ $editing ? route('association.donations.show', $donation) : route('association.donations.index') }}" class="inline-flex min-h-11 items-center border border-slate-300 px-5 font-semibold text-slate-700">Annuler</a></div>
</form>