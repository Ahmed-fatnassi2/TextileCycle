@extends('layouts.admin')
@section('title', 'Modifier atelier')
@section('page_title', 'Modifier un atelier')
@section('content')<div class="max-w-2xl rounded-xl bg-white p-6 shadow"><form action="{{ route('admin.workshops.update', $workshop) }}" method="POST">@csrf @method('PUT') @include('admin.workshops._form')<div class="flex gap-3"><button class="rounded-lg bg-emerald-600 px-5 py-2 text-white">Mettre à jour</button><a href="{{ route('admin.workshops.index') }}" class="rounded-lg border px-5 py-2">Annuler</a></div></form></div>@endsection
