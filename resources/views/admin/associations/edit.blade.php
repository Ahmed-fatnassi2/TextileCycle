@extends('layouts.admin')

@section('title', 'Modifier association')
@section('page_title', 'Modifier · '.$association->name)

@section('content')
    <form method="POST" action="{{ route('admin.associations.update', $association) }}" class="max-w-4xl space-y-6 border border-slate-200 bg-white p-6 sm:p-8">
        @csrf @method('PUT')
        <p class="border-b border-slate-100 pb-4 text-sm text-slate-600">Compte lié : <span class="font-semibold text-slate-900">{{ $association->user->name }} · {{ $association->user->email }}</span></p>
        @include('admin.associations._fields', ['association' => $association])
        <div class="flex flex-wrap gap-3"><button class="min-h-11 bg-emerald-900 px-5 font-semibold text-white">Enregistrer</button><a href="{{ route('admin.associations.show', $association) }}" class="inline-flex min-h-11 items-center border border-slate-300 px-5 font-semibold text-slate-700">Annuler</a></div>
    </form>
@endsection