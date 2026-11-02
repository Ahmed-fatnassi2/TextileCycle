@extends('layouts.admin')

@section('title', 'Validation '.$deposit->code)
@section('page_title', 'Validation du dépôt')

@section('content')

    <div class="max-w-3xl space-y-6">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Code</p>
                    <p class="font-mono text-2xl font-bold">{{ $deposit->code }}</p>
                </div>
                <span class="px-3 py-1 rounded text-sm font-semibold {{ $deposit->statusBadgeClasses() }}">{{ $deposit->status }}</span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Citoyen</dt><dd class="font-medium">{{ $deposit->user->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Email</dt><dd>{{ $deposit->user->email ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Point prévu</dt><dd>{{ $deposit->depositPoint->name ?? '—' }} ({{ $deposit->depositPoint->city ?? '' }})</dd></div>
                <div><dt class="text-gray-500">Date prévue</dt><dd>{{ $deposit->deposit_date->format('d/m/Y') }}</dd></div>
                <div><dt class="text-gray-500">Poids déclaré</dt><dd>{{ $deposit->weight_kg }} kg</dd></div>
                <div><dt class="text-gray-500">État déclaré</dt><dd>{{ $deposit->state }}</dd></div>
            </dl>

            @if($deposit->isPending() && ! $deposit->deposit_date->isToday())
                <p class="mt-4 rounded bg-amber-50 border border-amber-200 px-4 py-2 text-sm text-amber-800">
                    Attention : ce dépôt était prévu le {{ $deposit->deposit_date->format('d/m/Y') }}.
                </p>
            @endif
        </div>

        @if($deposit->isPending())
            <form method="POST" action="{{ route('admin.deposits.checkin.confirm', $deposit->code) }}" class="bg-white rounded-xl shadow p-6">
                @csrf
                <h2 class="text-lg font-bold mb-4">Contrôle au point de collecte</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-1">Poids pesé (kg) *</label>
                        <input type="number" step="0.1" name="weight_kg" value="{{ old('weight_kg', $deposit->weight_kg) }}"
                               class="w-full border rounded-lg px-3 py-2 @error('weight_kg') border-red-500 @enderror">
                        @error('weight_kg') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">État constaté *</label>
                        <select name="state" class="w-full border rounded-lg px-3 py-2">
                            @foreach(\App\Models\Deposit::states() as $s)
                                <option value="{{ $s }}" @selected(old('state', $deposit->state) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('state') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button name="decision" value="accept" class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">
                        ✔ Valider le dépôt
                    </button>
                    <button name="decision" value="reject" onclick="return confirm('Refuser ce dépôt ?')"
                            class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700">
                        ✖ Refuser
                    </button>
                    <a href="{{ route('admin.deposits.scan') }}" class="px-5 py-2 rounded-lg border hover:bg-gray-50">Annuler</a>
                </div>
            </form>
        @else
            <div class="bg-white rounded-xl shadow p-6 text-sm">
                <p class="font-semibold">Ce dépôt a déjà été traité.</p>
                @if($deposit->checked_in_at)
                    <p class="mt-1 text-gray-600">
                        Le {{ $deposit->checked_in_at->format('d/m/Y à H:i') }}
                        par {{ $deposit->checkedInBy->name ?? 'un administrateur' }}.
                    </p>
                @endif
                <div class="mt-4 flex gap-3">
                    <a href="{{ route('admin.deposits.scan') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg">Scanner un autre dépôt</a>
                    <a href="{{ route('admin.deposits.show', $deposit) }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Voir la fiche</a>
                </div>
            </div>
        @endif
    </div>

@endsection
