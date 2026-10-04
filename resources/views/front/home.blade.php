@extends('layouts.front')

@section('title', 'Accueil - TexTileCycle')

@section('content')
    <section class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white">
        <div class="max-w-7xl mx-auto px-4 py-20 text-center">
            <h1 class="text-5xl font-bold mb-4">Donnez une seconde vie à vos vêtements</h1>
            <p class="text-xl mb-8">Réparation • Transformation • Don</p>
            <a href="#" class="bg-white text-emerald-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Commencer maintenant
            </a>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center mb-12">Comment ça marche ?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-xl shadow">
                <div class="text-4xl mb-3">📦</div>
                <h3 class="font-bold text-xl mb-2">Déposez</h3>
                <p class="text-gray-600">Déposez vos vêtements inutilisés en quelques clics.</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow">
                <div class="text-4xl mb-3">🔧</div>
                <h3 class="font-bold text-xl mb-2">Réparez / Transformez</h3>
                <p class="text-gray-600">Nos ateliers partenaires leur donnent une nouvelle vie.</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow">
                <div class="text-4xl mb-3">❤️</div>
                <h3 class="font-bold text-xl mb-2">Donnez</h3>
                <p class="text-gray-600">Offrez-les à des associations locales.</p>
            </div>
        </div>
    </section>
@endsection