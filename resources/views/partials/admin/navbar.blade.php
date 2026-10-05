<header class="h-16 bg-white shadow flex items-center justify-between px-6">
    <div class="flex items-center gap-4">
        <h1 class="text-lg font-semibold text-gray-800">@yield('page_title', 'Administration')</h1>
    </div>

    <div class="flex items-center space-x-4">
        <a href="{{ url('/') }}"
           class="text-sm text-gray-600 hover:text-emerald-600 transition">
            🌐 Voir le site
        </a>

        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Admin</span>
            <div class="w-9 h-9 bg-emerald-600 text-white rounded-full flex items-center justify-center font-bold">
                A
            </div>
        </div>
    </div>
</header>