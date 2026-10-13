@extends('layouts.admin')

@section('title', 'Créer un utilisateur')
@section('page_title', 'Créer un utilisateur')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900">Nouveau compte</h2>
            <p class="mt-1 text-sm text-slate-500">Renseignez les informations du compte et son rôle.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nom complet</label>
                <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
                @error('name')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
                @error('email')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="role" class="mb-1 block text-sm font-medium text-slate-700">Rôle</label>
                <select id="role" name="role" required class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
                    <option value="user" @selected(old('role', 'user') === 'user')>Citoyen</option>
                    <option value="admin" @selected(old('role') === 'admin')>Administrateur</option>
                    <option value="workshop_owner" @selected(old('role') === 'workshop_owner')>Propriétaire d’atelier</option>
                </select>
                @error('role')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Mot de passe</label>
                    <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
                    @error('password')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirmer le mot de passe</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-10 items-center border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Annuler</a>
                <button type="submit" class="min-h-10 bg-emerald-800 px-4 text-sm font-semibold text-white hover:bg-emerald-900">Créer le compte</button>
            </div>
        </form>
    </div>
@endsection