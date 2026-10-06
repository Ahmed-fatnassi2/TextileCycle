@extends('layouts.front')

@section('title', 'Mon profil')

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-12">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Mon compte</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">Mon profil</h1>
            <p class="mt-2 text-slate-600">Mettez à jour vos informations personnelles.</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-5">
                <span class="grid h-12 w-12 place-items-center rounded-full bg-emerald-900 text-lg font-bold text-lime-200">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div><p class="font-semibold text-slate-900">{{ $user->name }}</p><p class="text-sm text-slate-500">{{ $user->role === 'admin' ? 'Administrateur' : 'Citoyen' }}</p></div>
            </div>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf @method('PUT')
                <div><label for="name" class="mb-1 block text-sm font-medium">Nom complet</label><input id="name" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-lg border px-3 py-2 @error('name') border-red-500 @enderror">@error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                <div><label for="email" class="mb-1 block text-sm font-medium">Adresse e-mail</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-lg border px-3 py-2 @error('email') border-red-500 @enderror">@error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                <button class="rounded-lg bg-emerald-700 px-5 py-2 font-medium text-white hover:bg-emerald-800">Enregistrer les modifications</button>
            </form>
        </div>
    </section>
@endsection
