@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Tableau de bord')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow">
            <p class="text-gray-500 text-sm">Utilisateurs</p>
            <p class="text-3xl font-bold text-emerald-600">0</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow">
            <p class="text-gray-500 text-sm">Vêtements</p>
            <p class="text-3xl font-bold text-emerald-600">0</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow">
            <p class="text-gray-500 text-sm">Services</p>
            <p class="text-3xl font-bold text-emerald-600">0</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow">
            <p class="text-gray-500 text-sm">Dons</p>
            <p class="text-3xl font-bold text-emerald-600">0</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-bold mb-4">Bienvenue dans l'administration de TexTileCycle 🎉</h2>
        <p class="text-gray-600">Utilisez le menu à gauche pour gérer les utilisateurs, vêtements, catégories et services.</p>
    </div>
@endsection