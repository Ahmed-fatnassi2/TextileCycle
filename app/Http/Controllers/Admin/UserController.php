<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->withCount(['repairRequests', 'activities']);
        if ($search = $request->input('search')) {
            $query->where(fn ($builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['activities' => fn ($query) => $query->with('actor')->latest()->take(30)]);

        return view('admin.users.show', compact('user'));
    }

    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Vous ne pouvez pas modifier votre propre rôle.');
        $oldRole = $user->role;
        $user->update($request->validated());
        User::logActivity($user, 'Rôle modifié', "Rôle modifié de {$oldRole} vers {$user->role} par un administrateur.", $request->user(), ['old_role' => $oldRole, 'new_role' => $user->role]);

        return back()->with('success', 'Rôle utilisateur mis à jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Vous ne pouvez pas supprimer votre propre compte.');
        User::logActivity($user, 'Compte supprimé', "Compte de {$user->name} supprimé par un administrateur.", $request->user());
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }
}
