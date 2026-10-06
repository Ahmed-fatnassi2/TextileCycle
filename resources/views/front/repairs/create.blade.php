@extends('layouts.front')
@section('title', 'Demander une réparation')
@section('content')
<section class="mx-auto max-w-2xl px-4 py-12">
    <h1 class="mb-2 text-3xl font-bold">Demander une réparation</h1><p class="mb-6 text-gray-600">Décrivez votre vêtement, ajoutez des photos et recevez un devis de l’atelier.</p>
    <div class="rounded-xl bg-white p-6 shadow"><form action="{{ route('repairs.store') }}" method="POST" enctype="multipart/form-data">@csrf
        <div class="mb-4"><label class="mb-1 block text-sm font-medium">Atelier *</label><select name="workshop_id" class="w-full rounded-lg border px-3 py-2 @error('workshop_id') border-red-500 @enderror"><option value="">-- Choisir --</option>@foreach($workshops as $workshop)<option value="{{ $workshop->id }}" @selected(old('workshop_id') == $workshop->id)>{{ $workshop->name }} — {{ $workshop->specialty }}</option>@endforeach</select>@error('workshop_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div class="mb-4"><label class="mb-1 block text-sm font-medium">Description du vêtement *</label><textarea name="item_description" rows="4" placeholder="Ex. Veste en jean, fermeture éclair cassée" class="w-full rounded-lg border px-3 py-2 @error('item_description') border-red-500 @enderror">{{ old('item_description') }}</textarea>@error('item_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div class="mb-4"><label class="mb-1 block text-sm font-medium">Type de problème *</label><select name="problem_type" class="w-full rounded-lg border px-3 py-2 @error('problem_type') border-red-500 @enderror">@foreach(\App\Models\RepairRequest::problemTypes() as $type)<option value="{{ $type }}" @selected(old('problem_type') === $type)>{{ $type }}</option>@endforeach</select>@error('problem_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div class="mb-6"><label class="mb-1 block text-sm font-medium">Photos du vêtement (3 maximum)</label><input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border px-3 py-2">@error('photos')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror @error('photos.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <button class="rounded-lg bg-emerald-600 px-6 py-2 text-white hover:bg-emerald-700">Envoyer ma demande</button>
    </form></div>
</section>
@endsection
