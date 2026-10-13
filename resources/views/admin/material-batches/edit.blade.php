@extends('layouts.admin')

@section('title', 'Modifier un lot')
@section('page_title', 'Modifier le lot de matière')

@section('content')
    <div class="max-w-2xl rounded-xl bg-white p-6 shadow">
        <form action="{{ route('admin.material-batches.update', $materialBatch) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.material-batches._form', ['submitLabel' => 'Mettre à jour'])
        </form>
    </div>
@endsection