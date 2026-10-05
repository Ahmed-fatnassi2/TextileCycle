<aside class="w-64 bg-gray-900 text-gray-200 flex flex-col">

    {{-- Logo --}}
    <div class="h-16 flex items-center justify-center border-b border-gray-700">
        <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-emerald-400">
            ♻️ TexTileCycle
        </a>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center px-3 py-2 rounded-lg transition
                  {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-emerald-400' : 'hover:bg-gray-800' }}">
            📊 <span class="ml-3">Dashboard</span>
        </a>

        {{-- Séparateur --}}
        <div class="text-xs uppercase text-gray-500 px-3 mt-4 mb-1">Module Dépôt</div>

        {{-- Points de collecte --}}
        <a href="{{ route('admin.deposit-points.index') }}"
           class="flex items-center px-3 py-2 rounded-lg transition
                  {{ request()->routeIs('admin.deposit-points.*') ? 'bg-gray-800 text-emerald-400' : 'hover:bg-gray-800' }}">
            📍 <span class="ml-3">Points de collecte</span>
        </a>

        {{-- Dépôts --}}
        <a href="{{ route('admin.deposits.index') }}"
           class="flex items-center px-3 py-2 rounded-lg transition
                  {{ request()->routeIs('admin.deposits.*') ? 'bg-gray-800 text-emerald-400' : 'hover:bg-gray-800' }}">
            📦 <span class="ml-3">Dépôts</span>
        </a>

        {{-- Séparateur --}}
        <div class="text-xs uppercase text-gray-500 px-3 mt-4 mb-1">Autres</div>

        {{-- Utilisateurs (placeholder) --}}
        <a href="#"
           class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-800 transition opacity-50 cursor-not-allowed">
            👥 <span class="ml-3">Utilisateurs</span>
        </a>

        {{-- Retour au front --}}
        <a href="{{ url('/') }}"
           class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-800 transition mt-4 border-t border-gray-700 pt-4">
            🌐 <span class="ml-3">Voir le site</span>
        </a>

    </nav>

    {{-- Bas de sidebar --}}
    <div class="p-3 border-t border-gray-700 text-xs text-gray-500">
        v1.0 — Module Dépôt
    </div>
</aside>