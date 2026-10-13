@extends('layouts.admin')

@section('title', 'Nouvelle association')
@section('page_title', 'Ajouter une association')

@section('content')
    <form method="POST" action="{{ route('admin.associations.store') }}" class="max-w-4xl space-y-6 border border-slate-200 bg-white p-6 sm:p-8">
        @csrf
        <div><label for="user_id" class="mb-1 block text-sm font-semibold">Compte utilisateur *</label><select id="user_id" name="user_id" required class="w-full border border-slate-300 bg-white px-3 py-2.5"><option value="">Sélectionner un compte</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} · {{ $user->email }}</option>@endforeach</select>@error('user_id')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
        @include('admin.associations._fields', ['association' => null])
        <div class="flex flex-wrap gap-3"><button class="min-h-11 bg-emerald-900 px-5 font-semibold text-white">Créer l’association</button><a href="{{ route('admin.associations.index') }}" class="inline-flex min-h-11 items-center border border-slate-300 px-5 font-semibold text-slate-700">Annuler</a></div>
    </form>
@endsection