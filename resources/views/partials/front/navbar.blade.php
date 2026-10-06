<header class="relative z-20 border-b border-slate-200 bg-white">
    <div class="mx-auto flex min-h-16 max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-2 px-5 py-3 sm:px-8 lg:px-12">
        <a href="{{ route('home') }}" class="flex items-center gap-3 text-slate-950" aria-label="TexTileCycle, accueil">
            <span class="grid h-9 w-9 place-items-center bg-emerald-900 text-lg text-lime-200">T</span>
            <span class="text-lg font-bold tracking-wide">TexTileCycle</span>
        </a>
        <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium text-slate-700" aria-label="Navigation principale">
            <a href="{{ route('products.index') }}" class="transition hover:text-emerald-800">Produits upcyclés</a>
            <a href="{{ route('deposits.index') }}" class="transition hover:text-emerald-800">Collectes</a>
            @auth
                @if(auth()->user()->association?->status === \App\Models\Association::STATUS_APPROVED)
                    <a href="{{ route('association.donations.index') }}" class="transition hover:text-emerald-800">Mes demandes de dons</a>
                @elseif(auth()->user()->association)
                    <a href="{{ route('association.status') }}" class="transition hover:text-emerald-800">Mon association</a>
                @else
                    <a href="{{ route('associations.create') }}" class="transition hover:text-emerald-800">Devenir partenaire</a>
                @endif
                @if(auth()->user()->role === 'admin')<a href="{{ route('admin.dashboard') }}" class="transition hover:text-emerald-800">Administration</a>@endif
                <span class="hidden text-slate-400 sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="font-semibold text-emerald-900 underline decoration-emerald-300 underline-offset-4">Déconnexion</button></form>
            @else
                <a href="{{ route('login') }}" class="transition hover:text-emerald-800">Connexion</a>
                <a href="{{ route('register') }}" class="bg-emerald-900 px-4 py-2 font-semibold text-white transition hover:bg-emerald-800">Créer un compte</a>
            @endauth
        </nav>
    </div>
</header>