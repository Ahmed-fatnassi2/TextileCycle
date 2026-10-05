@extends('layouts.admin')

@section('title', 'Modifier point')
@section('page_title', 'Modifier un point de collecte')

@section('content')

    <div class="bg-white rounded-xl shadow p-6 max-w-2xl">
        <form action="{{ route('admin.deposit-points.update', $depositPoint) }}" method="POST">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nom *</label>
                <input type="text" name="name" value="{{ old('name', $depositPoint->name) }}"
                       class="w-full border rounded-lg px-3 py-2 @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Adresse *</label>
                <input type="text" name="address" value="{{ old('address', $depositPoint->address) }}"
                       class="w-full border rounded-lg px-3 py-2 @error('address') border-red-500 @enderror">
                @error('address') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Ville *</label>
                <input type="text" name="city" value="{{ old('city', $depositPoint->city) }}"
                       class="w-full border rounded-lg px-3 py-2 @error('city') border-red-500 @enderror">
                @error('city') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Capacité *</label>
                <input type="number" name="capacity" value="{{ old('capacity', $depositPoint->capacity) }}"
                       class="w-full border rounded-lg px-3 py-2">
                @error('capacity') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">État *</label>
                <select name="state" class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\DepositPoint::states() as $state)
                        <option value="{{ $state }}" @selected(old('state', $depositPoint->state) === $state)>
                            {{ $state }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <button class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">
                    Mettre à jour
                </button>
                <a href="{{ route('admin.deposit-points.index') }}"
                   class="px-5 py-2 rounded-lg border hover:bg-gray-50">Annuler</a>
            </div>
        </form>
    </div>

@endsection