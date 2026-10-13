@extends('layouts.admin')

@section('title', 'Modifier le don')
@section('page_title', 'Modifier la demande de don')

@section('content')
    @include('admin.donations._form', ['donation' => $donation])
@endsection