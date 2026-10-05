@extends('layouts.admin')

@section('title', 'Modifier dépôt')
@section('page_title', 'Modifier un dépôt')

@section('content')

    <div class="bg-white rounded-xl shadow p-6 max-w-2xl">
        <form action="{{ route('admin.deposits.update', $deposit) }}" method="POST">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Citoyen *</label>
                <select name="user_id" class="w-full border rounded-lg px-3 py-2">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('user_id', $deposit->user_id) == $u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Point de collecte *</label>
                <select name="deposit_point_id" class="w-full border rounded-lg px-3 py-2">
                    @foreach($points as $p)
                        <option value="{{ $p->id }}" @selected(old('deposit_point_id', $deposit->deposit_point_id) == $p->id)>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Poids (kg) *</label>
                <input type="number" step="0.1" name="weight_kg"
                       value="{{ old('weight_kg', $deposit->weight_kg) }}"
                       class="w-full border rounded-lg px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">État *</label>
                <select name="state" class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\Deposit::states() as $s)
                        <option value="{{ $s }}" @selected(old('state', $deposit->state) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Statut *</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2">
                    @foreach(\App\Models\Deposit::statuses() as $s)
                        <option value="{{ $s }}" @selected(old('status', $deposit->status) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Date *</label>
                <input type="date" name="deposit_date"
                       value="{{ old('deposit_date', $deposit->deposit_date->format('Y-m-d')) }}"
                       class="w-full border rounded-lg px-3 py-2">
            </div>

            <div class="flex gap-3">
                <button class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">Mettre à jour</button>
                <a href="{{ route('admin.deposits.index') }}" class="px-5 py-2 rounded-lg border hover:bg-gray-50">Annuler</a>
            </div>
        </form>
    </div>

@endsection