@extends('layouts.admin')

@section('title', 'Modifier un produit')
@section('page_title', 'Modifier le produit upcyclé')

@section('content')
    <div class="max-w-2xl rounded-xl bg-white p-6 shadow">
        <form action="{{ route('admin.upcycled-products.update', $upcycledProduct) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.upcycled-products._form', ['submitLabel' => 'Mettre à jour'])
        </form>
    </div>
@endsection