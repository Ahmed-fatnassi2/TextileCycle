@extends('layouts.front')

@section('title', 'Nouvelle demande de don')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12 sm:px-8 lg:px-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Espace association</p>
        <h1 class="mb-7 mt-2 font-display text-4xl text-slate-950">Demander un don</h1>
        @include('front.donations._form')
    </section>
@endsection