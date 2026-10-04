<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center space-x-2">
                <span class="text-2xl">♻️</span>
                <span class="text-xl font-bold text-emerald-600">TexTileCycle</span>
            </a>

            {{-- Menu --}}
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ url('/') }}" class="text-gray-700 hover:text-emerald-600 transition">Accueil</a>
                <a href="#" class="text-gray-700 hover:text-emerald-600 transition">Vêtements</a>
                <a href="#" class="text-gray-700 hover:text-emerald-600 transition">Ateliers</a>
                <a href="#" class="text-gray-700 hover:text-emerald-600 transition">Associations</a>
                <a href="#" class="text-gray-700 hover:text-emerald-600 transition">À propos</a>
            </div>

            {{-- Auth --}}
            <div class="flex items-center space-x-3">
                @guest
                    <a href="#" class="text-gray-700 hover:text-emerald-600">Connexion</a>
                    <a href="#" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition">
                        Inscription
                    </a>
                @endguest
                @auth
                    <a href="#" class="text-gray-700 hover:text-emerald-600">Mon espace</a>
                    <form method="POST" action="#">
                        @csrf
                        <button class="text-red-500 hover:text-red-700">Déconnexion</button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav>