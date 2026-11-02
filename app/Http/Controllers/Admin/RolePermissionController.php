<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// Back office : matrice rôles × permissions (Spatie Permission).
class RolePermissionController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::with('permissions')->withCount('users')->orderBy('id')->get(),
            'permissions' => Permissions::ALL,
            'locked' => Permissions::LOCKED_FOR_ADMIN,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $roles = Role::orderBy('id')->get();

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['string', Rule::in(array_keys(Permissions::ALL))],
        ]);

        foreach ($roles as $role) {
            $granted = $data['permissions'][$role->name] ?? [];

            // L'administrateur garde toujours l'accès au back office et à la gestion des accès
            if ($role->name === User::ROLE_ADMIN) {
                $granted = array_unique([...$granted, ...Permissions::LOCKED_FOR_ADMIN]);
            }

            $role->syncPermissions($granted);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        User::logActivity($request->user(), 'Permissions modifiées', 'Matrice des rôles et permissions mise à jour.', $request->user());

        return back()->with('success', 'Permissions mises à jour. Elles s’appliquent immédiatement.');
    }
}
