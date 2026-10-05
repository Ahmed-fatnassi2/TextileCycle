@extends('layouts.admin')

@section('title', 'Nouveau dépôt')
@section('page_title', 'Enregistrer un dépôt')

@section('content')

    <div class="bg-white rounded-xl shadow p-6 max-w-2xl">
        <form action="{{ route('admin.deposits.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Citoyen *</label>
                <select name="user_id" class="w-full border rounded-lg px-3 py-2 @error('user_id') border-red-500 @enderror">
                    <option value="">-- Choisir --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Point de collecte *</label>
                <select name="deposit_point_id" class="w-full border rounded-lg px-3 py-2 @error('deposit_point_id') border-red-500 @enderror">
                    <option value="">-- Choisir --</option>
                    @foreach($points as $p)
                        <option value="{{ $p->id }}" @selected(old('deposit_point_id') == $p->id)>
                            {{ $p->name }} — {{ $p->city }}
                        </option>
                    @endforeach
                </select>
                @error('deposit_point_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Poids (kg) *</label>
                <input type="number" step="0.1" name="weight_kg" value="{{ old('weight_kg') }}"
                       class="w-full border rounded-lg px-3 py-2 @error('weight_kg') border-red-500 @enderror">
                @error('weight_kg') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">État du vêtement *</label>
                <select name="state" class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\Deposit::states() as $s)
                        <option value="{{ $s }}" @selected(old('state') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                @error('state') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Statut *</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\Deposit::statuses() as $s)
                        <option value="{{ $s }}" @selected(old('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                @error('status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Date de dépôt *</label>
                <input type="date" name="deposit_date" value="{{ old('deposit_date', date('Y-m-d')) }}"
                       class="w-full border rounded-lg px-3 py-2 @error('deposit_date') border-red-500 @enderror">
                @error('deposit_date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">Enregistrer</button>
                <a href="{{ route('admin.deposits.index') }}" class="px-5 py-2 rounded-lg border hover:bg-gray-50">Annuler</a>
            </div>
        </form>
    </div>

@endsection