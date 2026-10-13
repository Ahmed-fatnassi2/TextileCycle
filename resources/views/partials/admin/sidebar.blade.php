<aside class="hidden w-60 shrink-0 flex-col bg-slate-950 text-slate-200 lg:flex">
    <div class="flex h-16 items-center border-b border-white/10 px-6">
        <a href="{{ route('admin.dashboard') }}" class="font-bold tracking-wide text-lime-300">TexTileCycle</a>
    </div>
    <nav class="flex-1 space-y-1 px-3 py-5 text-sm" aria-label="Administration">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">🏠</span>Vue d’ensemble</a>
        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Collecte</p>
        <a href="{{ route('admin.deposit-points.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.deposit-points.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">📍</span>Points de collecte</a>
        <a href="{{ route('admin.deposits.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.deposits.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">📦</span>Dépôts citoyens</a>
        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Module Réparation</p>
        <a href="{{ route('admin.workshops.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.workshops.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">🧵</span>Ateliers</a>
        <a href="{{ route('admin.repair-requests.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.repair-requests.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">🪡</span>Réparations</a>
        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Transformation</p>
        <a href="{{ route('admin.material-batches.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.material-batches.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">🧶</span>Lots de matière</a>
        <a href="{{ route('admin.upcycled-products.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.upcycled-products.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">♻️</span>Produits upcyclés</a>
        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Associations et dons</p>
        <a href="{{ route('admin.associations.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.associations.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">🤝</span>Associations</a>
        <a href="{{ route('admin.donations.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.donations.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">🧺</span>Dons</a>
        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Administration</p>
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-3 py-2.5 {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-lime-300' : 'text-slate-300 hover:bg-white/5' }}"><span class="w-5 text-center">👥</span>Utilisateurs</a>
    </nav>
    <div class="space-y-2 border-t border-white/10 p-4 text-sm">
        <a href="{{ route('home') }}" class="flex items-center gap-2 px-2 py-2 text-slate-300 hover:text-white"><span class="w-5 text-center">🌐</span>Voir le site</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex w-full items-center gap-2 px-2 py-2 text-left text-rose-300 hover:text-rose-200"><span class="w-5 text-center">↪</span>Déconnexion</button></form>
    </div>
</aside>
