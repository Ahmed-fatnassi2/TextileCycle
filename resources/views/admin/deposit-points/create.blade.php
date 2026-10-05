@extends('layouts.admin')

@section('title', 'Nouveau point')
@section('page_title', 'Créer un point de collecte')

@section('content')

    <div class="bg-white rounded-xl shadow p-6 max-w-2xl">
        <form action="{{ route('admin.deposit-points.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nom *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full border rounded-lg px-3 py-2 @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Adresse *</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="w-full border rounded-lg px-3 py-2 @error('address') border-red-500 @enderror">
                @error('address') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Ville *</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       class="w-full border rounded-lg px-3 py-2 @error('city') border-red-500 @enderror">
                @error('city') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Capacité (nb de vêtements) *</label>
                <input type="number" name="capacity" value="{{ old('capacity', 100) }}"
                       class="w-full border rounded-lg px-3 py-2 @error('capacity') border-red-500 @enderror">
                @error('capacity') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">État *</label>
                <select name="state" class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\DepositPoint::states() as $state)
                        <option value="{{ $state }}" @selected(old('state') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">
                    Enregistrer
                </button>
                <a href="{{ route('admin.deposit-points.index') }}"
                   class="px-5 py-2 rounded-lg border hover:bg-gray-50">Annuler</a>
            </div>
        </form>
    </div>

@endsection