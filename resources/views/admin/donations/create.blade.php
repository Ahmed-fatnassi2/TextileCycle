@extends('layouts.admin')

@section('title', 'Nouveau don')
@section('page_title', 'Enregistrer un don')

@section('content')
    @include('admin.donations._form')
@endsection