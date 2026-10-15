@extends('layouts.front')

@section('title', 'Déposer un vêtement')

@section('content')
    <section class="max-w-2xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-6">Déposer un vêtement</h1>

        <div class="bg-white rounded-xl shadow p-6">
            <form action="{{ route('deposits.store') }}" method="POST">
                @csrf

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
                    <label class="block text-sm font-medium mb-1">Poids estimé (kg) *</label>
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
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1">Date *</label>
                    <input type="date" name="deposit_date" min="{{ now()->toDateString() }}" value="{{ old('deposit_date', date('Y-m-d')) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- Champs cachés pour la démo --}}
                <input type="hidden" name="status" value="Déposé">

                <button class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700">
                    Enregistrer mon dépôt
                </button>
            </form>
        </div>
    </section>
@endsection