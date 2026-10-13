<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace atelier - TexTileCycle')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex min-h-16 max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-3">
            <a href="{{ route('workshop-owner.dashboard') }}" class="font-bold text-emerald-900">TexTileCycle · Espace atelier</a>
            <nav class="flex flex-wrap items-center gap-4 text-sm font-medium">
                <a href="{{ route('workshop-owner.dashboard') }}" class="text-slate-700 hover:text-emerald-800">Tableau de bord</a>
                <a href="{{ route('workshop-owner.repairs.index') }}" class="text-slate-700 hover:text-emerald-800">Demandes</a>
                <a href="{{ route('home') }}" class="text-slate-700 hover:text-emerald-800">Voir le site</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-rose-700 hover:text-rose-900">Déconnexion</button></form>
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
        @if(session('success'))
            <div class="mb-5 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>