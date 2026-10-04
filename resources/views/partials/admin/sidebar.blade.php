<aside class="w-64 bg-gray-900 text-gray-200 flex flex-col">
    
    {{-- Logo --}}
    <div class="h-16 flex items-center justify-center border-b border-gray-700">
        <span class="text-xl font-bold text-emerald-400">♻️ TexTileCycle</span>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 px-3 py-4 space-y-1">
        <a href="{{ url('/admin') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->is('admin') ? 'bg-gray-800 text-emerald-400' : '' }}">
            📊 <span class="ml-3">Dashboard</span>
        </a>
        <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->is('admin/users*') ? 'bg-gray-800 text-emerald-400' : '' }}">
            👥 <span class="ml-3">Utilisateurs</span>
        </a>
        <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->is('admin/clothings*') ? 'bg-gray-800 text-emerald-400' : '' }}">
            👕 <span class="ml-3">Vêtements</span>
        </a>
        <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->is('admin/categories*') ? 'bg-gray-800 text-emerald-400' : '' }}">
            🏷️ <span class="ml-3">Catégories</span>
        </a>
        <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->is('admin/services*') ? 'bg-gray-800 text-emerald-400' : '' }}">
            🔧 <span class="ml-3">Services</span>
        </a>
    </nav>

    {{-- Bas de sidebar --}}
    <div class="p-3 border-t border-gray-700">
        <form method="POST" action="#">
            @csrf
            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-red-800 text-red-300">
                🚪 Déconnexion
            </button>
        </form>
    </div>
</aside>