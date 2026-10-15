<header class="relative z-20 border-b border-slate-200 bg-white">
    <div class="mx-auto flex min-h-16 max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-2 px-5 py-3 sm:px-8 lg:px-12">
        <a href="{{ route('home') }}" class="flex items-center gap-3 text-slate-950" aria-label="TexTileCycle, accueil">
            <span class="grid h-9 w-9 place-items-center bg-emerald-900 text-lg text-lime-200">T</span>
            <span class="text-lg font-bold tracking-wide">TexTileCycle</span>
        </a>
        <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium text-slate-700" aria-label="Navigation principale">
            <a href="{{ route('home') }}" class="transition {{ request()->routeIs('home') ? 'text-emerald-800' : 'hover:text-emerald-800' }}">Accueil</a>
            <a href="{{ route('deposits.index') }}" class="transition {{ request()->routeIs('deposits.*') ? 'text-emerald-800' : 'hover:text-emerald-800' }}">Points de collecte</a>
            <a href="{{ route('workshops.index') }}" class="transition {{ request()->routeIs('workshops.*') ? 'text-emerald-800' : 'hover:text-emerald-800' }}">Ateliers</a>
            <a href="{{ route('repairs.create') }}" class="transition {{ request()->routeIs('repairs.create') ? 'text-emerald-800' : 'hover:text-emerald-800' }}">Demander une réparation</a>
            @auth
                <a href="{{ route('repairs.index') }}" class="transition {{ request()->routeIs('repairs.index', 'repairs.show') ? 'text-emerald-800' : 'hover:text-emerald-800' }}">Mes réparations</a>
                @if(auth()->user()->association?->status === \App\Models\Association::STATUS_APPROVED)
                    <a href="{{ route('association.donations.index') }}" class="transition hover:text-emerald-800">Mes demandes de dons</a>
                @elseif(auth()->user()->association)
                    <a href="{{ route('association.status') }}" class="transition hover:text-emerald-800">Mon association</a>
                @else
                    <a href="{{ route('associations.create') }}" class="transition hover:text-emerald-800">Devenir partenaire</a>
                @endif
                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full p-1 pr-2 transition hover:bg-slate-100">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-emerald-900 text-sm font-bold text-lime-200">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="hidden max-w-28 truncate sm:inline">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-slate-400">▾</span>
                    </summary>
                    <div class="absolute right-0 mt-2 w-72 overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                        <div class="border-b border-slate-100 px-4 py-3"><p class="font-semibold text-slate-900">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p></div>
                        <a href="{{ route('profile.edit') }}" class="mx-2 mt-2 block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">👤 Mon profil</a>
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="mx-2 block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">⚙️ Tableau de bord</a>
                        @endif
                        <div class="my-2 border-t border-slate-100"></div>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="mx-2 block w-[calc(100%-1rem)] rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-700 hover:bg-rose-50">Déconnexion</button></form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="transition hover:text-emerald-800">Connexion</a>
                <a href="{{ route('register') }}" class="bg-emerald-900 px-4 py-2 font-semibold text-white transition hover:bg-emerald-800">Créer un compte</a>
            @endauth
        </nav>
    </div>
</header>
