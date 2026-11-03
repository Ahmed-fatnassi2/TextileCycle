@extends('layouts.admin')

@section('title', 'Aperçu de l’import')
@section('page_title', 'Aperçu de l’import')

@section('content')
    <div class="mb-6 rounded-xl bg-white p-6 shadow">
        <p class="text-sm text-gray-500">{{ $typeLabel }} · {{ $fileName }}</p>
        <div class="mt-3 flex flex-wrap gap-6 text-sm">
            <p><span class="text-2xl font-bold text-emerald-700">{{ $preview['valid'] }}</span> ligne(s) valide(s)</p>
            <p><span class="text-2xl font-bold {{ $preview['invalid'] ? 'text-red-600' : 'text-gray-400' }}">{{ $preview['invalid'] }}</span> ligne(s) en erreur</p>
            <p><span class="text-2xl font-bold text-gray-700">{{ collect($preview['rows'])->where('action', 'create')->where('errors', [])->count() }}</span> création(s)</p>
            <p><span class="text-2xl font-bold text-gray-700">{{ collect($preview['rows'])->where('action', 'update')->where('errors', [])->count() }}</span> mise(s) à jour</p>
        </div>

        @if($preview['error'])
            <p class="mt-4 rounded border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-900">{{ $preview['error'] }}</p>
        @endif

        <div class="mt-5 flex flex-wrap gap-3">
            @if($preview['valid'] > 0)
                <form method="POST" action="{{ route('admin.upcycling.import.confirm') }}">
                    @csrf
                    <button class="rounded-lg bg-emerald-600 px-5 py-2 text-white hover:bg-emerald-700">
                        Importer {{ $preview['valid'] }} ligne(s) valide(s)
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.upcycling.import.cancel') }}">
                @csrf
                <button class="rounded-lg border px-5 py-2 hover:bg-gray-50">Annuler</button>
            </form>
        </div>
        @if($preview['invalid'] > 0 && $preview['valid'] > 0)
            <p class="mt-2 text-xs text-gray-500">Les lignes en erreur seront ignorées. Corrigez-les dans le fichier et réimportez-les plus tard si besoin.</p>
        @endif
    </div>

    @if($preview['rows'])
        <div class="overflow-hidden rounded-xl bg-white shadow">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Ligne</th>
                            <th class="px-4 py-3 text-left">Action</th>
                            @foreach(array_keys($preview['rows'][0]['data']) as $field)
                                <th class="px-4 py-3 text-left">{{ \App\Services\UpcyclingImporter::LABELS[$field] ?? $field }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-left">Résultat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($preview['rows'] as $row)
                            <tr class="{{ $row['errors'] ? 'bg-red-50/60' : '' }}">
                                <td class="px-4 py-2 text-gray-500">{{ $row['line'] }}</td>
                                <td class="px-4 py-2">
                                    <span class="rounded px-2 py-0.5 text-xs font-semibold {{ $row['action'] === 'update' ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $row['action'] === 'update' ? 'Mise à jour #'.$row['id'] : 'Création' }}
                                    </span>
                                </td>
                                @foreach($row['data'] as $value)
                                    <td class="max-w-56 truncate px-4 py-2" title="{{ $value }}">{{ $value ?? '—' }}</td>
                                @endforeach
                                <td class="px-4 py-2">
                                    @if($row['errors'])
                                        <ul class="space-y-0.5 text-xs text-red-700">
                                            @foreach($row['errors'] as $error)<li>{{ $error }}</li>@endforeach
                                        </ul>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-700">✔ OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
