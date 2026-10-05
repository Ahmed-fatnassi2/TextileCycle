<section class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-20 text-center">
        <h1 class="text-5xl font-bold mb-4">Donnez une seconde vie à vos vêtements</h1>
        <p class="text-xl mb-8">Réparation • Transformation • Don</p>

        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('deposits.create') }}"
               class="bg-white text-emerald-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Déposer un vêtement
            </a>
            <a href="{{ route('deposits.index') }}"
               class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-emerald-600 transition">
                Voir les points de collecte
            </a>
        </div>
    </div>
</section>