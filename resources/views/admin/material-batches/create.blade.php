@extends('layouts.admin')

@section('title', 'Nouveau lot')
@section('page_title', 'Créer un lot de matière')

@section('content')
    <div class="max-w-2xl rounded-xl bg-white p-6 shadow">
        <form action="{{ route('admin.material-batches.store') }}" method="POST">
            @csrf
            @include('admin.material-batches._form', ['submitLabel' => 'Enregistrer'])
        </form>
    </div>
@endsection