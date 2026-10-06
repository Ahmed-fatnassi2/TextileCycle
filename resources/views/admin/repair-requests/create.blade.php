@extends('layouts.admin')
@section('title', 'Nouvelle réparation')
@section('page_title', 'Créer une demande de réparation')
@section('content')<div class="max-w-2xl rounded-xl bg-white p-6 shadow"><form action="{{ route('admin.repair-requests.store') }}" method="POST" enctype="multipart/form-data">@csrf @include('admin.repair-requests._form')<div class="flex gap-3"><button class="rounded-lg bg-emerald-600 px-5 py-2 text-white">Enregistrer</button><a href="{{ route('admin.repair-requests.index') }}" class="rounded-lg border px-5 py-2">Annuler</a></div></form></div>@endsection
