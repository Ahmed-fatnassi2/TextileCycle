@extends('layouts.admin')

@section('title', 'Scanner un dépôt')
@section('page_title', 'Scanner un dépôt')

@section('content')

    <div class="grid max-w-5xl gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-xl shadow p-6"
             data-qr-scanner
             data-lookup-url="{{ route('admin.deposits.lookup') }}">
            <h2 class="text-lg font-bold mb-1">Scanner le QR code du citoyen</h2>
            <p data-scanner-status class="text-sm text-slate-600">Démarrez la caméra ou importez une photo du QR code.</p>

            <div id="qr-reader" data-scanner-reader class="mt-4 overflow-hidden rounded-lg bg-slate-900/5 min-h-12"></div>

            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" data-scanner-start class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-emerald-700">
                    📷 Démarrer la caméra
                </button>
                <button type="button" data-scanner-stop class="hidden bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                    Arrêter la caméra
                </button>
                <label class="cursor-pointer rounded-lg border px-4 py-2 text-sm hover:bg-gray-50">
                    🖼️ Importer une photo
                    <input type="file" accept="image/*" data-scanner-file class="hidden">
                </label>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold mb-1">Saisie manuelle</h2>
                <p class="text-sm text-slate-600 mb-4">Si le QR code est illisible, saisissez le code affiché sous celui-ci.</p>

                <form method="GET" action="{{ route('admin.deposits.lookup') }}" class="flex gap-3">
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="TC-XXXXXXXX" autocomplete="off"
                           class="flex-1 border rounded-lg px-3 py-2 font-mono uppercase @error('code') border-red-500 @enderror">
                    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">Rechercher</button>
                </form>
                @error('code') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <a href="{{ route('admin.deposits.index', ['status' => \App\Models\Deposit::STATUS_EN_ATTENTE]) }}"
               class="flex items-center justify-between bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div>
                    <p class="text-sm text-gray-500">Dépôts en attente de validation</p>
                    <p class="text-3xl font-bold text-amber-600">{{ $pendingCount }}</p>
                </div>
                <span class="text-4xl">⏳</span>
            </a>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/qr-scanner.js')
@endpush
