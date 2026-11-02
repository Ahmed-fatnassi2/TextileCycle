@extends('layouts.front')

@section('title', 'Confirmez votre mot de passe | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-xl px-5 py-12 sm:px-8 lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Zone sécurisée</p>
        <h1 class="mt-4 font-display text-4xl leading-tight text-slate-950">Confirmez votre mot de passe.</h1>
        <p class="mt-4 leading-7 text-slate-600">Pour protéger votre compte, ressaisissez votre mot de passe avant de modifier vos réglages de sécurité.</p>

        <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-8 space-y-5 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            @csrf
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold text-slate-800">Mot de passe</label>
                <input id="password" type="password" name="password" required autofocus autocomplete="current-password" class="w-full border border-slate-300 px-3 py-2.5 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-800/15">
                @error('password')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <button class="min-h-12 w-full bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-800">Confirmer</button>
        </form>
    </section>
@endsection
