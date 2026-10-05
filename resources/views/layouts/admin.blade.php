<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - TexTileCycle')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-sans text-slate-900">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        @include('partials.admin.sidebar')

        {{-- Contenu principal --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            
            {{-- Topbar --}}
            @include('partials.admin.navbar')

            {{-- Contenu --}}
            <main class="flex-1 overflow-y-auto p-5 sm:p-8">
                
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>