@extends('layouts.admin')
@section('title', 'Nouvel atelier')
@section('page_title', 'Créer un atelier')
@section('content')<div class="max-w-2xl rounded-xl bg-white p-6 shadow"><form action="{{ route('admin.workshops.store') }}" method="POST">@csrf @include('admin.workshops._form')<div class="flex gap-3"><button class="rounded-lg bg-emerald-600 px-5 py-2 text-white">Enregistrer</button><a href="{{ route('admin.workshops.index') }}" class="rounded-lg border px-5 py-2">Annuler</a></div></form></div>@endsection
