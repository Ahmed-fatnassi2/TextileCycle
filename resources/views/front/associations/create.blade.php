@extends('layouts.front')

@section('title', 'Demande association | TexTileCycle')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12 sm:px-8 lg:px-12">
        <div class="mb-8 max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Partenariat associatif</p>
            <h1 class="mt-3 font-display text-4xl text-slate-950">Présentez votre association.</h1>
            <p class="mt-3 leading-7 text-slate-600">Après examen, votre espace sera activé. Le statut est attribué par l’équipe TexTileCycle.</p>
        </div>
        <form method="POST" action="{{ route('associations.store') }}" class="grid gap-5 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 sm:p-8">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-semibold">Nom de l’association *</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="w-full border border-slate-300 px-3 py-2.5">
                @error('name')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact_person" class="mb-1 block text-sm font-semibold">Personne à contacter *</label>
                <input id="contact_person" name="contact_person" value="{{ old('contact_person') }}" required class="w-full border border-slate-300 px-3 py-2.5">
                @error('contact_person')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="phone" class="mb-1 block text-sm font-semibold">Téléphone *</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required class="w-full border border-slate-300 px-3 py-2.5 sm:max-w-md">
                @error('phone')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="needs_description" class="mb-1 block text-sm font-semibold">Décrivez vos besoins textiles *</label>
                <textarea id="needs_description" name="needs_description" rows="5" required class="w-full border border-slate-300 px-3 py-2.5">{{ old('needs_description') }}</textarea>
                @error('needs_description')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-wrap items-center gap-4 sm:col-span-2">
                <button class="min-h-12 bg-emerald-900 px-6 font-semibold text-white hover:bg-emerald-800">Envoyer ma demande</button>
                <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-600 underline underline-offset-4">Annuler</a>
            </div>
        </form>
    </section>
@endsection