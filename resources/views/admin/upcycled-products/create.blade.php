@extends('layouts.admin')

@section('title', 'Nouveau produit')
@section('page_title', 'Créer un produit upcyclé')

@section('content')
    <div class="max-w-2xl rounded-xl bg-white p-6 shadow">
        <form action="{{ route('admin.upcycled-products.store') }}" method="POST">
            @csrf
            @include('admin.upcycled-products._form', ['submitLabel' => 'Enregistrer'])
        </form>
    </div>
@endsection