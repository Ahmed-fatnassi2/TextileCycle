<header class="min-h-16 border-b border-slate-200 bg-white px-5 py-3 sm:px-8">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-lg font-semibold text-slate-900">@yield('page_title', 'Administration')</h1>
        <div class="flex items-center gap-3">
            <span class="hidden text-sm text-slate-500 sm:inline">{{ auth()->user()->name }}</span>
            <a href="{{ route('profile.edit') }}" title="Mon profil">@include('partials.avatar', ['user' => auth()->user(), 'class' => 'h-9 w-9'])</a>
        </div>
    </div>
    <nav class="mt-3 flex gap-4 overflow-x-auto text-sm text-slate-600 lg:hidden" aria-label="Navigation administration">
        @can('donations.manage')
            <a class="shrink-0" href="{{ route('admin.associations.index') }}">Associations</a>
            <a class="shrink-0" href="{{ route('admin.donations.index') }}">Dons</a>
        @endcan
        @can('deposits.manage')<a class="shrink-0" href="{{ route('admin.deposit-points.index') }}">Collectes</a>@endcan
        @can('repairs.manage')
            <a class="shrink-0" href="{{ route('admin.workshops.index') }}">Ateliers</a>
            <a class="shrink-0" href="{{ route('admin.repair-requests.index') }}">Réparations</a>
        @endcan
        @can('upcycling.manage')<a class="shrink-0" href="{{ route('admin.material-batches.index') }}">Lots</a>@endcan
        @can('users.manage')<a class="shrink-0" href="{{ route('admin.users.index') }}">Utilisateurs</a>@endcan
        @can('roles.manage')<a class="shrink-0" href="{{ route('admin.roles.index') }}">Rôles</a>@endcan
        <a class="shrink-0" href="{{ route('home') }}">Site public</a>
    </nav>
</header>
