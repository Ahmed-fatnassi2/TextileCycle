@extends('layouts.admin')

@section('title', 'Rôles et permissions')
@section('page_title', 'Rôles et permissions')

@section('content')
    <div class="mb-6 max-w-4xl">
        <p class="text-sm text-slate-600">
            Chaque route du site est protégée côté serveur par une permission (Spatie Permission).
            Cochez ce que chaque rôle a le droit de faire : le changement s’applique immédiatement à tous les utilisateurs du rôle.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.roles.update') }}" class="max-w-5xl overflow-hidden rounded-xl bg-white shadow">
        @csrf @method('PUT')
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Permission</th>
                        @foreach($roles as $role)
                            <th class="px-4 py-3 text-center">
                                {{ \App\Support\Permissions::roleLabel($role->name) }}
                                <span class="block text-xs font-normal text-gray-400">{{ $role->users_count }} utilisateur(s)</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($permissions as $permission => $label)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $label }}</p>
                                <p class="font-mono text-xs text-slate-400">{{ $permission }}</p>
                            </td>
                            @foreach($roles as $role)
                                @php($isLocked = $role->name === \App\Models\User::ROLE_ADMIN && in_array($permission, $locked, true))
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="permissions[{{ $role->name }}][]" value="{{ $permission }}"
                                           aria-label="{{ $label }} — {{ \App\Support\Permissions::roleLabel($role->name) }}"
                                           @checked($role->permissions->contains('name', $permission))
                                           @disabled($isLocked)
                                           class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                                    @if($isLocked)<span class="block text-[10px] text-slate-400" title="Indispensable pour garder l’accès au back office">verrouillé</span>@endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between gap-4 border-t border-slate-100 p-4">
            <p class="text-xs text-slate-500">Les rôles des utilisateurs se changent dans « Utilisateurs ».</p>
            <button class="rounded-lg bg-emerald-700 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-800">Enregistrer les permissions</button>
        </div>
    </form>
@endsection
