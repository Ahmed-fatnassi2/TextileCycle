@extends('layouts.admin')
@section('title', 'Modifier réparation')
@section('page_title', 'Modifier une demande de réparation')
@section('content')<div class="max-w-2xl rounded-xl bg-white p-6 shadow"><form action="{{ route('admin.repair-requests.update', $repairRequest) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.repair-requests._form')<div class="flex gap-3"><button class="rounded-lg bg-emerald-600 px-5 py-2 text-white">Mettre à jour</button><a href="{{ route('admin.repair-requests.index') }}" class="rounded-lg border px-5 py-2">Annuler</a></div></form></div>@endsection
